@extends('layouts.app', ['title' => 'Deekshita\'s Family Restaurant - Master Portal'])

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Hero Header -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden mb-8">
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-indigo-100 text-xs font-semibold mb-3 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Enterprise POS &bull; Live Digital Dining
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Deekshita's Family Restaurant</h1>
                <p class="mt-2 text-indigo-100 text-sm leading-relaxed">
                    Complete Enterprise Restaurant POS &amp; Table-Wise QR Ordering. Live KOT generation, Bill Locking with Cashier PIN, Automatic Day-Wise &amp; Custom Overrides, 80mm Thermal Receipts, and WhatsApp Cloud Invoices.
                </p>
            </div>
            <img src="{{ asset('images/logo.jpg') }}" alt="Deekshita's Signboard" class="w-24 h-28 object-cover rounded-2xl border-2 border-amber-400/50 shadow-lg shrink-0">
        </div>
    </div>

    <!-- Main Navigation Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
        <!-- 1. Customer QR Ordering Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg mb-3 border border-purple-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Customer QR Order</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-normal">
                    Public mobile menu. Add items to table cart, choose Half/Full variants.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1">
                <span class="text-[9px] font-bold uppercase text-slate-400 block tracking-wider">Active Tables:</span>
                @foreach($tables->take(3) as $t)
                    <a href="{{ route('table.qr', $t->qr_token) }}" target="_blank" class="flex items-center justify-between px-2 py-1 rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200/70 text-[11px] font-semibold text-slate-700 hover:text-indigo-700 transition-colors">
                        <span>Table {{ $t->table_number }}</span>
                        <span class="text-[9px] px-1 py-0.5 rounded uppercase font-bold {{ $t->status === 'vacant' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $t->status }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 2. Waiter Terminal Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg mb-3 border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Waiter POS Terminal</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-normal">
                    Tablet table map. Open unlocked tables, add items, trigger KOT.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('quick.login', 'waiter') }}" class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-xs">
                    <span>Waiter View</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 3. Cook Portal (NEW Kitchen Display System) -->
        <div class="bg-white rounded-2xl border-2 border-amber-300 shadow-sm p-4 flex flex-col justify-between hover:shadow-md transition-shadow bg-amber-50/20">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center font-black text-xl mb-3 border border-amber-300">
                    🍳
                </div>
                <div class="flex items-center gap-1.5 mb-1">
                    <h3 class="font-black text-slate-900 text-sm">Cook Portal</h3>
                    <span class="text-[9px] font-black uppercase tracking-wider bg-amber-500 text-slate-950 px-1.5 py-0.2 rounded">KDS</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-normal">
                    Kitchen display for chefs. Real-time food preparation queue, live table orders, zero billing clutter.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-amber-200">
                <a href="{{ route('quick.login', 'cook') }}" class="w-full py-2 px-3 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-xs">
                    <span>Open Cook Portal</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 4. Cashier Master POS Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg mb-3 border border-indigo-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Cashier Master POS</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-normal">
                    Bill Lock/Unlock (PIN: 1234), manual discount override, thermal &amp; WhatsApp bills.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('quick.login', 'cashier') }}" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-xs">
                    <span>Cashier POS</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- 5. Admin Management Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-lg mb-3 border border-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Admin Dashboard</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-normal">
                    KPIs, QRs, Menu image uploads, Discounts, Reports &amp; Audit Logs.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('quick.login', 'admin') }}" class="w-full py-2 px-3 bg-slate-900 hover:bg-black text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-xs">
                    <span>Admin Controls</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
