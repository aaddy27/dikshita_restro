<?php

namespace App\Livewire\Cashier;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DiscountRule;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Services\OrderCalculationService;
use App\Services\WhatsAppInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class CashierPos extends Component
{
    public ?int $selectedTableId = null;
    public ?int $selectedOrderId = null;

    // Discount & Calculation State
    public string $discountMode = 'auto'; // 'auto', 'percentage', 'flat', 'override'
    public float $discountValue = 0.0;
    public string $manualPayableOverride = '';
    public array $breakdown = [];

    // Security & Unlock Modal
    public bool $showUnlockModal = false;
    public string $unlockPin = '';
    public string $unlockRemarks = 'Customer requested bill alteration after lock';
    public ?string $unlockError = null;

    // UPI Customer Display Modal
    public bool $showUpiModal = false;

    // Table Operations Modals
    public bool $showMergeModal = false;
    public ?int $targetMergeTableId = null;

    public bool $showShiftModal = false;
    public ?int $targetShiftTableId = null;

    public bool $showSplitModal = false;
    public array $splitItems = []; // [order_item_id => qty_to_split]

    // Customer & Payment
    public string $customerName = '';
    public string $customerPhone = '';
    public string $paymentMode = 'upi'; // cash, upi, card
    public string $cashReceived = '';

    // Menu search inside POS
    public string $menuSearch = '';
    public ?int $menuCategoryId = null;

    public function mount()
    {
        // Auto-heal table active status synchronization
        $activeOrders = Order::whereIn('status', ['open', 'locked'])->get();
        foreach ($activeOrders as $ord) {
            RestaurantTable::where('id', $ord->table_id)->update([
                'status' => $ord->status === 'locked' ? 'locked' : 'occupied',
                'current_order_id' => $ord->id,
            ]);
        }

        // Default to first occupied or first table
        $table = RestaurantTable::whereIn('status', ['occupied', 'locked'])->first()
            ?: RestaurantTable::first();

        if ($table) {
            $this->selectTable($table->id);
        }
    }

    public function selectTable(int $tableId)
    {
        $this->selectedTableId = $tableId;
        $table = RestaurantTable::find($tableId);

        if ($table) {
            // Find active running order via current_order_id OR open/locked orders
            $order = null;
            if ($table->current_order_id) {
                $order = Order::with(['items.menuItem', 'items.variant'])->find($table->current_order_id);
                if ($order && !in_array($order->status, ['open', 'locked'])) {
                    $order = null;
                }
            }

            if (!$order) {
                $order = Order::with(['items.menuItem', 'items.variant'])
                    ->where('table_id', $table->id)
                    ->whereIn('status', ['open', 'locked'])
                    ->latest()
                    ->first();
            }

            if ($order) {
                // Keep table status in 100% synchronization
                if ($table->current_order_id !== $order->id || $table->status === 'vacant') {
                    $table->update([
                        'status' => $order->status === 'locked' ? 'locked' : 'occupied',
                        'current_order_id' => $order->id,
                    ]);
                }

                $this->selectedOrderId = $order->id;
                $this->customerName = $order->customer_name ?? '';
                $this->customerPhone = $order->customer_phone ?? '';
                $this->paymentMode = $order->payment_mode ?: 'upi';

                if ($order->manual_discount_amount > 0) {
                    $this->discountMode = 'override';
                    $this->manualPayableOverride = (string) $order->final_payable_amount;
                } else {
                    $this->discountMode = 'auto';
                    $this->discountValue = 0.0;
                    $this->manualPayableOverride = '';
                }
            } else {
                if ($table->status !== 'vacant' || $table->current_order_id !== null) {
                    $table->update([
                        'status' => 'vacant',
                        'current_order_id' => null,
                    ]);
                }
                $this->selectedOrderId = null;
                $this->discountMode = 'auto';
                $this->discountValue = 0.0;
                $this->manualPayableOverride = '';
            }
        } else {
            $this->selectedOrderId = null;
            $this->discountMode = 'auto';
            $this->discountValue = 0.0;
            $this->manualPayableOverride = '';
        }

        $this->recalculate();
    }

    public function updatedDiscountMode()
    {
        if ($this->discountMode === 'auto') {
            $this->discountValue = 0.0;
            $this->manualPayableOverride = '';
        } elseif ($this->discountMode === 'override') {
            $this->manualPayableOverride = (string) ($this->breakdown['final_payable_amount'] ?? '');
        }
        $this->recalculate();
    }

    public function updatedDiscountValue()
    {
        $this->recalculate();
    }

    public function updatedManualPayableOverride()
    {
        $this->recalculate();
    }

    public function recalculate()
    {
        $calcService = app(OrderCalculationService::class);

        if (!$this->selectedOrderId) {
            $this->breakdown = $calcService->calculate([]);
            return;
        }

        $order = Order::with(['items.menuItem', 'items.variant'])->find($this->selectedOrderId);
        if (!$order || $order->items->isEmpty()) {
            $this->breakdown = $calcService->calculate([]);
            return;
        }

        $type = 'none';
        $val = 0.0;

        if ($this->discountMode === 'percentage' && $this->discountValue > 0) {
            $type = 'percentage';
            $val = (float) $this->discountValue;
        } elseif ($this->discountMode === 'flat' && $this->discountValue > 0) {
            $type = 'flat';
            $val = (float) $this->discountValue;
        } elseif ($this->discountMode === 'override' && is_numeric($this->manualPayableOverride) && (float) $this->manualPayableOverride > 0) {
            $type = 'manual_override';
            $val = (float) $this->manualPayableOverride;
        }

        $this->breakdown = $calcService->calculate($order, $type, $val);

        // Update database record
        $order->subtotal = $this->breakdown['subtotal'];
        $order->day_discount_amount = $this->breakdown['day_discount_amount'];
        $order->manual_discount_amount = $this->breakdown['manual_discount_amount'];
        $order->tax_amount = $this->breakdown['tax_amount'];
        $order->final_payable_amount = $this->breakdown['final_payable_amount'];
        $order->customer_name = $this->customerName ?: $order->customer_name;
        $order->customer_phone = $this->customerPhone ?: $order->customer_phone;
        $order->save();
    }

    public function lockBill()
    {
        if (!$this->selectedOrderId) {
            return;
        }

        $order = Order::findOrFail($this->selectedOrderId);
        $table = RestaurantTable::findOrFail($order->table_id);

        $order->update([
            'status' => 'locked',
            'locked_by' => Auth::id(),
            'locked_at' => now(),
        ]);

        $table->update(['status' => 'locked']);

        AuditLog::record(
            $order->id,
            'bill_locked',
            ['status' => 'open'],
            ['status' => 'locked'],
            'Cashier locked bill for checkout settlement.',
            Auth::id()
        );

        $this->recalculate();
    }

    public function openUnlockModal()
    {
        $this->unlockPin = '';
        $this->unlockError = null;
        $this->showUnlockModal = true;
    }

    public function confirmUnlockBill()
    {
        $user = Auth::user();

        // Check if entered PIN matches cashier's security_pin or admin fallback (9999 or 1234)
        if ($this->unlockPin !== $user->security_pin && $this->unlockPin !== '1234' && $this->unlockPin !== '9999') {
            $this->unlockError = 'Incorrect security PIN. Please enter the valid 4-digit cashier PIN.';
            return;
        }

        if (!$this->selectedOrderId) {
            return;
        }

        $order = Order::findOrFail($this->selectedOrderId);
        $table = RestaurantTable::findOrFail($order->table_id);

        $order->update([
            'status' => 'open',
            'locked_by' => null,
            'locked_at' => null,
        ]);

        $table->update(['status' => 'occupied']);

        AuditLog::record(
            $order->id,
            'bill_unlocked',
            ['status' => 'locked'],
            ['status' => 'open'],
            'Security PIN verified. Remarks: ' . $this->unlockRemarks,
            $user->id
        );

        $this->showUnlockModal = false;
        $this->recalculate();
    }

    public function startNewOrderForTable()
    {
        if (!$this->selectedTableId) {
            return;
        }

        $table = RestaurantTable::findOrFail($this->selectedTableId);
        if ($table->status === 'locked') {
            return;
        }

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(6)),
            'table_id' => $table->id,
            'status' => 'open',
            'customer_name' => 'Table ' . $table->table_number,
        ]);

        $table->update([
            'status' => 'occupied',
            'current_order_id' => $order->id,
        ]);

        AuditLog::record(
            $order->id,
            'order_created',
            null,
            ['status' => 'open'],
            "Cashier (" . (Auth::user()->name ?? 'Staff') . ") opened order {$order->order_number} for Table {$table->table_number}",
            Auth::id()
        );

        $this->selectedOrderId = $order->id;
        $this->recalculate();
    }

    public function addItemToOrder(int $menuItemId, ?int $variantId = null)
    {
        if (!$this->selectedOrderId) {
            $this->startNewOrderForTable();
        }

        $order = Order::with('table')->findOrFail($this->selectedOrderId);
        if ($order->isLocked()) {
            return;
        }

        $menuItem = MenuItem::with('variants')->findOrFail($menuItemId);
        $variant = $variantId ? $menuItem->variants->firstWhere('id', $variantId) : null;
        $price = $variant ? (float) $variant->price : (float) $menuItem->base_price;

        $existing = OrderItem::where('order_id', $order->id)
            ->where('menu_item_id', $menuItem->id)
            ->where('item_variant_id', $variantId)
            ->where('kot_status', 'pending')
            ->first();

        if ($existing) {
            $existing->qty += 1;
            $existing->total_price = round($existing->qty * $existing->unit_price, 2);
            $existing->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_variant_id' => $variantId,
                'item_name' => $menuItem->name,
                'variant_name' => $variant ? $variant->name : null,
                'qty' => 1,
                'unit_price' => $price,
                'total_price' => $price,
                'kot_status' => 'pending',
                'added_by_type' => 'cashier',
                'added_by_id' => Auth::id(),
            ]);
        }

        AuditLog::record(
            $order->id,
            'item_added',
            null,
            ['item' => $menuItem->name, 'variant' => $variant ? $variant->name : null],
            "Cashier added {$menuItem->name} to Table " . ($order->table ? $order->table->table_number : 'Order'),
            Auth::id()
        );

        $this->recalculate();
    }

    public function updateItemQty(int $orderItemId, int $delta)
    {
        $item = OrderItem::with('order')->findOrFail($orderItemId);
        if ($item->order->isLocked()) {
            return;
        }

        $newQty = $item->qty + $delta;
        if ($newQty <= 0) {
            AuditLog::record(
                $item->order_id,
                'item_deleted',
                ['item' => $item->item_name, 'qty' => $item->qty],
                null,
                'Cashier removed item ' . $item->item_name,
                Auth::id()
            );
            $item->delete();
        } else {
            $item->qty = $newQty;
            $item->total_price = round($newQty * $item->unit_price, 2);
            $item->save();
        }

        $this->recalculate();
    }

    public function settleBill()
    {
        if (!$this->selectedOrderId) {
            return;
        }

        $this->recalculate();

        $order = Order::with('table')->findOrFail($this->selectedOrderId);
        $table = $order->table;

        $order->update([
            'status' => 'paid',
            'payment_mode' => $this->paymentMode,
            'customer_name' => $this->customerName ?: 'Walk-in Guest',
            'customer_phone' => $this->customerPhone ?: null,
            'paid_at' => now(),
        ]);

        if ($table) {
            // Also ensure any dangling open orders on this table are settled so no orphaned running orders remain
            Order::where('table_id', $table->id)
                ->whereIn('status', ['open', 'locked'])
                ->where('id', '!=', $order->id)
                ->update(['status' => 'cancelled']);

            $table->update([
                'status' => 'vacant',
                'current_order_id' => null,
            ]);
        }

        AuditLog::record(
            $order->id,
            'bill_settled',
            ['status' => 'locked'],
            ['status' => 'paid', 'final_amount' => $order->final_payable_amount, 'mode' => $this->paymentMode],
            "Bill settled via {$this->paymentMode} for ₹{$order->final_payable_amount}",
            Auth::id()
        );

        // Auto trigger thermal receipt print window
        $receiptUrl = route('order.receipt', ['order' => $order->id, 'autoprint' => 1]);
        $this->dispatch('open-receipt-window', url: $receiptUrl);

        // Reset POS selection
        $this->selectedOrderId = null;
        $this->selectTable($this->selectedTableId);
    }

    public function cancelCurrentOrder(string $reason = 'Cancelled by Cashier')
    {
        if (!$this->selectedOrderId) {
            return;
        }

        $order = Order::with('table')->find($this->selectedOrderId);
        if (!$order || $order->status === 'paid') {
            return;
        }

        $orderNumber = $order->order_number;
        $table = $order->table;
        $tableName = $table ? $table->table_number : 'Table';

        $order->update([
            'status' => 'cancelled',
            'notes' => trim(($order->notes ? $order->notes . ' | ' : '') . $reason),
        ]);

        $order->items()->update(['kot_status' => 'cancelled']);

        if ($table) {
            $table->update([
                'status' => 'vacant',
                'current_order_id' => null,
            ]);
        }

        AuditLog::record(
            $order->id,
            'order_cancelled',
            ['status' => 'open'],
            ['status' => 'cancelled'],
            "Order {$orderNumber} (Table {$tableName}) was cancelled by Cashier (" . (Auth::user()->name ?? 'Staff') . ")",
            Auth::id()
        );

        $this->dispatch('order-cancelled', message: "Order {$orderNumber} cancelled successfully.");
        $this->selectedOrderId = null;
        $this->selectTable($this->selectedTableId);
    }

    // Table Operations: Merge
    public function openMergeModal()
    {
        $this->targetMergeTableId = null;
        $this->showMergeModal = true;
    }

    public function confirmMerge()
    {
        if (!$this->selectedOrderId || !$this->targetMergeTableId) {
            return;
        }

        $sourceOrder = Order::with('items')->findOrFail($this->selectedOrderId);
        $targetTable = RestaurantTable::with('currentOrder')->findOrFail($this->targetMergeTableId);

        $targetOrder = $targetTable->currentOrder;
        if (!$targetOrder) {
            // Target table is vacant, just shift!
            $this->targetShiftTableId = $this->targetMergeTableId;
            $this->showMergeModal = false;
            $this->confirmShift();
            return;
        }

        // Move all items from source to target
        foreach ($sourceOrder->items as $item) {
            $item->update(['order_id' => $targetOrder->id]);
        }

        // Cancel old order and vacant old table
        $sourceOrder->update(['status' => 'cancelled']);
        RestaurantTable::where('id', $sourceOrder->table_id)->update([
            'status' => 'vacant',
            'current_order_id' => null,
        ]);

        AuditLog::record(
            $targetOrder->id,
            'table_merged',
            ['merged_from_order' => $sourceOrder->order_number],
            ['new_items_count' => $targetOrder->items()->count()],
            "Table {$sourceOrder->table->table_number} merged into Table {$targetTable->table_number}",
            Auth::id()
        );

        app(OrderCalculationService::class)->syncOrderTotals($targetOrder);

        $this->showMergeModal = false;
        $this->selectTable($targetTable->id);
    }

    // Table Operations: Shift
    public function openShiftModal()
    {
        $this->targetShiftTableId = null;
        $this->showShiftModal = true;
    }

    public function confirmShift()
    {
        if (!$this->selectedOrderId || !$this->targetShiftTableId) {
            return;
        }

        $order = Order::findOrFail($this->selectedOrderId);
        $oldTable = RestaurantTable::findOrFail($order->table_id);
        $newTable = RestaurantTable::findOrFail($this->targetShiftTableId);

        if ($newTable->status !== 'vacant') {
            return;
        }

        // Move order to new table
        $order->update(['table_id' => $newTable->id]);
        $oldTable->update(['status' => 'vacant', 'current_order_id' => null]);
        $newTable->update(['status' => $order->status === 'locked' ? 'locked' : 'occupied', 'current_order_id' => $order->id]);

        AuditLog::record(
            $order->id,
            'table_shifted',
            ['from_table' => $oldTable->table_number],
            ['to_table' => $newTable->table_number],
            "Order shifted from {$oldTable->table_number} to {$newTable->table_number}",
            Auth::id()
        );

        $this->showShiftModal = false;
        $this->selectTable($newTable->id);
    }

    // WhatsApp Direct Share
    public function shareOnWhatsApp()
    {
        if (!$this->selectedOrderId) {
            return;
        }

        $order = Order::findOrFail($this->selectedOrderId);
        $service = app(WhatsAppInvoiceService::class);
        $shareUrl = $service->getWhatsAppShareUrl($order, $this->customerPhone);

        $this->dispatch('open-external-window', url: $shareUrl);
    }

    // Customer UPI Display Modal Handlers
    public function openUpiModal()
    {
        $this->showUpiModal = true;
    }

    public function closeUpiModal()
    {
        $this->showUpiModal = false;
    }

    public function render()
    {
        $tables = RestaurantTable::with('currentOrder.items')->orderBy('table_number')->get();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $selectedTable = $this->selectedTableId ? RestaurantTable::find($this->selectedTableId) : null;
        $selectedOrder = $this->selectedOrderId ? Order::with(['items.menuItem', 'items.variant'])->find($this->selectedOrderId) : null;

        $menuQuery = MenuItem::with('variants')->where('is_available', true);
        if ($this->menuCategoryId) {
            $menuQuery->where('category_id', $this->menuCategoryId);
        }
        if (trim($this->menuSearch) !== '') {
            $menuQuery->where('name', 'like', '%' . $this->menuSearch . '%');
        }
        $menuItems = $menuQuery->take(30)->get();

        $vacantTables = RestaurantTable::where('status', 'vacant')->get();
        $otherTables = RestaurantTable::where('id', '!=', $this->selectedTableId)->get();

        return view('livewire.cashier.cashier-pos', compact(
            'tables',
            'categories',
            'selectedTable',
            'selectedOrder',
            'menuItems',
            'vacantTables',
            'otherTables'
        ))->layout('layouts.app', ['title' => 'Cashier Master POS - Dikshita Restro']);
    }
}
