@extends('layouts.app', ['title' => 'Staff Login - Deekshita\'s Family Restaurant POS'])

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full space-y-6">
        <!-- Logo & Title -->
        <div class="text-center">
            <div class="mx-auto w-24 h-24 rounded-3xl overflow-hidden border-2 border-amber-400/50 shadow-lg bg-slate-950 p-1 flex items-center justify-center">
                <img
                    src="{{ asset('images/logo.jpg') }}"
                    alt="Deekshita's Family Restaurant Logo"
                    class="w-full h-full object-cover rounded-2xl"
                >
            </div>
            <h2 class="mt-4 text-2xl font-black text-slate-900 tracking-tight">Deekshita's POS Terminal</h2>
            <p class="mt-1 text-xs text-slate-500 font-medium">Enterprise Restaurant Billing &amp; Table Management</p>
        </div>

        <!-- Quick Demo Login Switcher -->
        <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-bold tracking-wider uppercase text-indigo-700 block mb-2 text-center">
                Instant 1-Click Role Switcher
            </span>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <a href="{{ route('quick.login', 'cashier') }}" class="flex flex-col items-center justify-center p-2.5 bg-white border border-indigo-200/80 rounded-xl hover:border-indigo-600 hover:shadow-sm transition-all active:scale-95 group text-center">
                    <span class="text-xs font-bold text-indigo-700 group-hover:text-indigo-800">Cashier</span>
                    <span class="text-[10px] text-slate-500">Master POS</span>
                    <span class="text-[9px] text-indigo-500 font-mono mt-0.5 font-bold">PIN: 1234</span>
                </a>
                <a href="{{ route('quick.login', 'waiter') }}" class="flex flex-col items-center justify-center p-2.5 bg-white border border-emerald-200/80 rounded-xl hover:border-emerald-600 hover:shadow-sm transition-all active:scale-95 group text-center">
                    <span class="text-xs font-bold text-emerald-700 group-hover:text-emerald-800">Waiter</span>
                    <span class="text-[10px] text-slate-500">Live KOT</span>
                    <span class="text-[9px] text-emerald-500 font-mono mt-0.5 font-bold">Tablet View</span>
                </a>
                <a href="{{ route('quick.login', 'cook') }}" class="flex flex-col items-center justify-center p-2.5 bg-white border border-amber-200/80 rounded-xl hover:border-amber-600 hover:shadow-sm transition-all active:scale-95 group text-center">
                    <span class="text-xs font-bold text-amber-700 group-hover:text-amber-800">Chef / Cook</span>
                    <span class="text-[10px] text-slate-500">Kitchen KDS</span>
                    <span class="text-[9px] text-amber-600 font-mono mt-0.5 font-bold">Orders Only</span>
                </a>
                <a href="{{ route('quick.login', 'admin') }}" class="flex flex-col items-center justify-center p-2.5 bg-white border border-purple-200/80 rounded-xl hover:border-purple-600 hover:shadow-sm transition-all active:scale-95 group text-center">
                    <span class="text-xs font-bold text-purple-700 group-hover:text-purple-800">Admin</span>
                    <span class="text-[10px] text-slate-500">Full Access</span>
                    <span class="text-[9px] text-purple-500 font-mono mt-0.5 font-bold">Reports & QRs</span>
                </a>
            </div>
        </div>

        <!-- Material Outlined Login Card -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80">
            <h3 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Standard Credentials Login</h3>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', 'cashier@restro.com') }}" required autocomplete="email" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition-all">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                    <input id="password" name="password" type="password" value="password" required autocomplete="current-password" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition-all">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Remember device</span>
                    </label>
                    <span class="text-slate-400">Default pwd: <code class="font-mono text-slate-600">password</code></span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-xs transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Sign In to Terminal</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-slate-400">
            Dikshita Restro &bull; Table QR Ordering & Master POS
        </div>
    </div>
</div>
@endsection
