<div class="p-4 sm:p-6 max-w-[1600px] mx-auto"
     x-data="{}"
     x-on:open-receipt-window.window="window.open($event.detail.url, '_blank', 'width=450,height=700')"
     x-on:open-external-window.window="window.open($event.detail.url, '_blank')">

    <!-- Top Context & Live Table Selector Rail -->
    <div class="mb-5 bg-white rounded-2xl p-3.5 border border-slate-200/90 shadow-xs flex items-center justify-between gap-4 overflow-x-auto">
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tables:</span>
            <div class="flex items-center gap-1.5">
                @foreach($tables as $tbl)
                    @php
                        $isSelected = $selectedTableId === $tbl->id;
                        $colorClass = match($tbl->status) {
                            'vacant' => $isSelected ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:border-emerald-400',
                            'occupied' => $isSelected ? 'bg-amber-500 text-slate-950 font-black border-amber-600 shadow-sm' : 'bg-amber-50 text-amber-900 border-amber-300 hover:border-amber-400',
                            'locked' => $isSelected ? 'bg-rose-600 text-white border-rose-600 shadow-sm' : 'bg-rose-50 text-rose-800 border-rose-200 hover:border-rose-400',
                        };
                    @endphp
                    <button
                        wire:click="selectTable({{ $tbl->id }})"
                        class="px-3 py-1.5 rounded-xl border text-xs font-bold font-mono-numbers transition-all active:scale-95 flex items-center gap-1 {{ $colorClass }}"
                    >
                        <span>{{ $tbl->table_number }}</span>
                        @if($tbl->status === 'occupied')
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        @elseif($tbl->status === 'locked')
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Table Operations Bar -->
        @if($selectedOrder)
            <div class="flex items-center gap-2 shrink-0">
                <button
                    wire:click="openMergeModal"
                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-colors"
                >
                    Merge Table
                </button>
                <button
                    wire:click="openShiftModal"
                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-colors"
                >
                    Shift Table
                </button>
            </div>
        @endif
    </div>

    <!-- 12-Column POS Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left 7 Columns: Order Items & Fast Item Selector -->
        <div class="lg:col-span-7 space-y-5">

            <!-- Active Table Status & Header Card -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-base font-mono-numbers shadow-xs">
                        {{ $selectedTable ? $selectedTable->table_number : '--' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-extrabold text-slate-900">
                                Table {{ $selectedTable ? $selectedTable->table_number : 'None' }}
                            </h2>
                            @if($selectedTable)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $selectedTable->status === 'vacant' ? 'bg-emerald-100 text-emerald-800' : ($selectedTable->status === 'occupied' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $selectedTable->status }}
                                </span>
                            @endif
                        </div>
                        @if($selectedOrder)
                            <p class="text-xs text-slate-500 font-mono">Bill #: {{ $selectedOrder->order_number }} &bull; Opened {{ $selectedOrder->created_at->format('h:i A') }}</p>
                        @else
                            <p class="text-xs text-slate-400">No active bill for this table</p>
                        @endif
                    </div>
                </div>

                <!-- Lock / Unlock Trigger Controls -->
                @if($selectedOrder)
                    <div class="flex items-center gap-2">
                        @if($selectedOrder->isLocked())
                            <button
                                wire:click="openUnlockModal"
                                class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 flex items-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                <span>Unlock Bill (Requires PIN)</span>
                            </button>
                        @else
                            <button
                                wire:click="lockBill"
                                class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 flex items-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Lock Bill for Settlement</span>
                            </button>
                        @endif
                    </div>
                @elseif($selectedTable && $selectedTable->status === 'vacant')
                    <button
                        wire:click="startNewOrderForTable"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95"
                    >
                        + Open Table Order
                    </button>
                @endif
            </div>

            <!-- Itemized Order Table -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                        Ordered Items ({{ $selectedOrder ? $selectedOrder->items->sum('qty') : 0 }})
                    </span>
                    @if($selectedOrder && $selectedOrder->isLocked())
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded uppercase">
                            Editing Locked
                        </span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 px-4">Item Details</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Price</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                                <th class="py-2.5 px-3 text-center">KOT</th>
                                <th class="py-2.5 px-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if($selectedOrder && $selectedOrder->items->isNotEmpty())
                                @foreach($selectedOrder->items as $item)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 text-xs">{{ $item->item_name }}</div>
                                            @if($item->variant_name)
                                                <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-1.5 py-0.2 rounded">
                                                    {{ $item->variant_name }}
                                                </span>
                                            @endif
                                            @if($item->menuItem && $item->menuItem->is_discount_excluded)
                                                <span class="text-[9px] font-semibold text-slate-500 uppercase bg-slate-100 px-1 rounded ml-1">
                                                    No Discount
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-3 px-3 text-center">
                                            @if(!$selectedOrder->isLocked())
                                                <div class="inline-flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                                                    <button wire:click="updateItemQty({{ $item->id }}, -1)" class="w-5 h-5 rounded bg-white text-slate-700 font-bold hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center text-xs shadow-2xs">-</button>
                                                    <span class="w-6 text-center font-bold font-mono-numbers">{{ $item->qty }}</span>
                                                    <button wire:click="updateItemQty({{ $item->id }}, 1)" class="w-5 h-5 rounded bg-white text-slate-700 font-bold hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-center text-xs shadow-2xs">+</button>
                                                </div>
                                            @else
                                                <span class="font-bold font-mono-numbers">{{ $item->qty }}</span>
                                            @endif
                                        </td>

                                        <td class="py-3 px-3 text-right font-mono-numbers text-slate-600">
                                            ₹{{ number_format($item->unit_price, 2) }}
                                        </td>

                                        <td class="py-3 px-3 text-right font-extrabold font-mono-numbers text-slate-900">
                                            ₹{{ number_format($item->total_price, 2) }}
                                        </td>

                                        <td class="py-3 px-3 text-center">
                                            @if($item->kot_status === 'pending')
                                                <span class="text-[9px] font-bold uppercase bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">Pending</span>
                                            @else
                                                <span class="text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">Printed</span>
                                            @endif
                                        </td>

                                        <td class="py-3 px-3 text-center">
                                            @if(!$selectedOrder->isLocked())
                                                <button
                                                    wire:click="updateItemQty({{ $item->id }}, -{{ $item->qty }})"
                                                    title="Delete Item"
                                                    class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @else
                                                <span class="text-slate-300">&bull;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                        No items on this table. Add items using the catalog below or start new order.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fast Menu Item Selector Catalog (when unlocked) -->
            @if(!$selectedOrder || !$selectedOrder->isLocked())
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Quick Add Items</span>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                wire:model.live.debounce.150ms="menuSearch"
                                placeholder="Fast search..."
                                class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white focus:outline-none focus:border-indigo-600 w-44"
                            >
                            <select wire:model.live="menuCategoryId" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white focus:outline-none">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto pr-1">
                        @foreach($menuItems as $m)
                            <div class="p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 bg-slate-50/50 hover:bg-indigo-50/20 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $m->isVeg() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <h6 class="text-xs font-bold text-slate-900 leading-tight truncate">{{ $m->name }}</h6>
                                    </div>
                                    <span class="text-[11px] text-slate-500 font-mono-numbers">₹{{ number_format($m->base_price, 2) }}</span>
                                </div>

                                <div class="mt-2">
                                    @if($m->variants->isNotEmpty())
                                        <div class="flex gap-1 flex-wrap">
                                            @foreach($m->variants as $var)
                                                <button
                                                    wire:click="addItemToOrder({{ $m->id }}, {{ $var->id }})"
                                                    class="px-1.5 py-0.5 bg-white hover:bg-indigo-600 hover:text-white border border-slate-200 text-[10px] font-bold rounded"
                                                >
                                                    {{ $var->name }} (₹{{ number_format($var->price, 0) }})
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <button
                                            wire:click="addItemToOrder({{ $m->id }})"
                                            class="w-full py-1 bg-white hover:bg-indigo-600 hover:text-white border border-slate-200 text-xs font-bold rounded-lg transition-colors"
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

        <!-- Right 5 Columns: Sticky Billing Summary Card, Discounts & Settlement -->
        <div class="lg:col-span-5 space-y-5 sticky top-20">

            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-md p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Billing Summary</span>
                    @if($breakdown['day_rule_name'] ?? false)
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full">
                            🎁 {{ $breakdown['day_rule_name'] }}
                        </span>
                    @endif
                </div>

                <!-- Customer Details Input -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Customer Name</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="customerName"
                            placeholder="Guest Name"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600"
                        >
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Phone (for WhatsApp)</label>
                        <input
                            type="tel"
                            wire:model.live.debounce.300ms="customerPhone"
                            placeholder="10-digit mobile"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600"
                        >
                    </div>
                </div>

                <!-- Discount Mode Segmented Controls -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-[10px] font-bold uppercase text-slate-500">Discount & Price Adjustment</label>
                    <div class="grid grid-cols-4 gap-1 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                        <button
                            wire:click="$set('discountMode', 'auto')"
                            class="py-1.5 rounded-lg transition-all {{ $discountMode === 'auto' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600' }}"
                        >
                            Auto
                        </button>
                        <button
                            wire:click="$set('discountMode', 'percentage')"
                            class="py-1.5 rounded-lg transition-all {{ $discountMode === 'percentage' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600' }}"
                        >
                            % Off
                        </button>
                        <button
                            wire:click="$set('discountMode', 'flat')"
                            class="py-1.5 rounded-lg transition-all {{ $discountMode === 'flat' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600' }}"
                        >
                            ₹ Flat
                        </button>
                        <button
                            wire:click="$set('discountMode', 'override')"
                            class="py-1.5 rounded-lg transition-all {{ $discountMode === 'override' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600' }}"
                        >
                            Override
                        </button>
                    </div>

                    <!-- Percentage or Flat Input -->
                    @if(in_array($discountMode, ['percentage', 'flat']))
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-600">{{ $discountMode === 'percentage' ? 'Discount %' : 'Flat Discount ₹' }}:</span>
                            <input
                                type="number"
                                step="any"
                                wire:model.live.debounce.250ms="discountValue"
                                placeholder="{{ $discountMode === 'percentage' ? 'e.g. 10' : 'e.g. 150' }}"
                                class="w-32 px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono-numbers focus:outline-none focus:border-indigo-600"
                            >
                        </div>
                    @endif

                    <!-- Manual Final Payable Override Input Field -->
                    @if($discountMode === 'override')
                        <div class="mt-3 p-3.5 rounded-2xl bg-indigo-50 border-2 border-indigo-300 space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase text-indigo-900 tracking-wider">
                                    Manual Final Payable Override
                                </label>
                                <span class="text-[10px] font-bold uppercase text-indigo-600">Reverse Calc</span>
                            </div>
                            <p class="text-[11px] text-indigo-700">Enter custom rounded final amount (e.g. ₹1,100):</p>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-2 font-bold text-slate-500 text-sm">₹</span>
                                <input
                                    type="number"
                                    step="any"
                                    wire:model.live.debounce.250ms="manualPayableOverride"
                                    placeholder="Enter target final total"
                                    class="w-full pl-7 pr-3 py-2 bg-white border-2 border-indigo-400 rounded-xl text-sm font-black font-mono-numbers text-indigo-950 focus:outline-none focus:border-indigo-600"
                                >
                            </div>
                            @if(($breakdown['manual_discount_amount'] ?? 0) > 0)
                                <div class="text-[11px] font-semibold text-emerald-800 pt-1">
                                    Derived Cashier Discount: -₹{{ number_format($breakdown['manual_discount_amount'], 2) }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Numerical Breakdown Details -->
                <div class="space-y-2 pt-2 border-t border-slate-100 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Items Subtotal:</span>
                        <span class="font-bold text-slate-800 font-mono-numbers">₹{{ number_format($breakdown['subtotal'] ?? 0, 2) }}</span>
                    </div>

                    @if(($breakdown['day_discount_amount'] ?? 0) > 0)
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Day Discount ({{ $breakdown['day_rule_name'] }}):</span>
                            <span class="font-mono-numbers">-₹{{ number_format($breakdown['day_discount_amount'], 2) }}</span>
                        </div>
                    @endif

                    @if(($breakdown['manual_discount_amount'] ?? 0) > 0)
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Cashier Discount:</span>
                            <span class="font-mono-numbers">-₹{{ number_format($breakdown['manual_discount_amount'], 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-slate-500 pt-1 border-t border-dashed border-slate-200">
                        <span>Taxable Amount:</span>
                        <span class="font-mono-numbers font-medium">₹{{ number_format($breakdown['taxable_amount'] ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500">
                        <span>CGST (2.5%):</span>
                        <span class="font-mono-numbers">₹{{ number_format($breakdown['cgst_amount'] ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500">
                        <span>SGST (2.5%):</span>
                        <span class="font-mono-numbers">₹{{ number_format($breakdown['sgst_amount'] ?? 0, 2) }}</span>
                    </div>

                    <!-- Grand Total Highlight -->
                    <div class="pt-3 pb-1 border-t-2 border-slate-900 flex justify-between items-baseline">
                        <span class="text-sm font-black text-slate-900 uppercase tracking-wide">FINAL PAYABLE</span>
                        <span class="text-2xl font-black text-slate-950 font-mono-numbers">
                            ₹{{ number_format($breakdown['final_payable_amount'] ?? 0, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Payment Mode Segmented Picker -->
                <div class="space-y-1.5 pt-2 border-t border-slate-100">
                    <label class="block text-[10px] font-bold uppercase text-slate-500">Payment Mode</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            wire:click="$set('paymentMode', 'upi')"
                            class="py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all {{ $paymentMode === 'upi' ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}"
                        >
                            <span>UPI / QR</span>
                        </button>
                        <button
                            wire:click="$set('paymentMode', 'cash')"
                            class="py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all {{ $paymentMode === 'cash' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}"
                        >
                            <span>Cash</span>
                        </button>
                        <button
                            wire:click="$set('paymentMode', 'card')"
                            class="py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all {{ $paymentMode === 'card' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}"
                        >
                            <span>Card / POS</span>
                        </button>
                    </div>
                </div>

                <!-- Dynamic Real-Time UPI QR Code for Final Settled Amount -->
                @if($selectedOrder && ($breakdown['final_payable_amount'] ?? 0) > 0)
                    <div class="p-3.5 rounded-2xl bg-gradient-to-br from-indigo-50/70 via-white to-slate-50 border-2 border-indigo-200/90 shadow-2xs space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black">₹</span>
                                <span class="text-xs font-black uppercase text-indigo-950 tracking-wide">Live UPI Payment QR</span>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 px-2 py-0.5 rounded-full">
                                Exact Final Amount
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Dynamic QR Code generated for exact final payable amount -->
                            <div class="p-2 bg-white rounded-xl border border-indigo-200 shadow-xs shrink-0 flex items-center justify-center">
                                {!! $selectedOrder->getUpiQrSvg(92, $breakdown['final_payable_amount'] ?? $selectedOrder->final_payable_amount) !!}
                            </div>

                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="text-[10px] font-bold uppercase text-slate-400">Scan to Pay Exact:</div>
                                <div class="text-xl font-black text-slate-900 font-mono-numbers leading-none">
                                    ₹{{ number_format($breakdown['final_payable_amount'] ?? $selectedOrder->final_payable_amount, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-600 truncate font-mono">
                                    {{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}
                                </div>
                                <div class="flex items-center gap-1 pt-0.5 text-[9px] font-bold text-slate-500 uppercase">
                                    <span>GPay</span>
                                    <span>&bull;</span>
                                    <span>PhonePe</span>
                                    <span>&bull;</span>
                                    <span>Paytm</span>
                                    <span>&bull;</span>
                                    <span>BHIM</span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="openUpiModal"
                            class="w-full py-2 bg-white hover:bg-indigo-50 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-300 shadow-2xs transition-all flex items-center justify-center gap-1.5 active:scale-[0.99]"
                        >
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Show Fullscreen QR to Customer</span>
                        </button>
                    </div>
                @endif

                <!-- Settlement & Quick Actions -->
                <div class="space-y-2 pt-2">
                    <button
                        wire:click="settleBill"
                        @if(!$selectedOrder) disabled @endif
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:text-slate-400 text-white font-extrabold text-sm rounded-2xl shadow-md transition-all active:scale-[0.99] flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Settle & Print 80mm Bill</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        @if($selectedOrder)
                            <button
                                onclick="window.open('{{ route('order.receipt', ['order' => $selectedOrder->id, 'autoprint' => 1]) }}', '_blank', 'width=450,height=700')"
                                class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl flex items-center justify-center gap-1 border border-slate-200"
                            >
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Thermal Print</span>
                            </button>

                            <button
                                wire:click="shareOnWhatsApp"
                                class="py-2 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl flex items-center justify-center gap-1 border border-emerald-300"
                            >
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                <span>WhatsApp PDF</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security PIN Modal for Bill Unlock -->
    @if($showUnlockModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <h3 class="text-center text-base font-extrabold text-slate-900">Security Authorization</h3>
                <p class="text-center text-xs text-slate-500 mt-1">Enter your 4-digit Cashier PIN to unlock this bill. This event is logged in Audit Logs.</p>

                @if($unlockError)
                    <div class="mt-3 p-2 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl text-center">
                        {{ $unlockError }}
                    </div>
                @endif

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">4-Digit Security PIN</label>
                        <input
                            type="password"
                            maxlength="6"
                            wire:model="unlockPin"
                            placeholder="&bull;&bull;&bull;&bull;"
                            class="w-full text-center text-2xl tracking-widest font-mono font-bold px-4 py-2.5 bg-slate-50 border-2 border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-rose-500"
                        >
                        <span class="text-[10px] text-slate-400 block text-center mt-1">Cashier Default PIN: 1234 &bull; Admin PIN: 9999</span>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Reason for Unlock (Audit Log)</label>
                        <input
                            type="text"
                            wire:model="unlockRemarks"
                            placeholder="Remarks"
                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600"
                        >
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button
                        wire:click="$set('showUnlockModal', false)"
                        class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="confirmUnlockBill"
                        class="py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs"
                    >
                        Verify & Unlock
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Merge Modal -->
    @if($showMergeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Merge Current Table</h3>
                <p class="text-xs text-slate-500 mb-4">Select the destination table to merge all ordered items into.</p>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700">Destination Table:</label>
                    <select wire:model="targetMergeTableId" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:outline-none">
                        <option value="">-- Choose Destination Table --</option>
                        @foreach($otherTables as $oTable)
                            <option value="{{ $oTable->id }}">Table {{ $oTable->table_number }} ({{ ucfirst($oTable->status) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showMergeModal', false)" class="py-2 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="confirmMerge" class="py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl">Confirm Merge</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Shift Modal -->
    @if($showShiftModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Shift Table / Move Guests</h3>
                <p class="text-xs text-slate-500 mb-4">Move current order and guests to an empty vacant table.</p>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700">Choose Vacant Table:</label>
                    <select wire:model="targetShiftTableId" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:outline-none">
                        <option value="">-- Choose Vacant Table --</option>
                        @foreach($vacantTables as $vTable)
                            <option value="{{ $vTable->id }}">Table {{ $vTable->table_number }} (Capacity: {{ $vTable->capacity }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showShiftModal', false)" class="py-2 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="confirmShift" class="py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl">Shift Order</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Fullscreen Customer-Facing Dynamic UPI QR Modal -->
    @if($showUpiModal && $selectedOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-150">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-200 text-center space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="text-left">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Scan & Pay via UPI</span>
                        <h4 class="text-base font-black text-slate-900 leading-tight">Table {{ $selectedTable ? $selectedTable->table_number : 'Takeaway' }}</h4>
                    </div>
                    <button wire:click="closeUpiModal" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold">
                        &times;
                    </button>
                </div>

                <div class="p-3 bg-indigo-50/70 rounded-2xl border border-indigo-100 inline-block mx-auto shadow-inner">
                    <div class="p-3 bg-white rounded-xl shadow-md border border-slate-200 inline-block">
                        {!! $selectedOrder->getUpiQrSvg(220, $breakdown['final_payable_amount'] ?? $selectedOrder->final_payable_amount) !!}
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="text-xs uppercase tracking-wider text-slate-500 font-bold">Exact Final Amount to Pay</div>
                    <div class="text-3xl font-black text-indigo-950 font-mono-numbers">
                        ₹{{ number_format($breakdown['final_payable_amount'] ?? $selectedOrder->final_payable_amount, 2) }}
                    </div>
                    <p class="text-[11px] text-slate-600 font-mono mt-1">UPI ID: <span class="font-bold text-slate-900">{{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}</span></p>
                    <p class="text-[10px] text-slate-400">Merchant: {{ config('app.restaurant_upi_name', 'Dikshita Restro & Cafe') }} &bull; Order #{{ $selectedOrder->order_number }}</p>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-center gap-1.5 flex-wrap">
                    <span class="text-[10px] font-bold text-slate-600 uppercase bg-slate-100 px-2 py-0.5 rounded-md">Google Pay</span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase bg-slate-100 px-2 py-0.5 rounded-md">PhonePe</span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase bg-slate-100 px-2 py-0.5 rounded-md">Paytm</span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase bg-slate-100 px-2 py-0.5 rounded-md">BHIM</span>
                </div>

                <div class="pt-1">
                    <button
                        wire:click="closeUpiModal"
                        class="w-full py-2.5 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-xs"
                    >
                        Close / Done
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
