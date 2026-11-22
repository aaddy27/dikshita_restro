<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Security & Integrity</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tamper-Proof Audit Trail</h1>
        </div>

        <!-- Filter Controls -->
        <div class="flex items-center gap-3">
            <input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="Search order # or remarks..."
                class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs focus:outline-none focus:border-indigo-600 shadow-2xs w-48 sm:w-64"
            >

            <select wire:model.live="actionFilter" class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:border-indigo-600 shadow-2xs">
                <option value="all">All Security Events</option>
                <option value="bill_locked">Bill Locked</option>
                <option value="bill_unlocked">Bill Unlocked (PIN)</option>
                <option value="item_deleted">Item Deleted</option>
                <option value="table_merged">Table Merged</option>
                <option value="table_shifted">Table Shifted</option>
                <option value="kot_printed">KOT Printed</option>
                <option value="bill_settled">Bill Settled</option>
            </select>
        </div>
    </div>

    <!-- Audit Log Records Table -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-3">Action Event</th>
                        <th class="py-3 px-3">Order / Table</th>
                        <th class="py-3 px-3">Authorized User</th>
                        <th class="py-3 px-4">Audit Details & Remarks</th>
                        <th class="py-3 px-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                            </td>

                            <td class="py-3.5 px-3">
                                @php
                                    $chipClass = match($log->action) {
                                        'bill_unlocked' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'bill_locked' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'item_deleted' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'bill_settled' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'table_merged', 'table_shifted' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $chipClass }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>

                            <td class="py-3.5 px-3 font-semibold text-slate-800">
                                @if($log->order)
                                    <span class="font-mono text-slate-900 block font-bold">{{ $log->order->order_number }}</span>
                                    <span class="text-[10px] text-slate-400">Table: {{ $log->order->table ? $log->order->table->table_number : 'Takeaway' }}</span>
                                @else
                                    <span class="text-slate-400">--</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3">
                                @if($log->user)
                                    <span class="font-bold text-slate-900 block">{{ $log->user->name }}</span>
                                    <span class="text-[10px] font-semibold uppercase text-slate-400">({{ $log->user->role }})</span>
                                @else
                                    <span class="text-slate-400">System</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-slate-700">
                                <div>{{ $log->remarks ?: 'No additional remarks' }}</div>
                                @if($log->old_value || $log->new_value)
                                    <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-slate-500">
                                        @if($log->old_value)
                                            <span class="bg-rose-50 text-rose-700 px-1.5 py-0.2 rounded border border-rose-100">
                                                Old: {{ json_encode($log->old_value) }}
                                            </span>
                                        @endif
                                        @if($log->new_value)
                                            <span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.2 rounded border border-emerald-100">
                                                New: {{ json_encode($log->new_value) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-slate-400 font-mono text-[11px]">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">
                                No audit log records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
