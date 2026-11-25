<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppInvoiceService
{
    /**
     * Generate and store PDF invoice for an order.
     *
     * @param Order $order
     * @return string File storage relative path
     */
    public function generatePdf(Order $order): string
    {
        $order->loadMissing(['table', 'items.menuItem', 'items.variant']);

        $restaurant = [
            'name' => config('app.restaurant_name', 'Dikshita Restro & Cafe'),
            'address' => config('app.restaurant_address', 'Murlidhar Vyas Colony,
Near Chura Gali, Bikaner 334001, Bkn-334001'),
            'phone' => config('app.restaurant_phone', '+91 80944 26174'),
            'email' => config('app.restaurant_email', 'billing@dikshitarestro.com'),
            'gstin' => config('app.restaurant_gstin', '29AABCU9603R1ZM'),
            'fssai' => config('app.restaurant_fssai', '11223344556677'),
        ];

        $itemCount = $order->items->count();
        $heightPt = max(450, 370 + ($itemCount * 26));

        $pdf = Pdf::loadView('receipts.thermal_pdf', [
            'order' => $order,
            'restaurant' => $restaurant,
        ]);

        // 80mm roll width = 226.77pt
        $pdf->setPaper([0, 0, 226.77, $heightPt], 'portrait');

        $fileName = 'invoices/' . $order->order_number . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        return $fileName;
    }

    /**
     * Get accessible URL for the generated PDF invoice.
     */
    public function getPdfUrl(Order $order): string
    {
        $fileName = 'invoices/' . $order->order_number . '.pdf';

        if (!Storage::disk('public')->exists($fileName)) {
            $this->generatePdf($order);
        }

        return route('order.pdf', $order->id);
    }

    /**
     * Format a WhatsApp message text for the order.
     */
    public function buildWhatsAppMessage(Order $order, ?string $pdfUrl = null): string
    {
        $order->loadMissing(['table', 'items']);
        $pdfUrl = $pdfUrl ?: $this->getPdfUrl($order);

        $restroName = config('app.restaurant_name', 'Dikshita Restro & Cafe');
        $lines = [];
        $lines[] = "🍽️ *{$restroName}*";
        $lines[] = "Thank you for dining with us!";
        $lines[] = "--------------------------------";
        $lines[] = "🧾 *Invoice #:* {$order->order_number}";
        $lines[] = "🪑 *Table:* " . ($order->table ? $order->table->table_number : 'Takeaway');
        $lines[] = "📅 *Date:* " . ($order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'));
        $lines[] = "--------------------------------";
        $lines[] = "*Items Ordered:*";

        foreach ($order->items as $item) {
            $variant = $item->variant_name ? " ({$item->variant_name})" : '';
            $lines[] = "• {$item->qty}x {$item->item_name}{$variant} - ₹" . number_format($item->total_price, 2);
        }

        $lines[] = "--------------------------------";
        $lines[] = "Subtotal: ₹" . number_format($order->subtotal, 2);

        if ($order->day_discount_amount > 0) {
            $lines[] = "Day Discount: -₹" . number_format($order->day_discount_amount, 2);
        }
        if ($order->manual_discount_amount > 0) {
            $lines[] = "Cashier Discount: -₹" . number_format($order->manual_discount_amount, 2);
        }

        $lines[] = "GST (2.5% CGST + 2.5% SGST): ₹" . number_format($order->tax_amount, 2);
        $lines[] = "💰 *Final Total: ₹" . number_format($order->final_payable_amount, 2) . "*";
        $lines[] = "Payment Mode: " . strtoupper($order->payment_mode ?: 'Settled');
        $lines[] = "💳 *Instant UPI Pay Link:* " . $order->getUpiPaymentUrl();
        $lines[] = "--------------------------------";
        $lines[] = "📄 *Download Thermal Bill (PDF):* {$pdfUrl}";
        $lines[] = "We hope to see you again soon! ✨";

        return implode("\n", $lines);
    }

    /**
     * Generate a direct wa.me click-to-chat URL.
     */
    public function getWhatsAppShareUrl(Order $order, ?string $phone = null): string
    {
        $targetPhone = preg_replace('/[^0-9]/', '', $phone ?: ($order->customer_phone ?: ''));
        // If 10-digit Indian number, prepend 91
        if (strlen($targetPhone) === 10) {
            $targetPhone = '91' . $targetPhone;
        }

        $message = $this->buildWhatsAppMessage($order);
        $encoded = urlencode($message);

        if (!empty($targetPhone)) {
            return "https://wa.me/{$targetPhone}?text={$encoded}";
        }

        return "https://api.whatsapp.com/send?text={$encoded}";
    }

    /**
     * Send invoice via Meta WhatsApp Cloud API if configured, otherwise fallback to share URL.
     */
    public function sendInvoice(Order $order, string $phone): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $token = env('META_WHATSAPP_TOKEN');
        $phoneNumberId = env('META_WHATSAPP_PHONE_NUMBER_ID');
        $pdfUrl = $this->getPdfUrl($order);
        $messageText = $this->buildWhatsAppMessage($order, $pdfUrl);

        if ($token && $phoneNumberId) {
            try {
                $response = Http::withToken($token)
                    ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to' => $cleanPhone,
                        'type' => 'text',
                        'text' => [
                            'preview_url' => true,
                            'body' => $messageText,
                        ],
                    ]);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'method' => 'meta_api',
                        'data' => $response->json(),
                        'share_url' => $this->getWhatsAppShareUrl($order, $phone),
                    ];
                }

                Log::warning('Meta WhatsApp Cloud API error: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('Meta WhatsApp Cloud API Exception: ' . $e->getMessage());
            }
        }

        // Return fallback share URL
        return [
            'success' => true,
            'method' => 'wa_me_link',
            'share_url' => $this->getWhatsAppShareUrl($order, $phone),
            'message' => $messageText,
        ];
    }
}
