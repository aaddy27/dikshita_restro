<div class="p-4 sm:p-6 max-w-7xl mx-auto" x-data="{}" x-on:open-kot-window.window="window.open($event.detail.url, '_blank', 'width=450,height=650')">

    <!-- Page Header & Overview KPI Chips -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Live Service</span>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Waiter Table Map & KOT</h1>
        </div>

        <!-- Semantic Table Status Chips -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Vacant: {{ $tables->where('status', 'vacant')->count() }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Occupied: {{ $tables->where('status', 'occupied')->count() }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Locked: {{ $tables->where('status', 'locked')->count() }}
            </span>
        </div>
    </div>

    <!-- Live Tables Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4">
        @foreach($tables as $table)
            @php
                $isVacant = $table->status === 'vacant';
                $isOccupied = $table->status === 'occupied';
                $isLocked = $table->status === 'locked';
                $order = $table->currentOrder;
                $pendingKot = $order ? $order->items->where('kot_status', 'pending')->count() : 0;
            @endphp

            <div
                wire:click="selectTable({{ $table->id }})"
                class="cursor-pointer rounded-2xl p-4 border transition-all duration-150 relative flex flex-col justify-between h-40 {{ $isVacant ? 'bg-white border-slate-200 hover:border-emerald-400 hover:shadow-md' : ($isOccupied ? 'bg-amber-50/40 border-amber-300 shadow-sm hover:shadow-md' : 'bg-rose-50/50 border-rose-300 shadow-sm') }}"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Table</span>
                        <h3 class="text-xl font-black text-slate-900 font-mono-numbers">{{ $table->table_number }}</h3>
                    </div>

                    <!-- Status Pill -->
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $isVacant ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($isOccupied ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-rose-100 text-rose-900 border border-rose-200') }}">
                        {{ $table->status }}
                    </span>
                </div>

                <!-- Middle Content -->
                <div class="my-auto">
                    @if($order)
                        <div class="text-xs font-semibold text-slate-700">
                            {{ $order->items->sum('qty') }} items &bull; <span class="font-mono-numbers font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($pendingKot > 0)
                            <div class="mt-1 inline-flex items-center gap-1 text-[10px] font-extrabold bg-amber-200/90 text-amber-900 px-2 py-0.5 rounded-md animate-pulse">
                                <span>⚡ {{ $pendingKot }} Pending KOT</span>
                            </div>
                        @else
                            <div class="mt-1 text-[10px] text-slate-500">All items sent to kitchen</div>
                        @endif
                    @else
                        <div class="text-xs text-slate-400">Cap: {{ $table->capacity }} Guests &bull; Clean</div>
                    @endif
                </div>

                <!-- Footer Action Hint -->
                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] font-medium text-indigo-600">
                    <span>{{ $isVacant ? '+ Start Order' : 'Manage Table' }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Waiter Side-Drawer for Table Management & Item Ordering -->
    @if($selectedTable)
        <div class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-xs">
            <div class="bg-white w-full max-w-xl h-full shadow-2xl flex flex-col border-l border-slate-200 animate-slide-left">

                <!-- Drawer Header -->
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm font-mono-numbers">
                            {{ $selectedTable->table_number }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold text-slate-900">Table {{ $selectedTable->table_number }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $selectedTable->status === 'vacant' ? 'bg-emerald-100 text-emerald-800' : ($selectedTable->status === 'occupied' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $selectedTable->status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Capacity: {{ $selectedTable->capacity }} Guests</p>
                        </div>
                    </div>

                    <button wire:click="closeTable" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-600 flex items-center justify-center text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- If Table is Locked -->
                @if($selectedTable->status === 'locked')
                    <div class="p-4 bg-rose-50 border-b border-rose-200 text-rose-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <div>
                            <h4 class="text-xs font-bold text-rose-900 uppercase">Bill Locked by Cashier</h4>
                            <p class="text-xs text-rose-700 mt-0.5">This bill is currently being settled at the billing counter. Waiters cannot add or modify items on locked bills.</p>
                        </div>
                    </div>
                @endif

                <!-- Drawer Content -->
                <div class="flex-1 overflow-y-auto p-4 space-y-6">

                    <!-- Running Order Items -->
                    @if($selectedOrder && $selectedOrder->items->isNotEmpty())
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Current Table Order (#{{ $selectedOrder->order_number }})
                                </span>
                                <span class="text-xs font-bold text-slate-700 font-mono-numbers">
                                    Subtotal: ₹{{ number_format($selectedOrder->subtotal, 2) }}
                                </span>
                            </div>

                            <div class="bg-slate-50 rounded-2xl border border-slate-200 divide-y divide-slate-200/80 overflow-hidden">
                                @foreach($selectedOrder->items as $orderItem)
                                    <div class="p-3 flex items-center justify-between gap-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <h5 class="text-xs font-bold text-slate-900">{{ $orderItem->item_name }}</h5>
                                                @if($orderItem->variant_name)
                                                    <span class="text-[10px] text-indigo-600 font-semibold bg-indigo-50 border border-indigo-100 px-1.5 py-0.2 rounded">
                                                        {{ $orderItem->variant_name }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-slate-500 font-mono-numbers">₹{{ number_format($orderItem->unit_price, 2) }}</span>
                                                @if($orderItem->kot_status === 'pending')
                                                    <span class="text-[9px] font-bold uppercase bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded">
                                                        KOT Pending
                                                    </span>
                                                @else
                                                    <span class="text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded">
                                                        KOT Printed
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Stepper Controls (disabled if locked) -->
                                        @if(!$selectedOrder->isLocked())
                                            <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-xs">
                                                <button
                                                    wire:click="updateItemQty({{ $orderItem->id }}, -1)"
                                                    class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs hover:bg-rose-50 hover:text-rose-600"
                                                >
                                                    -
                                                </button>
                                                <span class="w-6 text-center text-xs font-bold font-mono-numbers">{{ $orderItem->qty }}</span>
                                                <button
                                                    wire:click="updateItemQty({{ $orderItem->id }}, 1)"
                                                    class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs hover:bg-indigo-50 hover:text-indigo-600"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs font-bold font-mono-numbers px-2">Qty: {{ $orderItem->qty }}</span>
                                        @endif

                                        <div class="w-16 text-right font-bold text-xs text-slate-900 font-mono-numbers">
                                            ₹{{ number_format($orderItem->total_price, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($selectedTable->status === 'vacant')
                        <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100 text-center">
                            <p class="text-xs text-indigo-900 font-semibold mb-2">Table is currently vacant.</p>
                            <button
                                wire:click="startOrder({{ $selectedTable->id }})"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all"
                            >
                                Start New Order on {{ $selectedTable->table_number }}
                            </button>
                        </div>
                    @endif

                    <!-- Add Items Section (Only if not locked) -->
                    @if($selectedTable->status !== 'locked')
                        <div class="border-t border-slate-200 pt-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">
                                Add Items to Table
                            </span>

                            <!-- Search & Filter -->
                            <div class="space-y-2 mb-3">
                                <input
                                    type="text"
                                    wire:model.live.debounce.200ms="searchItem"
                                    placeholder="Search food item to add..."
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600"
                                >
                                <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                                    <button
                                        wire:click="$set('selectedCategory', null)"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold whitespace-nowrap {{ is_null($selectedCategory) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}"
                                    >
                                        All
                                    </button>
                                    @foreach($categories as $cat)
                                        <button
                                            wire:click="$set('selectedCategory', {{ $cat->id }})"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold whitespace-nowrap {{ $selectedCategory === $cat->id ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}"
                                        >
                                            {{ $cat->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Menu Items Picker -->
                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                @foreach($menuItems as $mItem)
                                    <div class="p-2.5 bg-white border border-slate-200 rounded-xl flex items-center justify-between gap-2 hover:border-indigo-300">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full {{ $mItem->isVeg() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                <h6 class="text-xs font-bold text-slate-900 leading-tight">{{ $mItem->name }}</h6>
                                            </div>
                                            <span class="text-[11px] text-slate-500 font-mono-numbers">₹{{ number_format($mItem->base_price, 2) }}</span>
                                        </div>

                                        <div>
                                            @if($mItem->variants->isNotEmpty())
                                                <div class="flex gap-1">
                                                    @foreach($mItem->variants as $var)
                                                        <button
                                                            wire:click="addItem({{ $mItem->id }}, {{ $var->id }})"
                                                            class="px-2 py-1 bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700 text-[10px] font-bold rounded-lg border border-slate-200 transition-colors"
                                                        >
                                                            {{ $var->name }} (₹{{ number_format($var->price, 0) }})
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @else
                                                <button
                                                    wire:click="addItem({{ $mItem->id }})"
                                                    class="px-3 py-1 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 transition-colors"
                                                >
                                                    + Add
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Drawer Footer Actions -->
                @if($selectedOrder)
                    @php
                        $pendingItemsCount = $selectedOrder->items->where('kot_status', 'pending')->count();
                    @endphp
                    <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase text-slate-500">Total Items</span>
                            <span class="text-sm font-black text-slate-900 font-mono-numbers">{{ $selectedOrder->items->sum('qty') }} items (₹{{ number_format($selectedOrder->subtotal, 2) }})</span>
                        </div>

                        <!-- KOT Print Button -->
                        @if($pendingItemsCount > 0)
                            <button
                                wire:click="triggerKot"
                                class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all active:scale-95 flex items-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print KOT ({{ $pendingItemsCount }} Pending)</span>
                            </button>
                        @else
                            <button
                                onclick="window.open('{{ route('order.kot', ['order' => $selectedOrder->id, 'all' => 1, 'autoprint' => 1]) }}', '_blank', 'width=450,height=650')"
                                class="px-3.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl"
                            >
                                Reprint Full KOT
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
