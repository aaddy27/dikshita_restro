<div wire:poll.4s class="space-y-6 max-w-7xl mx-auto py-4">

    <!-- Top Kitchen Header & KPI Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/90 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center font-black text-2xl shadow-md border border-amber-400">
                🍳
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kitchen Order Display (Cook Portal)</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        LIVE SYNC (4s)
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Orders to prepare from Table QRs &amp; Waiters. Last checked: <span class="font-mono font-bold text-slate-700">{{ $lastRefreshedAt }}</span>
                </p>
            </div>
        </div>

        <!-- Kitchen Stats Counters (Food quantities only) -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
            <!-- Total Items -->
            <div class="px-4 py-2 bg-slate-900 text-white rounded-2xl flex items-center gap-2.5 shadow-sm">
                <span class="text-base font-black text-amber-400 font-mono-numbers">{{ $totalItemsToCook }}</span>
                <span class="font-bold text-[11px] uppercase tracking-wider text-slate-300">Items to Cook</span>
            </div>

            <!-- 100% Pure Veg Badge -->
            <div class="px-3.5 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="font-extrabold text-[11px] uppercase tracking-wider">100% Pure Veg (Shuddh Shakahari) Kitchen</span>
            </div>
        </div>
    </div>

    <!-- Filters & Sound Alerts Toolbar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-2xs">
        <!-- Segmented Filter Buttons -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-semibold w-full sm:w-auto">
            <button
                type="button"
                wire:click="setFilter('active')"
                class="flex-1 sm:flex-none px-4 py-2 rounded-lg transition-all cursor-pointer {{ $filter === 'active' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
            >
                🔥 Active To-Cook ({{ $totalItemsToCook }})
            </button>
            <button
                type="button"
                wire:click="setFilter('all')"
                class="flex-1 sm:flex-none px-3 py-2 rounded-lg transition-all cursor-pointer {{ $filter === 'all' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
            >
                All Orders
            </button>
        </div>

        <!-- Table Filter Dropdown -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <select
                wire:model.live="tableFilter"
                class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:bg-white focus:outline-none focus:border-indigo-600"
            >
                <option value="">All Tables ({{ $tables->count() }})</option>
                @foreach($tables as $tbl)
                    <option value="{{ $tbl->table_number }}">{{ $tbl->table_number }}</option>
                @endforeach
            </select>

            <button
                type="button"
                onclick="window.location.reload()"
                class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs transition-colors cursor-pointer"
                title="Manual Refresh"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    <!-- Active Kitchen Tickets Grid -->
    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/90 shadow-sm max-w-lg mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto border border-emerald-200">
                ✓
            </div>
            <h3 class="text-lg font-black text-slate-900">All Dishes Prepared!</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">
                Kitchen queue is currently empty. As soon as a customer scans a table QR or waiter sends an order, new dishes will pop up automatically.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($orders as $order)
                @php
                    $pendingItems = $order->items->filter(fn($it) => in_array($it->kot_status, ['pending', 'printed', 'cooking']));
                    $allPrepared = $pendingItems->isEmpty();
                    $elapsedMinutes = $order->created_at ? (int) $order->created_at->diffInMinutes(now()) : 0;
                @endphp

                <div class="bg-white rounded-3xl border {{ $allPrepared ? 'border-slate-200 bg-slate-50/50' : ($elapsedMinutes > 15 ? 'border-rose-300 ring-2 ring-rose-200' : 'border-slate-200/90 shadow-md') }} flex flex-col justify-between overflow-hidden transition-all">

                    <!-- Ticket Header -->
                    <div class="p-4 sm:p-5 border-b border-slate-100 {{ $allPrepared ? 'bg-slate-100/70' : ($elapsedMinutes > 15 ? 'bg-rose-50/70' : 'bg-slate-50') }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider {{ $allPrepared ? 'bg-slate-200 text-slate-700' : 'bg-slate-900 text-amber-300' }}">
                                    {{ $order->table ? $order->table->table_number : 'Takeaway' }}
                                </span>
                                <span class="text-[11px] font-mono font-bold text-slate-400">
                                    {{ $order->order_number }}
                                </span>
                            </div>

                            <!-- Elapsed Time Badge -->
                            <div class="flex items-center gap-1 text-[11px] font-bold font-mono px-2.5 py-0.5 rounded-full {{ $elapsedMinutes > 15 ? 'bg-rose-100 text-rose-800 animate-pulse' : ($elapsedMinutes > 8 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                <span>🕒</span>
                                <span>{{ $order->created_at ? $order->created_at->diffForHumans(null, true, true) : 'just now' }}</span>
                            </div>
                        </div>

                        <!-- Special Cooking Instructions / Notes -->
                        @if(!empty($order->notes))
                            <div class="mt-2 p-2 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-900 font-semibold flex items-start gap-1.5">
                                <span class="text-amber-500 font-bold shrink-0">📝 Note:</span>
                                <span>{{ $order->notes }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Itemized Food List (What to cook) -->
                    <div class="p-4 sm:p-5 flex-1 divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            @php
                                $isVeg = $item->menuItem ? $item->menuItem->isVeg() : true;
                                $isItemPending = in_array($item->kot_status, ['pending', 'printed', 'cooking']);
                            @endphp

                            <div class="py-3 first:pt-0 last:pb-0 flex items-start justify-between gap-3 {{ !$isItemPending ? 'opacity-50' : '' }}">
                                <div class="flex items-start gap-3">
                                    <!-- Big Quantity Badge -->
                                    <div class="w-8 h-8 rounded-xl {{ $isItemPending ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-black text-sm shrink-0 font-mono-numbers shadow-2xs">
                                        {{ $item->qty }}x
                                    </div>

                                    <div>
                                        <!-- Dish Name & Pure Veg Indicator -->
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-3.5 h-3.5 border border-emerald-600 rounded-xs flex items-center justify-center p-0.5 shrink-0" title="100% Pure Veg">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            </span>
                                            <span class="font-black text-xs text-slate-900 {{ !$isItemPending ? 'line-through' : '' }}">
                                                {{ $item->item_name }}
                                            </span>
                                        </div>

                                        <!-- Portion / Variant -->
                                        @if($item->variant_name)
                                            <span class="inline-block mt-0.5 text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                                                {{ $item->variant_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cook Status Action Buttons -->
                                <div class="shrink-0 flex items-center gap-1.5">
                                    @if($item->kot_status === 'prepared')
                                        <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase">
                                            ✓ Ready
                                        </span>
                                    @elseif($item->kot_status === 'cooking')
                                        <button
                                            type="button"
                                            wire:click="markItemPrepared({{ $item->id }})"
                                            class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 text-[10px] font-black rounded-xl border border-amber-400 shadow-2xs cursor-pointer transition-all active:scale-95"
                                        >
                                            Cooking... Mark Done
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="markItemCooking({{ $item->id }})"
                                            class="px-2.5 py-1 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-900 text-[10px] font-bold rounded-xl border border-slate-200 transition-all cursor-pointer"
                                        >
                                            Start Cooking
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Ticket Footer Action -->
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">
                            {{ count($order->items) }} Total Dish(es)
                        </span>

                        @if(!$allPrepared)
                            <button
                                type="button"
                                wire:click="markOrderAllPrepared({{ $order->id }})"
                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-xs transition-all active:scale-95 flex items-center gap-1.5 cursor-pointer"
                            >
                                <span>✓ Mark Table Food Ready</span>
                            </button>
                        @else
                            <span class="text-[11px] font-extrabold text-emerald-700 flex items-center gap-1">
                                <span>✓ All Food Dispatched</span>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
