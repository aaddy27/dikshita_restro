<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <!-- Header & Date Context -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Master Operations</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Executive POS Dashboard</h1>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
                📅 {{ now()->format('l, d F Y') }}
            </span>
            <a href="{{ route('cashier.pos') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all active:scale-95 flex items-center gap-1.5">
                <span>Go to Master POS</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards (Material Elevation & Tabular Numerals) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today's Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Today's Revenue</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                    ₹
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 font-mono-numbers">
                    ₹{{ number_format($todayRevenue, 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $paidOrdersCount }} settled orders today</p>
            </div>
        </div>

        <!-- Active Tables -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Table Occupancy</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                    🪑
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 font-mono-numbers">
                    {{ $activeTablesCount }} <span class="text-sm font-normal text-slate-400">/ {{ $totalTablesCount }}</span>
                </div>
                <p class="text-[11px] text-amber-700 font-medium mt-0.5">{{ round(($activeTablesCount / max(1, $totalTablesCount)) * 100) }}% occupancy rate</p>
            </div>
        </div>

        <!-- Total Discounts Given -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Discounts Conceded</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    %
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 font-mono-numbers">
                    ₹{{ number_format($totalDiscountsToday, 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Day promos & cashier overrides</p>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Avg. Order Value (AOV)</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">
                    📊
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 font-mono-numbers">
                    ₹{{ number_format($avgOrderValue, 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Per dining table ticket</p>
            </div>
        </div>
    </div>

    <!-- Live Tables Mini Status Overview -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Floor Plan Status</h3>
                <p class="text-xs text-slate-500">Real-time table occupancy & locks</p>
            </div>
            <a href="{{ route('admin.tables') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                Manage Tables &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3">
            @foreach($tables as $t)
                <div class="p-3 rounded-xl border text-center {{ $t->status === 'vacant' ? 'bg-emerald-50/60 border-emerald-200 text-emerald-800' : ($t->status === 'occupied' ? 'bg-amber-50/70 border-amber-300 text-amber-900' : 'bg-rose-50/70 border-rose-300 text-rose-900') }}">
                    <div class="font-extrabold text-sm font-mono-numbers">{{ $t->table_number }}</div>
                    <div class="text-[10px] uppercase font-bold mt-0.5">{{ $t->status }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Recent Restaurant Orders</h3>
            <a href="{{ route('admin.reports') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                View Full Sales Report &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-3">Table</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Subtotal</th>
                        <th class="py-3 px-3 text-right">Discount</th>
                        <th class="py-3 px-3 text-right">Tax (5%)</th>
                        <th class="py-3 px-4 text-right">Final Amount</th>
                        <th class="py-3 px-3 text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $ro)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $ro->order_number }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-700">
                                {{ $ro->table ? $ro->table->table_number : 'Takeaway' }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $ro->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($ro->status === 'locked' ? 'bg-rose-100 text-rose-800' : ($ro->status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $ro->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono-numbers">
                                ₹{{ number_format($ro->subtotal, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono-numbers text-emerald-700">
                                ₹{{ number_format($ro->total_discount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono-numbers text-slate-500">
                                ₹{{ number_format($ro->tax_amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono-numbers font-black text-slate-900 text-sm">
                                ₹{{ number_format($ro->final_payable_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="{{ route('order.receipt', $ro->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-bold">
                                    Print 80mm
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No orders recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
