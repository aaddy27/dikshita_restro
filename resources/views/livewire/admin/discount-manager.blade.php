<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Promotions & Loyalty</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Day-Wise Discount Rule Engine</h1>
        </div>

        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5 self-start">
            <span>+ Create Discount Rule</span>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold">
            ✓ {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Rule Name</th>
                        <th class="py-3 px-3">Condition Type</th>
                        <th class="py-3 px-3">Schedule</th>
                        <th class="py-3 px-3 text-right">Discount Benefit</th>
                        <th class="py-3 px-3 text-right">Min Order</th>
                        <th class="py-3 px-3 text-center">Status Toggle</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rules as $rule)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 text-xs">
                                {{ $rule->name }}
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $rule->rule_type === 'weekday' ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ $rule->rule_type === 'weekday' ? 'Weekly Day' : 'Festival Date' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-700">
                                @if($rule->rule_type === 'weekday')
                                    Every <span class="capitalize text-indigo-600">{{ $rule->day_of_week }}</span>
                                @else
                                    {{ $rule->specific_date ? $rule->specific_date->format('d M Y') : 'N/A' }}
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-right font-extrabold font-mono-numbers text-emerald-700 text-xs">
                                {{ $rule->discount_type === 'percentage' ? $rule->discount_value . '% OFF' : '₹' . number_format($rule->discount_value, 2) . ' FLAT' }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono-numbers text-slate-500">
                                ₹{{ number_format($rule->min_order_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <button
                                    wire:click="toggleRule({{ $rule->id }})"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase transition-all {{ $rule->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}"
                                >
                                    {{ $rule->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button wire:click="deleteRule({{ $rule->id }})" onclick="confirm('Delete rule?') || event.stopImmediatePropagation()" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Create Discount Rule</h3>
                <p class="text-xs text-slate-500 mb-4">Promotions will automatically apply at the POS for eligible items.</p>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Rule Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. Wednesday Feast 15% Off" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Schedule Type</label>
                            <select wire:model.live="rule_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                                <option value="weekday">Day of Week</option>
                                <option value="specific_date">Specific Date</option>
                            </select>
                        </div>

                        <div>
                            @if($rule_type === 'weekday')
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Day of Week</label>
                                <select wire:model="day_of_week" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl capitalize">
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                            @else
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Event Date</label>
                                <input type="date" wire:model="specific_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Discount Type</label>
                            <select wire:model="discount_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                                <option value="percentage">Percentage (% Off)</option>
                                <option value="flat">Flat Amount (₹ Off)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Discount Value</label>
                            <input type="number" step="any" wire:model="discount_value" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono-numbers">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Min. Order Amount (₹)</label>
                        <input type="number" step="any" wire:model="min_order_amount" placeholder="0 for no minimum" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono-numbers">
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="font-bold text-slate-700">Rule Enabled Immediately</span>
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600">
                        </label>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showModal', false)" class="py-2.5 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="saveRule" class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Save Rule</button>
                </div>
            </div>
        </div>
    @endif
</div>
