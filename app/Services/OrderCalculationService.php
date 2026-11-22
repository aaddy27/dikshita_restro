<?php

namespace App\Services;

use App\Models\DiscountRule;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderCalculationService
{
    /**
     * Compute full breakdown for an order given its items and discount parameters.
     *
     * @param Order|iterable $orderOrItems Either an Order model or collection/array of OrderItems / cart items
     * @param string $manualDiscountType 'none'|'percentage'|'flat'|'manual_override'
     * @param float $manualDiscountValue The input percentage, flat amount, or overridden final payable amount
     * @param Carbon|null $date For day-wise discount check (defaults to now)
     * @return array
     */
    public function calculate(
        $orderOrItems,
        string $manualDiscountType = 'none',
        float $manualDiscountValue = 0.0,
        ?Carbon $date = null
    ): array {
        $items = $orderOrItems instanceof Order ? $orderOrItems->items : $orderOrItems;

        $subtotal = 0.0;
        $eligibleSubtotal = 0.0;
        $excludedSubtotal = 0.0;
        $totalItemsCount = 0;

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? ($item->qty ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? ($item->unit_price ?? 0.0));
            $itemTotal = round($qty * $unitPrice, 2);

            $subtotal += $itemTotal;
            $totalItemsCount += $qty;

            // Check if item is excluded from discounts
            $isExcluded = false;
            if (isset($item['is_discount_excluded'])) {
                $isExcluded = (bool) $item['is_discount_excluded'];
            } elseif (isset($item->menuItem) && $item->menuItem) {
                $isExcluded = (bool) $item->menuItem->is_discount_excluded;
            }

            if ($isExcluded) {
                $excludedSubtotal += $itemTotal;
            } else {
                $eligibleSubtotal += $itemTotal;
            }
        }

        $subtotal = round($subtotal, 2);
        $eligibleSubtotal = round($eligibleSubtotal, 2);
        $excludedSubtotal = round($excludedSubtotal, 2);

        // 1. Day-Wise Global Discount
        $activeRule = DiscountRule::getActiveRuleForDate($date);
        $dayDiscountAmount = 0.0;
        $dayRuleName = null;

        if ($activeRule && $eligibleSubtotal >= (float) $activeRule->min_order_amount && $eligibleSubtotal > 0) {
            $dayRuleName = $activeRule->name;
            if ($activeRule->discount_type === 'percentage') {
                $dayDiscountAmount = round($eligibleSubtotal * ((float) $activeRule->discount_value / 100), 2);
            } else {
                $dayDiscountAmount = min($eligibleSubtotal, (float) $activeRule->discount_value);
            }
        }

        // Subtotal after day discount
        $subtotalAfterDayDiscount = max(0.0, round($subtotal - $dayDiscountAmount, 2));

        // 2. Base GST rate (Restaurant Standard 5% GST: 2.5% CGST + 2.5% SGST)
        $gstRate = 5.0; // 5%

        $manualDiscountAmount = 0.0;
        $taxAmount = 0.0;
        $finalPayableAmount = 0.0;

        if ($manualDiscountType === 'manual_override' && $manualDiscountValue > 0) {
            // Cashier manually set final payable amount (e.g. entered 1100 for 1245 total)
            $targetFinalPayable = round($manualDiscountValue, 2);

            // Nominal pre-manual tax: tax on subtotalAfterDayDiscount
            $nominalTax = round($subtotalAfterDayDiscount * ($gstRate / 100), 2);
            $nominalTotalWithTax = round($subtotalAfterDayDiscount + $nominalTax, 2);

            // manual_discount_amount = (subtotal + tax - day_discount) - final_payable_amount
            if ($targetFinalPayable < $nominalTotalWithTax) {
                $manualDiscountAmount = round($nominalTotalWithTax - $targetFinalPayable, 2);
                $finalPayableAmount = $targetFinalPayable;

                // Derive taxable & tax from final payable amount
                // final = taxable * (1 + gstRate/100)
                $taxableAmount = round($finalPayableAmount / (1 + ($gstRate / 100)), 2);
                $taxAmount = round($finalPayableAmount - $taxableAmount, 2);
            } else {
                // If override is higher than nominal, keep standard
                $manualDiscountAmount = 0.0;
                $taxableAmount = $subtotalAfterDayDiscount;
                $taxAmount = $nominalTax;
                $finalPayableAmount = $targetFinalPayable;
            }
        } else {
            // Percentage or Flat discount
            if ($manualDiscountType === 'percentage' && $manualDiscountValue > 0) {
                $manualDiscountAmount = round($subtotalAfterDayDiscount * ($manualDiscountValue / 100), 2);
            } elseif ($manualDiscountType === 'flat' && $manualDiscountValue > 0) {
                $manualDiscountAmount = min($subtotalAfterDayDiscount, round($manualDiscountValue, 2));
            }

            $taxableAmount = max(0.0, round($subtotalAfterDayDiscount - $manualDiscountAmount, 2));
            $taxAmount = round($taxableAmount * ($gstRate / 100), 2);
            $finalPayableAmount = round($taxableAmount + $taxAmount, 2);
        }

        $cgstAmount = round($taxAmount / 2, 2);
        $sgstAmount = round($taxAmount - $cgstAmount, 2);
        $totalDiscount = round($dayDiscountAmount + $manualDiscountAmount, 2);

        return [
            'subtotal' => $subtotal,
            'eligible_subtotal' => $eligibleSubtotal,
            'excluded_subtotal' => $excludedSubtotal,
            'total_items_count' => $totalItemsCount,
            'day_rule_name' => $dayRuleName,
            'day_discount_amount' => $dayDiscountAmount,
            'manual_discount_type' => $manualDiscountType,
            'manual_discount_value' => $manualDiscountValue,
            'manual_discount_amount' => $manualDiscountAmount,
            'total_discount' => $totalDiscount,
            'taxable_amount' => $taxableAmount,
            'gst_rate' => $gstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'tax_amount' => $taxAmount,
            'final_payable_amount' => $finalPayableAmount,
        ];
    }

    /**
     * Recalculate and update the Order database record.
     */
    public function syncOrderTotals(
        Order $order,
        string $manualDiscountType = 'none',
        float $manualDiscountValue = 0.0
    ): Order {
        $order->loadMissing('items.menuItem');
        $calc = $this->calculate($order, $manualDiscountType, $manualDiscountValue);

        $order->subtotal = $calc['subtotal'];
        $order->day_discount_amount = $calc['day_discount_amount'];
        $order->manual_discount_amount = $calc['manual_discount_amount'];
        $order->tax_amount = $calc['tax_amount'];
        $order->final_payable_amount = $calc['final_payable_amount'];
        $order->save();

        return $order;
    }
}
