<div>
    <!-- Sticky Sub-Bar with Table Info & Status -->
    <div class="bg-white rounded-2xl p-3.5 mb-4 shadow-xs border border-slate-200/80 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Table</span>
            <span class="px-2.5 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 font-extrabold rounded-lg text-sm tabular-nums">
                {{ $table_number }}
            </span>
        </div>

        <div>
            @if($order_status === 'locked')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Bill Locked
                </span>
            @elseif($order_status === 'open')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Running Order
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Ready to Order
                </span>
            @endif
        </div>
    </div>

    <!-- Bill Locked Alert Banner & Dynamic UPI Payment -->
    @if($order_status === 'locked' && $activeOrder)
        <div class="mb-4 p-4 rounded-3xl bg-linear-to-br from-rose-50 via-white to-rose-50/40 border-2 border-rose-300 text-rose-950 shadow-md space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-sm font-black text-rose-950 uppercase tracking-tight">Final Bill Locked</h4>
                        <span class="text-sm font-black text-slate-900 font-mono-numbers bg-white border border-rose-200 px-2.5 py-0.5 rounded-full shadow-2xs">
                            ₹{{ number_format($activeOrder->final_payable_amount, 2) }}
                        </span>
                    </div>
                    <p class="text-xs text-rose-800 mt-0.5 leading-relaxed">
                        Your bill is finalized by cashier. Scan the QR or tap below to pay instantly via UPI:
                    </p>
                </div>
            </div>

            <!-- Dynamic UPI QR & Pay Action -->
            <div class="p-3 bg-white rounded-2xl border border-rose-200 flex flex-col sm:flex-row items-center gap-3.5 shadow-xs">
                <div class="p-2 bg-white rounded-xl border border-slate-200 shadow-2xs shrink-0">
                    {!! $activeOrder->getUpiQrSvg(105) !!}
                </div>
                <div class="flex-1 text-center sm:text-left space-y-2 w-full">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Total Payable Amount</div>
                        <div class="text-2xl font-black text-slate-950 font-mono-numbers">₹{{ number_format($activeOrder->final_payable_amount, 2) }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">{{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ $activeOrder->getUpiPaymentUrl() }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition-all active:scale-[0.99]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Tap to Pay via UPI App</span>
                        </a>
                        <button
                            type="button"
                            wire:click="openBillModal"
                            class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition-colors"
                        >
                            View Items
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @elseif($order_status === 'locked')
        <div class="mb-4 p-4 rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-800 shadow-sm flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-rose-900">Settlement in Progress</h4>
                <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">
                    Your bill is locked for final settlement. Please contact the cashier to add more items.
                </p>
            </div>
        </div>
    @endif

    <!-- Order Placed Success Banner -->
    @if($orderPlacedSuccess)
        <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    ✓
                </div>
                <div>
                    <h5 class="text-xs font-bold text-emerald-900">Order Sent to Kitchen!</h5>
                    <p class="text-[11px] text-emerald-700">Kitchen tickets (KOT) generated. Preparing your food.</p>
                </div>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold p-1">&times;</button>
        </div>
    @endif

    <!-- Search & Type Filters -->
    <div class="space-y-3 mb-4">
        <!-- Search Input -->
        <div class="relative">
            <input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="Search food, drinks, desserts..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 shadow-xs"
            >
            <div class="absolute left-3.5 top-3 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 p-1 text-xs">
                    &times;
                </button>
            @endif
        </div>

        <!-- Pure Veg Restaurant Badge & Table Bill -->
        <div class="flex items-center justify-between gap-2">
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200/80 rounded-xl text-xs font-bold text-emerald-800 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>100% Pure Vegetarian</span>
            </div>

            @if($activeOrder && $activeOrder->items->isNotEmpty())
                <button
                    wire:click="openBillModal"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-xl flex items-center gap-1 shadow-2xs transition-all active:scale-95"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Table Bill ({{ $activeOrder->items->sum('qty') }})</span>
                </button>
            @endif
        </div>

        <!-- Category Horizontal Filter Chips -->
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
            <button
                wire:click="$set('selectedCategory', null)"
                class="px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all border {{ is_null($selectedCategory) ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400' }}"
            >
                All Menu
            </button>
            @foreach($categories as $category)
                <button
                    wire:click="$set('selectedCategory', {{ $category->id }})"
                    class="px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all border {{ $selectedCategory === $category->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400' }}"
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Menu Items List (Swiggy / Zomato Modern Card Style) -->
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/90 shadow-sm space-y-5">
        @forelse($menuItems as $item)
            <div class="border-b border-slate-200/80 pb-5 last:border-b-0 last:pb-0 flex items-start justify-between gap-3">
                <!-- Left Details -->
                <div class="flex-1 pr-1">
                    <!-- Veg Indicator -->
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center justify-center w-4 h-4 border border-emerald-600 rounded-[3px] p-[2.5px]" title="100% Pure Veg">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        </span>

                        @if($item->is_discount_excluded)
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded">
                                No Promo
                            </span>
                        @endif
                    </div>

                    <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">{{ $item->name }}</h3>

                    <!-- Price -->
                    <div class="text-sm font-semibold text-slate-800 font-mono-numbers mt-0.5">
                        ₹{{ number_format($item->base_price, 0) }}
                    </div>

                    <!-- More Details Expandable -->
                    <div x-data="{ open: false }" class="mt-1">
                        <button
                            @click="open = !open"
                            class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 cursor-pointer"
                        >
                            <span>More Details</span>
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="mt-1 text-xs text-slate-500 leading-relaxed">
                            <p>{{ $item->description }}</p>
                            @if($item->variants->isNotEmpty())
                                <div class="mt-1 flex items-center gap-1 flex-wrap">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Portions:</span>
                                    @foreach($item->variants as $var)
                                        <span class="text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-mono-numbers font-semibold">
                                            {{ $var->name }} &bull; ₹{{ number_format($var->price, 0) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Image Thumbnail & Floating Overlapping ADD Button -->
                <div class="w-28 sm:w-32 shrink-0 flex flex-col items-center select-none">
                    <div class="relative w-full">
                        <img
                            src="{{ $item->image }}"
                            alt="{{ $item->name }}"
                            class="w-full h-20 sm:h-24 rounded-2xl object-cover shadow-xs border border-slate-200/80 bg-slate-100"
                            loading="lazy"
                        >
                        <!-- Floating ADD Button -->
                        @if($order_status === 'locked')
                            <button
                                disabled
                                class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-slate-100 text-slate-400 font-bold text-xs uppercase px-4 py-1.5 rounded-xl border border-slate-300 shadow-xs cursor-not-allowed whitespace-nowrap"
                            >
                                LOCKED
                            </button>
                        @else
                            <button
                                wire:click="openItem({{ $item->id }})"
                                class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-white hover:bg-slate-50 text-emerald-700 font-black text-xs uppercase tracking-wider px-5 py-1.5 rounded-xl shadow-md border border-slate-200 hover:border-slate-300 transition-all active:scale-95 whitespace-nowrap shadow-slate-900/10 cursor-pointer"
                            >
                                ADD
                            </button>
                        @endif
                    </div>

                    <!-- Customizable notice underneath ADD button -->
                    @if($item->variants->isNotEmpty())
                        <span class="text-[10px] text-slate-400 font-medium text-center mt-3.5">
                            customizable
                        </span>
                    @else
                        <div class="h-3 mt-2.5"></div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-slate-400">
                <p class="text-sm">No items found matching your filters.</p>
                <button wire:click="$set('search', ''); $set('selectedCategory', null); $set('typeFilter', 'all');" class="mt-2 text-xs font-semibold text-indigo-600 hover:underline">
                    Reset filters
                </button>
            </div>
        @endforelse
    </div>

    <!-- Variant Bottom-Sheet Modal -->
    @if($showVariantModal && $modalItem)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity animate-fade-in">
            <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Choose Variant</span>
                        <h3 class="text-base font-extrabold text-slate-900">{{ $modalItem->name }}</h3>
                    </div>
                    <button wire:click="$set('showVariantModal', false)" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold">
                        &times;
                    </button>
                </div>

                <div class="py-4 space-y-2.5">
                    @foreach($modalItem->variants as $variant)
                        <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:border-indigo-600 bg-slate-50/50 hover:bg-indigo-50/30 transition-all">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ $variant->name }}</h4>
                                <span class="font-extrabold text-indigo-700 text-sm font-mono-numbers">₹{{ number_format($variant->price, 2) }}</span>
                            </div>
                            <button
                                wire:click="addVariantToCart({{ $variant->id }})"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95"
                            >
                                Select
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Sticky Bottom Floating Cart Bar -->
    @if($this->cart_count > 0)
        <div class="fixed bottom-4 left-4 right-4 z-40 max-w-lg mx-auto">
            <div class="bg-indigo-900 text-white rounded-2xl p-3.5 shadow-xl border border-indigo-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-sm font-mono-numbers">
                        {{ $this->cart_count }}
                    </span>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-indigo-200 tracking-wider">Cart Total</div>
                        <div class="text-base font-black font-mono-numbers">₹{{ number_format($this->cart_total, 2) }}</div>
                    </div>
                </div>

                <button
                    wire:click="$set('showCartDrawer', true)"
                    class="px-4 py-2 bg-white text-indigo-950 font-extrabold text-xs rounded-xl shadow-xs hover:bg-indigo-50 transition-all active:scale-95 flex items-center gap-1.5"
                >
                    <span>View Cart &bull; Order</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Cart Slide-Over / Bottom-Sheet Drawer -->
    @if($showCartDrawer)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 shadow-2xl border border-slate-200 max-h-[92vh] flex flex-col">
                <!-- Drawer Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                            {{ $this->cart_count }}
                        </span>
                        <h3 class="text-base font-extrabold text-slate-900">Your Table Cart</h3>
                    </div>
                    <button wire:click="$set('showCartDrawer', false)" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Items Scroll Area -->
                <div class="flex-1 overflow-y-auto py-3 space-y-3 divide-y divide-slate-100">
                    @foreach($cart as $key => $cartItem)
                        <div class="pt-2 flex items-center justify-between gap-2">
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-900 text-xs">{{ $cartItem['name'] }}</h4>
                                @if($cartItem['variant_name'])
                                    <span class="text-[10px] text-indigo-600 font-semibold">({{ $cartItem['variant_name'] }})</span>
                                @endif
                                <div class="text-xs text-slate-600 font-mono-numbers font-medium mt-0.5">
                                    ₹{{ number_format($cartItem['price'], 2) }}
                                </div>
                            </div>

                            <!-- Qty Stepper -->
                            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                                <button
                                    wire:click="updateQty('{{ $key }}', -1)"
                                    class="w-6 h-6 rounded-lg bg-white text-slate-800 font-black flex items-center justify-center text-xs shadow-xs hover:bg-rose-50 hover:text-rose-600"
                                >
                                    -
                                </button>
                                <span class="w-6 text-center text-xs font-bold font-mono-numbers">{{ $cartItem['qty'] }}</span>
                                <button
                                    wire:click="updateQty('{{ $key }}', 1)"
                                    class="w-6 h-6 rounded-lg bg-white text-slate-800 font-black flex items-center justify-center text-xs shadow-xs hover:bg-indigo-50 hover:text-indigo-600"
                                >
                                    +
                                </button>
                            </div>

                            <div class="w-16 text-right font-extrabold text-xs text-slate-900 font-mono-numbers">
                                ₹{{ number_format($cartItem['qty'] * $cartItem['price'], 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Guest Info Inputs -->
                <div class="border-t border-slate-200 pt-3 space-y-2 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Your Name (Optional)</label>
                            <input type="text" wire:model="customer_name" placeholder="Guest Name" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white focus:outline-none focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">WhatsApp / Phone</label>
                            <input type="tel" wire:model="customer_phone" placeholder="10-digit mobile" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white focus:outline-none focus:border-indigo-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Cooking Notes / Instructions</label>
                        <input type="text" wire:model="notes" placeholder="e.g. Less spicy, extra sauce" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <!-- Order Action Button -->
                <div class="border-t border-slate-200 pt-3 mt-2">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-bold text-slate-600 uppercase">Estimated Total</span>
                        <span class="text-base font-black text-slate-900 font-mono-numbers">₹{{ number_format($this->cart_total, 2) }}</span>
                    </div>

                    <button
                        wire:click="submitOrder"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm rounded-xl shadow-md transition-all active:scale-[0.99] flex items-center justify-center gap-2"
                    >
                        <span>Place Order / Send to Kitchen</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <p class="text-[10px] text-slate-400 text-center mt-1.5">Taxes & promotional discounts calculated at cashier billing.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Bill & Real-Time Dynamic UPI QR Modal -->
    @if($showBillModal && $activeOrder)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 shadow-2xl border border-slate-200 max-h-[92vh] flex flex-col animate-in fade-in">
                <!-- Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Table Bill & UPI Payment</span>
                        <h3 class="text-base font-extrabold text-slate-900 leading-tight">Table {{ $table_number }} &bull; Order #{{ $activeOrder->order_number }}</h3>
                    </div>
                    <button wire:click="closeBillModal" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Items List -->
                <div class="flex-1 overflow-y-auto py-3 space-y-2 divide-y divide-slate-100">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 pb-1">Items Ordered:</div>
                    @foreach($activeOrder->items as $item)
                        <div class="pt-2 flex items-center justify-between gap-2 text-xs">
                            <div class="flex-1">
                                <span class="font-bold text-slate-900">{{ $item->item_name }}</span>
                                @if($item->variant_name)
                                    <span class="text-[10px] text-indigo-600 font-semibold">({{ $item->variant_name }})</span>
                                @endif
                                <div class="text-[11px] text-slate-500">
                                    {{ $item->qty }} &times; ₹{{ number_format($item->unit_price, 2) }}
                                </div>
                            </div>
                            <span class="font-bold font-mono-numbers text-slate-900">
                                ₹{{ number_format($item->total_price, 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Numerical Summary -->
                <div class="border-t border-slate-200 pt-3 space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-bold font-mono-numbers text-slate-800">₹{{ number_format($activeOrder->subtotal, 2) }}</span>
                    </div>
                    @if($activeOrder->day_discount_amount > 0)
                        <div class="flex justify-between text-emerald-700">
                            <span>Promotional Discount:</span>
                            <span class="font-mono-numbers font-bold">-₹{{ number_format($activeOrder->day_discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($activeOrder->manual_discount_amount > 0)
                        <div class="flex justify-between text-emerald-700">
                            <span>Cashier Discount:</span>
                            <span class="font-mono-numbers font-bold">-₹{{ number_format($activeOrder->manual_discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-500">
                        <span>GST (5%):</span>
                        <span class="font-mono-numbers">₹{{ number_format($activeOrder->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-baseline pt-2 border-t-2 border-slate-900 text-slate-950 font-black">
                        <span class="text-xs uppercase">Total Payable:</span>
                        <span class="text-xl font-mono-numbers">₹{{ number_format($activeOrder->final_payable_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Dynamic Real-Time UPI QR Payment Section -->
                <div class="mt-3 p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200 text-center space-y-2.5">
                    <div class="flex items-center justify-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-black uppercase text-indigo-950 tracking-wider">Pay via Any UPI App</span>
                    </div>

                    <div class="p-2.5 bg-white rounded-xl border border-indigo-200 shadow-xs inline-block">
                        {!! $activeOrder->getUpiQrSvg(130) !!}
                    </div>

                    <div class="text-[11px] text-slate-600 font-mono">
                        {{ config('app.restaurant_upi_id', 'dikshitarestro@upi') }}
                    </div>

                    <a
                        href="{{ $activeOrder->getUpiPaymentUrl() }}"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition-all active:scale-[0.99] flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Open UPI App to Pay ₹{{ number_format($activeOrder->final_payable_amount, 2) }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
