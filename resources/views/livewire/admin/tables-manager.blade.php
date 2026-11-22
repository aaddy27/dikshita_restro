<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Floor Architecture</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tables & QR Standee Generator</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tables.qrs.print') }}" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print All QR Standees</span>
            </a>
            <button wire:click="$set('showCreateModal', true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5">
                <span>+ Add Table</span>
            </button>
        </div>
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

    <!-- Tables Grid with Live QR Preview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($tables as $table)
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dining Table</span>
                            <h3 class="text-xl font-black text-slate-900 font-mono-numbers">{{ $table->table_number }}</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $table->status === 'vacant' ? 'bg-emerald-100 text-emerald-800' : ($table->status === 'occupied' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                            {{ $table->status }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 mt-1">Seating Capacity: <strong>{{ $table->capacity }} Guests</strong></p>

                    <!-- QR Code Render -->
                    <div class="my-4 p-3 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col items-center justify-center">
                        <div class="w-32 h-32 flex items-center justify-center">
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($table->qr_url) !!}
                        </div>
                        <a href="{{ $table->qr_url }}" target="_blank" class="mt-2 text-[10px] font-bold text-indigo-600 hover:underline truncate max-w-full">
                            Open Customer URL &nearr;
                        </a>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <button
                        wire:click="regenerateQr({{ $table->id }})"
                        onclick="confirm('Regenerate QR code token for Table {{ $table->table_number }}? Previous printed codes will stop working.') || event.stopImmediatePropagation()"
                        class="text-[11px] font-semibold text-slate-500 hover:text-indigo-600"
                    >
                        🔄 Refresh QR
                    </button>

                    @if($table->status === 'vacant')
                        <button
                            wire:click="deleteTable({{ $table->id }})"
                            onclick="confirm('Delete Table {{ $table->table_number }}?') || event.stopImmediatePropagation()"
                            class="text-[11px] font-semibold text-rose-600 hover:underline"
                        >
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Table Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Add Restaurant Table</h3>
                <p class="text-xs text-slate-500 mb-4">A unique UUID token will be assigned for contactless QR ordering.</p>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Table Number / Label</label>
                        <input type="text" wire:model="table_number" placeholder="e.g. T-09, BALCONY-01" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600 uppercase font-mono">
                        @error('table_number') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Seating Capacity</label>
                        <input type="number" wire:model="capacity" min="1" max="50" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600">
                        @error('capacity') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button wire:click="$set('showCreateModal', false)" class="py-2.5 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button wire:click="createTable" class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">Save Table</button>
                </div>
            </div>
        </div>
    @endif
</div>
