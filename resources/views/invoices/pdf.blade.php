<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.4;
            padding: 24px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
        }
        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 9px 8px;
            font-size: 11px;
        }
        .items-table td.num, .items-table th.num {
            text-align: right;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 11px;
        }
        .totals-table tr.grand-total td {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            color: #1e1b4b;
            padding: 8px 8px;
            background: #f8fafc;
        }
        .footer {
            margin-top: 36px;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = public_path('images/logo-icon.jpg');
            $logoBase64 = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        @endphp
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="width: 55px; height: 55px; border-radius: 10px; margin-bottom: 8px;" alt="Logo"><br>
        @endif
        <div class="title">{{ $restaurant['name'] }}</div>
        <div>{{ $restaurant['address'] }}</div>
        <div>Phone: {{ $restaurant['phone'] }} | Email: {{ $restaurant['email'] }}</div>
        <div style="margin-top: 6px; font-weight: 600; color: #475569;">
            GSTIN: {{ $restaurant['gstin'] }} | FSSAI Lic: {{ $restaurant['fssai'] }}
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>INVOICE TO:</strong><br>
                Customer: {{ $order->customer_name ?: 'Walk-in Guest' }}<br>
                Phone: {{ $order->customer_phone ?: 'N/A' }}<br>
                Table: <span class="badge">{{ $order->table ? $order->table->table_number : 'Takeaway' }}</span>
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>INVOICE DETAILS:</strong><br>
                Invoice #: <strong>{{ $order->order_number }}</strong><br>
                Date & Time: {{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}<br>
                Status: <span class="badge">{{ strtoupper($order->status) }}</span><br>
                Payment: {{ strtoupper($order->payment_mode ?: 'N/A') }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 47%;">Item Description</th>
                <th class="num" style="width: 15%;">Unit Price</th>
                <th class="num" style="width: 10%;">Qty</th>
                <th class="num" style="width: 20%;">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if($item->variant_name)
                            <span style="color: #6366f1;">({{ $item->variant_name }})</span>
                        @endif
                    </td>
                    <td class="num">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">{{ $item->qty }}</td>
                    <td class="num">₹{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <!-- Left: Dynamic UPI QR Code for Exact Final Amount -->
            <td style="width: 48%; vertical-align: top; border: 1px dashed #cbd5e1; padding: 12px; border-radius: 6px; background-color: #f8fafc;">
                <div style="font-weight: bold; font-size: 11px; color: #4338ca; text-transform: uppercase; margin-bottom: 6px; text-align: center;">
                    Scan & Pay via UPI (Exact Amount)
                </div>
                <div style="text-align: center; margin: 4px 0;">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(105)->generate($order->getUpiPaymentUrl())) }}" width="105" height="105" />
                </div>
                <div style="text-align: center; font-size: 10px; color: #334155;">
                    <div style="font-size: 13px; font-weight: bold; color: #1e1b4b; margin-top: 2px;">
                        Pay: ₹{{ number_format($order->final_payable_amount, 2) }}
                    </div>
                    <div style="font-family: monospace; color: #64748b; margin-top: 2px;">
                        {{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}
                    </div>
                    <div style="font-size: 9px; color: #94a3b8; margin-top: 3px;">
                        GPay &bull; PhonePe &bull; Paytm &bull; BHIM
                    </div>
                </div>
            </td>

            <td style="width: 4%;"></td>

            <!-- Right: Totals Table -->
            <td style="width: 48%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 6px; font-size: 11px;">Item Subtotal:</td>
                        <td style="padding: 4px 6px; font-size: 11px; text-align: right;">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->day_discount_amount > 0)
                        <tr style="color: #059669;">
                            <td style="padding: 4px 6px; font-size: 11px;">Day Discount:</td>
                            <td style="padding: 4px 6px; font-size: 11px; text-align: right;">-₹{{ number_format($order->day_discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($order->manual_discount_amount > 0)
                        <tr style="color: #059669;">
                            <td style="padding: 4px 6px; font-size: 11px;">Cashier Discount:</td>
                            <td style="padding: 4px 6px; font-size: 11px; text-align: right;">-₹{{ number_format($order->manual_discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding: 4px 6px; font-size: 11px;">Taxable Subtotal:</td>
                        <td style="padding: 4px 6px; font-size: 11px; text-align: right;">₹{{ number_format(max(0, $order->subtotal - $order->day_discount_amount - $order->manual_discount_amount), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 6px; font-size: 11px;">CGST (2.5%):</td>
                        <td style="padding: 4px 6px; font-size: 11px; text-align: right;">₹{{ number_format($order->cgst_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 6px; font-size: 11px;">SGST (2.5%):</td>
                        <td style="padding: 4px 6px; font-size: 11px; text-align: right;">₹{{ number_format($order->sgst_amount, 2) }}</td>
                    </tr>
                    <tr style="border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1; background: #f8fafc; font-weight: bold; color: #1e1b4b;">
                        <td style="padding: 8px 6px; font-size: 13px;">Final Payable:</td>
                        <td style="padding: 8px 6px; font-size: 13px; text-align: right;">₹{{ number_format($order->final_payable_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div>This is a computer-generated Tax Invoice and requires no physical signature.</div>
        <div style="margin-top: 4px; font-weight: 600;">Thank you for dining with us! Visit again.</div>
    </div>
</body>
</html>
