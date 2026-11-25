<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Receipt - {{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            html, body {
                width: 76mm !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .thermal-container {
                width: 76mm !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 2mm 1mm !important;
                border: none !important;
                box-shadow: none !important;
            }
            .thermal-container * {
                color: #000000 !important;
                border-color: #000000 !important;
            }
            .thermal-qr-img {
                display: block !important;
                margin: 4px auto !important;
                width: 125px !important;
                height: 125px !important;
                image-rendering: -webkit-optimize-contrast !important;
                image-rendering: crisp-edges !important;
                image-rendering: pixelated !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen text-slate-800 font-mono text-xs antialiased p-4">

    <!-- Screen-only action toolbar -->
    <div class="no-print mb-4 flex flex-wrap items-center justify-center gap-2 bg-white p-3 rounded-2xl shadow-md border border-slate-200">
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans font-bold text-xs flex items-center gap-2 shadow-xs transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Receipt (80mm)</span>
        </button>

        <a href="{{ route('order.pdf', $order->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-sans font-bold text-xs flex items-center gap-1.5 shadow-xs transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Download 80mm PDF</span>
        </a>

        <a href="{{ route('order.whatsapp', ['order' => $order->id, 'phone' => $order->customer_phone]) }}" target="_blank" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl font-sans font-bold text-xs flex items-center gap-1.5 transition-all">
            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
            <span>Share WhatsApp</span>
        </a>

        <button onclick="window.close()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-sans font-medium text-xs">
            Close
        </button>
    </div>

    <!-- 80mm Thermal Receipt Container -->
    <div class="thermal-container w-[80mm] max-w-[80mm] bg-white p-4 shadow-xl border border-slate-200 text-slate-900 mx-auto">
        <!-- Restaurant Header -->
        <div class="text-center pb-3 border-b border-dashed border-slate-400">
            <div class="flex justify-center mb-1.5">
                <img src="{{ asset('images/logo-icon.jpg') }}" alt="Logo" class="w-12 h-12 object-contain rounded-lg border border-slate-300">
            </div>
            <h1 class="text-base font-black tracking-tight uppercase">DEEKSHITA'S FAMILY RESTAURANT</h1>
            <p class="text-[11px] text-slate-600 leading-tight">Murlidhar Vyas Colony,
Near Chura Gali, Bikaner 334001</p>
            <p class="text-[11px] text-slate-600">Ph:+91 80944 26174</p>
            <p class="text-[10px] text-slate-500 mt-1">GSTIN: 29AABCU9603R1ZM | FSSAI: 11223344556677</p>
        </div>

        <!-- Meta Details -->
        <div class="py-2 border-b border-dashed border-slate-400 text-[11px] space-y-1">
            <div class="flex justify-between">
                <span>Bill #: <strong class="tabular-nums">{{ $order->order_number }}</strong></span>
                <span>Table: <strong class="text-xs uppercase bg-slate-100 px-1.5 py-0.5 rounded">{{ $order->table ? $order->table->table_number : 'TAKEAWAY' }}</strong></span>
            </div>
            <div class="flex justify-between">
                <span>Date: {{ $order->created_at ? $order->created_at->format('d/m/y h:i A') : now()->format('d/m/y h:i A') }}</span>
                <span>Type: DINE-IN</span>
            </div>
            @if($order->customer_phone)
                <div class="flex justify-between text-slate-600">
                    <span>Customer: {{ $order->customer_name ?: 'Guest' }}</span>
                    <span>Ph: {{ $order->customer_phone }}</span>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="py-2 border-b border-dashed border-slate-400">
            <div class="flex justify-between font-bold text-[11px] pb-1 border-b border-slate-300">
                <span class="w-[45%] text-left">ITEM</span>
                <span class="w-[15%] text-center">QTY</span>
                <span class="w-[20%] text-right">RATE</span>
                <span class="w-[20%] text-right">AMT</span>
            </div>
            <div class="divide-y divide-dotted divide-slate-200 py-1 space-y-1">
                @foreach($order->items as $item)
                    <div class="flex justify-between text-[11px] pt-1">
                        <div class="w-[45%] text-left font-sans">
                            <span class="font-medium text-slate-800">{{ $item->item_name }}</span>
                            @if($item->variant_name)
                                <span class="text-[10px] text-indigo-600 font-semibold block">({{ $item->variant_name }})</span>
                            @endif
                        </div>
                        <div class="w-[15%] text-center tabular-nums">{{ $item->qty }}</div>
                        <div class="w-[20%] text-right tabular-nums">{{ number_format($item->unit_price, 2) }}</div>
                        <div class="w-[20%] text-right font-semibold tabular-nums">{{ number_format($item->total_price, 2) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Totals & Taxes Breakdown -->
        <div class="py-2 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
            <div class="flex justify-between text-slate-700">
                <span>Subtotal ({{ $order->items->sum('qty') }} items):</span>
                <span class="tabular-nums font-semibold">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if($order->day_discount_amount > 0)
                <div class="flex justify-between text-emerald-700">
                    <span>Day Discount:</span>
                    <span class="tabular-nums font-semibold">-₹{{ number_format($order->day_discount_amount, 2) }}</span>
                </div>
            @endif

            @if($order->manual_discount_amount > 0)
                <div class="flex justify-between text-emerald-700">
                    <span>Cashier Discount:</span>
                    <span class="tabular-nums font-semibold">-₹{{ number_format($order->manual_discount_amount, 2) }}</span>
                </div>
            @endif

            <div class="flex justify-between text-slate-600 pt-1 border-t border-dotted border-slate-300">
                <span>Taxable Amount:</span>
                <span class="tabular-nums font-medium">₹{{ number_format(max(0, $order->subtotal - $order->day_discount_amount - $order->manual_discount_amount), 2) }}</span>
            </div>

            <div class="flex justify-between text-slate-600">
                <span>CGST (2.5%):</span>
                <span class="tabular-nums">₹{{ number_format($order->cgst_amount, 2) }}</span>
            </div>

            <div class="flex justify-between text-slate-600">
                <span>SGST (2.5%):</span>
                <span class="tabular-nums">₹{{ number_format($order->sgst_amount, 2) }}</span>
            </div>
        </div>

        <!-- Grand Total -->
        <div class="py-2.5 border-b-2 border-slate-900">
            <div class="flex justify-between items-center text-sm font-bold">
                <span class="tracking-wide">TOTAL PAYABLE:</span>
                <span class="tabular-nums text-base">₹{{ number_format($order->final_payable_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-[11px] text-slate-600 mt-1">
                <span>Payment Mode:</span>
                <span class="uppercase font-semibold text-slate-800">{{ $order->payment_mode ?: 'UPI / PENDING' }}</span>
            </div>
        </div>

        <!-- Dynamic Real-Time UPI QR Code for Exact Final Amount -->
        <div class="py-3 border-b-2 border-dashed border-slate-400 text-center space-y-1">
            <div class="text-[11px] font-bold tracking-tight uppercase">SCAN & PAY VIA ANY UPI APP</div>
            <div class="flex justify-center py-1">
                <div class="p-1 bg-white border border-slate-300 rounded inline-block">
                    <img
                        src="{{ $order->getUpiQrBase64(130) }}"
                        class="thermal-qr-img mx-auto block"
                        width="125"
                        height="125"
                        alt="UPI QR Code"
                        style="display: block; margin: 0 auto; width: 125px; height: 125px; image-rendering: crisp-edges; image-rendering: pixelated;"
                    />
                </div>
            </div>
            <div class="text-xs font-bold tabular-nums">AMOUNT: ₹{{ number_format($order->final_payable_amount, 2) }}</div>
            <div class="text-[10px] text-slate-600 font-mono">{{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}</div>
            <div class="text-[9px] text-slate-500 uppercase font-semibold">GPay &bull; PhonePe &bull; Paytm &bull; BHIM</div>
        </div>

        <!-- Footer -->
        <div class="text-center pt-3 text-[10px] text-slate-600 space-y-1">
            <p class="font-semibold text-slate-800">THANK YOU FOR YOUR VISIT!</p>
            <p>Scan to Pay / Order Next Time</p>
            <p class="text-[9px] text-slate-400">Powered by Dikshita Restro POS</p>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Auto open print dialog if requested
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                window.print();
            }
        });
    </script>
</body>
</html>
