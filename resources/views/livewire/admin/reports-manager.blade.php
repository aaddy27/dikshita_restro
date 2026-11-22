<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <!-- Header & Date Picker -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Financial Reports</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daily Sales & Settlement Report</h1>
        </div>

        <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-600">Select Date:</label>
            <input
                type="date"
                wire:model.live="selectedDate"
                class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold font-mono focus:outline-none focus:border-indigo-600 shadow-2xs"
            >
        </div>
    </div>

    <!-- Revenue Breakdown Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Net Collection</span>
            <div class="text-2xl font-black text-slate-900 font-mono-numbers">
                ₹{{ number_format($totalRevenue, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">{{ $orders->count() }} paid bills</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">UPI / Digital QR</span>
            <div class="text-2xl font-black text-indigo-700 font-mono-numbers">
                ₹{{ number_format($upiTotal, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">{{ $orders->where('payment_mode', 'upi')->count() }} transactions</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Cash Drawer Collection</span>
            <div class="text-2xl font-black text-emerald-700 font-mono-numbers">
                ₹{{ number_format($cashTotal, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">{{ $orders->where('payment_mode', 'cash')->count() }} transactions</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Card / EDC POS</span>
            <div class="text-2xl font-black text-slate-700 font-mono-numbers">
                ₹{{ number_format($cardTotal, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">{{ $orders->where('payment_mode', 'card')->count() }} transactions</p>
        </div>
    </div>

    <!-- Taxes & Concessions Sub-Strip -->
    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 flex flex-wrap items-center justify-around gap-4 text-xs">
        <div>
            <span class="text-slate-500 block">Gross Item Subtotal:</span>
            <strong class="font-mono-numbers text-slate-800 text-sm">₹{{ number_format($totalSubtotal, 2) }}</strong>
        </div>
        <div class="h-6 w-px bg-slate-300"></div>
        <div>
            <span class="text-slate-500 block">Total Discounts Conceded:</span>
            <strong class="font-mono-numbers text-emerald-700 text-sm">-₹{{ number_format($totalDiscounts, 2) }}</strong>
        </div>
        <div class="h-6 w-px bg-slate-300"></div>
        <div>
            <span class="text-slate-500 block">CGST Collected (2.5%):</span>
            <strong class="font-mono-numbers text-slate-800 text-sm">₹{{ number_format($totalTaxes / 2, 2) }}</strong>
        </div>
        <div class="h-6 w-px bg-slate-300"></div>
        <div>
            <span class="text-slate-500 block">SGST Collected (2.5%):</span>
            <strong class="font-mono-numbers text-slate-800 text-sm">₹{{ number_format($totalTaxes / 2, 2) }}</strong>
        </div>
    </div>

    <!-- Itemized Settled Bills Table -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Settled Bills for {{ $date->format('d F Y') }}</h3>
            <span class="text-xs text-slate-500">{{ $orders->count() }} Invoices</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-3">Table</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">Payment Mode</th>
                        <th class="py-3 px-3 text-right">Subtotal</th>
                        <th class="py-3 px-3 text-right">Discount</th>
                        <th class="py-3 px-3 text-right">Tax (5%)</th>
                        <th class="py-3 px-4 text-right">Net Amount</th>
                        <th class="py-3 px-3 text-center">Receipts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold font-mono text-slate-900">
                                {{ $order->order_number }}
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-700">
                                {{ $order->table ? $order->table->table_number : 'Takeaway' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $order->customer_name ?: 'Walk-in' }}
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_mode === 'cash' ? 'bg-emerald-100 text-emerald-800' : ($order->payment_mode === 'upi' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-800') }}">
                                    {{ $order->payment_mode }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono-numbers">
                                ₹{{ number_format($order->subtotal, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono-numbers text-emerald-700">
                                ₹{{ number_format($order->total_discount, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono-numbers text-slate-500">
                                ₹{{ number_format($order->tax_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono-numbers font-black text-slate-900 text-sm">
                                ₹{{ number_format($order->final_payable_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('order.receipt', $order->id) }}" target="_blank" class="text-indigo-600 font-bold hover:underline">
                                        80mm
                                    </a>
                                    <span class="text-slate-300">&bull;</span>
                                    <a href="{{ route('order.pdf', $order->id) }}" target="_blank" class="text-slate-600 font-bold hover:underline">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400 italic">
                                No settled orders found on {{ $date->format('d M Y') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
