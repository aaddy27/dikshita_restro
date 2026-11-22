<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT Ticket - {{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 4mm;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen text-slate-900 font-mono text-xs antialiased p-4">

    <!-- Screen toolbar -->
    <div class="no-print mb-4 flex items-center gap-3 bg-white p-3 rounded-xl shadow-md border border-slate-200">
        <button onclick="window.print()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-sans font-medium text-sm flex items-center gap-2 shadow-sm transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print KOT (Kitchen Ticket)
        </button>
        <button onclick="window.close()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-sans font-medium text-sm">
            Close
        </button>
    </div>

    <!-- 80mm KOT Container -->
    <div class="w-[80mm] bg-white p-4 shadow-xl border border-slate-300 mx-auto">
        <div class="text-center pb-2 border-b-2 border-slate-900">
            <span class="text-xs font-bold tracking-widest uppercase bg-slate-900 text-white px-2 py-0.5 rounded">KITCHEN ORDER TICKET</span>
            <div class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                TABLE: {{ $order->table ? $order->table->table_number : 'TAKEAWAY' }}
            </div>
            <p class="text-[11px] font-semibold text-slate-600">Order: #{{ $order->order_number }}</p>
        </div>

        <div class="py-2 border-b border-dashed border-slate-400 text-[11px] flex justify-between">
            <span>Time: <strong>{{ now()->format('h:i A') }}</strong></span>
            <span>Date: {{ now()->format('d/m/Y') }}</span>
        </div>

        <!-- Pending Items List -->
        <div class="py-2 border-b-2 border-slate-900">
            <div class="flex justify-between font-bold text-[11px] pb-1 border-b border-slate-300">
                <span class="w-[20%] text-center">QTY</span>
                <span class="w-[80%] text-left">ITEM NAME / VARIANT</span>
            </div>
            <div class="divide-y divide-slate-200 py-1 space-y-2">
                @forelse($items as $item)
                    <div class="flex justify-between items-start text-sm pt-2">
                        <div class="w-[20%] text-center font-black text-base tabular-nums bg-slate-100 rounded py-0.5">
                            {{ $item->qty }}x
                        </div>
                        <div class="w-[80%] pl-3 font-sans">
                            <span class="font-bold text-slate-900 text-sm leading-tight block">{{ $item->item_name }}</span>
                            @if($item->variant_name)
                                <span class="inline-block text-xs font-bold uppercase text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded mt-0.5">
                                    {{ $item->variant_name }}
                                </span>
                            @endif
                            <span class="text-[10px] text-slate-400 block mt-0.5">Added by: {{ ucfirst($item->added_by_type) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center text-slate-500 italic">No pending items for KOT</div>
                @endforelse
            </div>
        </div>

        @if($order->notes)
            <div class="py-2 border-b border-dashed border-slate-400 text-xs">
                <strong class="text-rose-600 uppercase">Special Instructions:</strong>
                <p class="font-sans italic text-slate-700 mt-0.5">{{ $order->notes }}</p>
            </div>
        @endif

        <div class="pt-3 text-center text-[10px] text-slate-500">
            KOT Ticket generated automatically &bull; Keep in sequence
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                window.print();
            }
        });
    </script>
</body>
</html>
