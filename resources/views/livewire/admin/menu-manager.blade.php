<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Catalog & Pricing</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Menu Items & Variants Management</h1>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="$set('showCategoryModal', true)" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-200">
                + New Category
            </button>
            <button wire:click="openCreateItem" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5">
                <span>+ Add Menu Item</span>
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold">
            ✓ {{ session('message') }}
        </div>
    @endif

    <!-- Category Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button
            wire:click="$set('activeCategoryFilter', null)"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all border {{ is_null($activeCategoryFilter) ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400' }}"
        >
            All Items ({{ $items->count() }})
        </button>
        @foreach($categories as $category)
            <button
                wire:click="$set('activeCategoryFilter', {{ $category->id }})"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all border {{ $activeCategoryFilter === $category->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400' }}"
            >
                {{ $category->name }} ({{ $category->menu_items_count }})
            </button>
        @endforeach
    </div>

    <!-- Menu Items Table -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-3 text-center">Type</th>
                        <th class="py-3 px-3">Photo</th>
                        <th class="py-3 px-4">Item Name</th>
                        <th class="py-3 px-3">Category</th>
                        <th class="py-3 px-3 text-right">Base Price</th>
                        <th class="py-3 px-3">Variants</th>
                        <th class="py-3 px-3 text-center">GST %</th>
                        <th class="py-3 px-3 text-center">Promo Discount</th>
                        <th class="py-3 px-3 text-center">Availability</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-3 text-center">
                                @if($item->isVeg())
                                    <span class="inline-flex items-center justify-center w-4 h-4 border border-emerald-600 rounded-sm p-0.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-4 h-4 border border-rose-600 rounded-sm p-0.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3">
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs">
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-lg">
                                        🍲
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $item->name }}</div>
                                @if($item->description)
                                    <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $item->description }}</p>
                                @endif
                            </td>

                            <td class="py-3.5 px-3">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[11px] font-semibold">
                                    {{ $item->category->name }}
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-right font-bold font-mono-numbers text-slate-900">
                                ₹{{ number_format($item->base_price, 2) }}
                            </td>

                            <td class="py-3.5 px-3">
                                @if($item->variants->isNotEmpty())
                                    <button wire:click="manageVariants({{ $item->id }})" class="text-[11px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded hover:bg-indigo-100">
                                        {{ $item->variants->count() }} Variants &bull; Edit
                                    </button>
                                @else
                                    <button wire:click="manageVariants({{ $item->id }})" class="text-[11px] font-semibold text-slate-400 hover:text-indigo-600">
                                        + Add Variants
                                    </button>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-center font-mono-numbers text-slate-600">
                                {{ $item->gst_percentage }}%
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                @if($item->is_discount_excluded)
                                    <span class="text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded">
                                        Excluded
                                    </span>
                                @else
                                    <span class="text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded">
                                        Eligible
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <button wire:click="toggleAvailability({{ $item->id }})" class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $item->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $item->is_available ? 'In Stock' : 'Sold Out' }}
                                </button>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editItem({{ $item->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                        Edit
                                    </button>
                                    <span class="text-slate-300">&bull;</span>
                                    <button wire:click="deleteItem({{ $item->id }})" onclick="confirm('Delete {{ $item->name }}?') || event.stopImmediatePropagation()" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Item Create / Edit Modal -->
    @if($showItemModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 max-h-[92vh] overflow-y-auto">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">
                    {{ $editingItemId ? 'Edit Menu Item' : 'Create Menu Item' }}
                </h3>

                <div class="space-y-3 mt-4 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Item Name</label>
                        <input type="text" wire:model="name" placeholder="Item Name" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600 font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Category</label>
                            <select wire:model="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Dietary Type</label>
                            <select wire:model="type" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none">
                                <option value="veg">Vegetarian (Veg)</option>
                                <option value="non_veg">Non-Vegetarian</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Base Price (₹)</label>
                            <input type="number" step="any" wire:model="base_price" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600 font-mono-numbers">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">GST Tax %</label>
                            <input type="number" step="any" wire:model="gst_percentage" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600 font-mono-numbers">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Description</label>
                        <textarea wire:model="description" rows="2" placeholder="Ingredients and flavor notes..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600"></textarea>
                    </div>

                    <!-- Dish Image Upload Section -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-700">Dish Photo</label>

                        @if($image)
                            <div class="flex items-center gap-3">
                                <div class="relative w-16 h-16 rounded-xl overflow-hidden border-2 border-indigo-400 shadow-xs">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                                <div class="text-[11px] text-emerald-700 font-bold">
                                    ✓ New image selected for upload
                                </div>
                            </div>
                        @elseif($existingImagePath)
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $existingImagePath) }}" class="w-16 h-16 rounded-xl object-cover border border-slate-300 shadow-xs">
                                <div>
                                    <button type="button" wire:click="removeDishImage" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                                        Delete Current Photo
                                    </button>
                                </div>
                            </div>
                        @endif

                        <input
                            type="file"
                            wire:model="image"
                            accept="image/png,image/jpeg,image/webp"
                            class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer"
                        >
                        <div wire:loading wire:target="image" class="text-[10px] text-indigo-600 font-semibold animate-pulse">
                            Loading image preview...
                        </div>
                        <p class="text-[10px] text-slate-400">JPG, PNG, WebM format (Max 5MB). Photo website aur customer QR order card par dikhegi.</p>
                    </div>

                    <!-- Material Switches -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="font-bold text-slate-700">Exclude from Promotional Discounts</span>
                            <input type="checkbox" wire:model="is_discount_excluded" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </label>
                        <p class="text-[10px] text-slate-500 leading-normal">If checked, day-wise or festival percentage discounts will not apply to this item (e.g. MRP water, premium platters).</p>

                        <div class="pt-2 border-t border-slate-200">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="font-bold text-slate-700">Available In Stock</span>
                                <input type="checkbox" wire:model="is_available" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showItemModal', false)" class="py-2.5 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="saveItem" class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Save Item</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Manage Variants Modal -->
    @if($showVariantModal && $selectedItemForVariants)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-indigo-600">Variants Configuration</span>
                        <h3 class="text-base font-extrabold text-slate-900">{{ $selectedItemForVariants->name }}</h3>
                    </div>
                    <button wire:click="$set('showVariantModal', false)" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
                </div>

                <!-- Add Variant Inputs -->
                <div class="py-3 border-b border-slate-200 text-xs">
                    <span class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Add New Variant (e.g. Half / Full)</span>
                    <div class="flex gap-2">
                        <input type="text" wire:model="variantName" placeholder="Variant Name" class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl">
                        <input type="number" step="any" wire:model="variantPrice" placeholder="Price ₹" class="w-24 px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-mono-numbers">
                        <button wire:click="addVariant" class="px-3 py-1.5 bg-indigo-600 text-white font-bold rounded-xl text-xs">+ Add</button>
                    </div>
                </div>

                <!-- Existing Variants List -->
                <div class="py-3 space-y-2 max-h-60 overflow-y-auto">
                    @forelse($selectedItemForVariants->variants as $var)
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800">{{ $var->name }}</span>
                                <span class="text-indigo-700 font-bold font-mono-numbers ml-2">₹{{ number_format($var->price, 2) }}</span>
                            </div>
                            <button wire:click="deleteVariant({{ $var->id }})" class="text-rose-600 hover:text-rose-800 font-bold text-[11px]">Delete</button>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-2">No variants created yet. Customers will order at base price.</p>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-slate-200 text-right">
                    <button wire:click="$set('showVariantModal', false)" class="px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl">Done</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Category Modal -->
    @if($showCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-xs w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">New Category</h3>
                <input type="text" wire:model="newCategoryName" placeholder="e.g. Soups & Salads" class="w-full px-3 py-2 mt-3 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 font-bold">

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showCategoryModal', false)" class="py-2 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="saveCategory" class="py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Create</button>
                </div>
            </div>
        </div>
    @endif
</div>
