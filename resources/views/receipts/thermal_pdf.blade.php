<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 4mm 3mm 4mm 3mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #000000;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .header {
            text-align: center;
            padding-bottom: 6px;
            border-bottom: 1px dashed #000000;
            margin-bottom: 6px;
        }
        .restaurant-title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .sub-header {
            font-size: 8pt;
            line-height: 1.25;
            color: #222222;
        }
        .meta-section {
            padding: 4px 0;
            border-bottom: 1px dashed #000000;
            font-size: 8.5pt;
        }
        .meta-row {
            width: 100%;
            margin-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 8.5pt;
        }
        .items-table th {
            border-bottom: 1px solid #000000;
            padding: 4px 1px;
            font-size: 8.5pt;
        }
        .items-table td {
            padding: 3px 1px;
            vertical-align: top;
        }
        .totals-section {
            border-top: 1px dashed #000000;
            padding: 5px 0;
            font-size: 8.5pt;
        }
        .total-row {
            width: 100%;
            margin-bottom: 2px;
        }
        .grand-total-box {
            border-top: 2px solid #000000;
            border-bottom: 2px solid #000000;
            padding: 6px 0;
            margin: 6px 0;
            font-size: 11pt;
            font-weight: bold;
        }
        .upi-box {
            border-bottom: 1px dashed #000000;
            padding: 8px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            font-size: 7.5pt;
            padding-top: 8px;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    <!-- Restaurant Header -->
    <div class="header">
        @php
            $logoPath = public_path('images/logo-icon.jpg');
            $logoBase64 = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        @endphp
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="width: 48px; height: 48px; border-radius: 8px; margin-bottom: 5px;" alt="Logo"><br>
        @endif
        <div class="restaurant-title">{{ $restaurant['name'] ?? config('app.restaurant_name', "Deekshita's Family Restaurant") }}</div>
        <div class="sub-header">
            {{ $restaurant['address'] ?? config('app.restaurant_address', 'Main Street, Tech Zone, Bengaluru') }}<br>
            Ph: {{ $restaurant['phone'] ?? config('app.restaurant_phone', '+91 98765 43210') }}<br>
            GSTIN: {{ $restaurant['gstin'] ?? config('app.restaurant_gstin', '29AABCU9603R1ZM') }} | FSSAI: {{ $restaurant['fssai'] ?? config('app.restaurant_fssai', '11223344556677') }}
        </div>
    </div>

    <!-- Bill Meta Details -->
    <div class="meta-section">
        <table style="width: 100%;">
            <tr>
                <td class="bold">Bill #: {{ $order->order_number }}</td>
                <td class="text-right bold">Table: {{ $order->table ? $order->table->table_number : 'Takeaway' }}</td>
            </tr>
            <tr>
                <td>Date: {{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}</td>
                <td class="text-right uppercase">Type: Dine-In</td>
            </tr>
            @if($order->customer_phone)
                <tr>
                    <td colspan="2">Customer: {{ $order->customer_name ?: 'Guest' }} ({{ $order->customer_phone }})</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Itemized List -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 48%;">ITEM</th>
                <th class="text-center" style="width: 14%;">QTY</th>
                <th class="text-right" style="width: 18%;">RATE</th>
                <th class="text-right" style="width: 20%;">AMT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td class="text-left">
                        {{ $item->item_name }}
                        @if($item->variant_name)
                            <br><small style="color: #444;">({{ $item->variant_name }})</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right bold">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals & Taxes Breakdown -->
    <div class="totals-section">
        <table style="width: 100%;">
            <tr>
                <td>Subtotal ({{ $order->items->sum('qty') }} items):</td>
                <td class="text-right bold">Rs {{ number_format($order->subtotal, 2) }}</td>
            </tr>

            @if($order->day_discount_amount > 0)
                <tr>
                    <td>Day Discount:</td>
                    <td class="text-right bold">-Rs {{ number_format($order->day_discount_amount, 2) }}</td>
                </tr>
            @endif

            @if($order->manual_discount_amount > 0)
                <tr>
                    <td>Cashier Discount:</td>
                    <td class="text-right bold">-Rs {{ number_format($order->manual_discount_amount, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td>Taxable Amount:</td>
                <td class="text-right">Rs {{ number_format(max(0, $order->subtotal - $order->day_discount_amount - $order->manual_discount_amount), 2) }}</td>
            </tr>
            <tr>
                <td>CGST (2.5%):</td>
                <td class="text-right">Rs {{ number_format($order->cgst_amount, 2) }}</td>
            </tr>
            <tr>
                <td>SGST (2.5%):</td>
                <td class="text-right">Rs {{ number_format($order->sgst_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Grand Total Box -->
    <div class="grand-total-box">
        <table style="width: 100%;">
            <tr>
                <td class="uppercase">TOTAL PAYABLE:</td>
                <td class="text-right">Rs {{ number_format($order->final_payable_amount, 2) }}</td>
            </tr>
        </table>
        <div style="font-size: 8pt; font-weight: normal; margin-top: 3px;">
            Payment: {{ strtoupper($order->payment_mode ?: 'UPI / SETTLED') }}
        </div>
    </div>

    <!-- Dynamic Real-Time UPI QR Code for Exact Amount -->
    <div class="upi-box">
        <div style="font-size: 9pt; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 4px;">
            SCAN & PAY VIA ANY UPI APP
        </div>
        <div style="margin: 4px 0;">
            <img src="{{ $order->getUpiQrBase64(125) }}" width="115" height="115" style="display: block; margin: 0 auto;" alt="UPI QR Code" />
        </div>
        <div style="font-size: 10pt; font-weight: bold; margin-top: 2px;">
            AMOUNT: Rs {{ number_format($order->final_payable_amount, 2) }}
        </div>
        <div style="font-size: 8pt; color: #333333; margin-top: 1px;">
            {{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}
        </div>
        <div style="font-size: 7.5pt; color: #555555; text-transform: uppercase; margin-top: 2px;">
            GPay &bull; PhonePe &bull; Paytm &bull; BHIM
        </div>
    </div>

    <!-- Receipt Footer -->
    <div class="footer">
        <div class="bold">THANK YOU FOR YOUR VISIT!</div>
        <div>Please Visit Again</div>
        <div style="color: #666; margin-top: 2px;">Powered by Dikshita Restro POS</div>
    </div>

</body>
</html>
