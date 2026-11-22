<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Access Control</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Staff & Cashier PIN Management</h1>
        </div>

        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5 self-start">
            <span>+ Add Staff Member</span>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold">
            ✓ {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold">
            ⚠ {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-3">Email Address</th>
                        <th class="py-3 px-3">Role</th>
                        <th class="py-3 px-3">Cashier PIN</th>
                        <th class="py-3 px-3">Phone</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 text-xs">
                                {{ $u->name }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600 font-mono">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-800' : ($u->role === 'cashier' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-700">
                                @if($u->security_pin)
                                    <span class="bg-slate-100 px-2 py-0.5 rounded tracking-widest text-[11px]">{{ $u->security_pin }}</span>
                                @else
                                    <span class="text-slate-300">--</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-slate-500 font-mono">
                                {{ $u->phone ?: 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editUser({{ $u->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                        Edit
                                    </button>
                                    @if($u->id !== auth()->id())
                                        <span class="text-slate-300">&bull;</span>
                                        <button wire:click="deleteUser({{ $u->id }})" onclick="confirm('Delete user?') || event.stopImmediatePropagation()" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">
                    {{ $editingUserId ? 'Edit Staff User' : 'New Staff Member' }}
                </h3>

                <div class="space-y-3 mt-4 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Full Name</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Role Assignment</label>
                        <select wire:model.live="role" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-semibold">
                            <option value="waiter">Waiter (Floor Orders & KOT)</option>
                            <option value="cashier">Cashier (POS Billing & Unlock)</option>
                            <option value="admin">Admin (Executive Master Access)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">
                            Cashier 4-Digit Security PIN {{ $role === 'cashier' ? '(Required)' : '(Optional)' }}
                        </label>
                        <input type="text" maxlength="6" wire:model="security_pin" placeholder="e.g. 1234" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold tracking-widest text-center">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Password {{ $editingUserId ? '(Leave blank to keep current)' : '' }}</label>
                        <input type="password" wire:model="password" placeholder="Password" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Phone</label>
                        <input type="tel" wire:model="phone" placeholder="Mobile Number" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showModal', false)" class="py-2.5 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="saveUser" class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
