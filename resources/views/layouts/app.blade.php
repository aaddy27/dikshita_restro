<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Deekshita\'s Family Restaurant POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Inter Font & Tabular Numeral Ready -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .font-mono-numbers {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Material Top App Bar -->
    <header class="sticky top-0 z-40 h-16 bg-white border-b border-slate-200/90 shadow-xs flex items-center justify-between px-4 sm:px-6">
        <!-- Brand & Context -->
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <img
                    src="{{ asset('images/logo-icon.jpg') }}"
                    alt="Deekshita's Logo"
                    class="w-10 h-10 rounded-xl object-cover shadow-sm border border-amber-400/40 group-hover:scale-105 transition-transform"
                >
                <div class="flex flex-col">
                    <span class="font-bold text-slate-900 leading-tight text-sm tracking-tight">Deekshita's</span>
                    <span class="text-[10px] font-semibold text-amber-700 tracking-wider uppercase">Enterprise POS</span>
                </div>
            </a>

            <!-- Navigation Links based on role -->
            @auth
                <nav class="hidden md:flex items-center gap-1 pl-4 border-l border-slate-200">
                    @if(auth()->user()->hasRole(['cashier', 'admin']))
                        <a href="{{ route('cashier.pos') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('cashier.pos') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Cashier POS
                        </a>
                    @endif

                    @if(auth()->user()->hasRole(['waiter', 'cashier', 'admin']))
                        <a href="{{ route('waiter.pos') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('waiter.pos') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Waiter Tables
                        </a>
                    @endif

                    @if(auth()->user()->hasRole(['cook', 'admin', 'cashier', 'waiter']))
                        <a href="{{ route('kitchen.display') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('kitchen.display') ? 'bg-amber-50 text-amber-800 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors flex items-center gap-1">
                            <span>🍳</span>
                            <span>Kitchen (Cook)</span>
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <div class="h-4 w-px bg-slate-200 mx-1"></div>
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Overview
                        </a>
                        <a href="{{ route('admin.tables') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.tables') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Tables & QRs
                        </a>
                        <a href="{{ route('admin.menu') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.menu') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Menu & Variants
                        </a>
                        <a href="{{ route('admin.discounts') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.discounts') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Discounts
                        </a>
                        <a href="{{ route('admin.reports') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.reports') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Sales Reports
                        </a>
                        <a href="{{ route('admin.audit_logs') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.audit_logs') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Audit Logs
                        </a>
                        <a href="{{ route('admin.staff') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.staff') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Staff
                        </a>
                        <a href="{{ route('admin.media') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.media') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Media &amp; Reviews
                        </a>
                    @endif
                </nav>
            @endauth
        </div>

        <!-- Right Side: Status Chip & User Controls -->
        <div class="flex items-center gap-3">
            <!-- Live Sync Indicator -->
            <div class="hidden sm:flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2.5 py-1 rounded-full text-[11px] font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>POS Live</span>
            </div>

            @auth
                <!-- User Profile & Role Chip -->
                <div class="flex items-center gap-2 pl-2">
                    <div class="flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
                        @if(auth()->user()->role === 'admin')
                            <span class="text-[10px] font-semibold text-purple-700 uppercase tracking-wider">Admin</span>
                        @elseif(auth()->user()->role === 'cashier')
                            <span class="text-[10px] font-semibold text-indigo-700 uppercase tracking-wider">Cashier (PIN: {{ auth()->user()->security_pin }})</span>
                        @else
                            <span class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wider">Waiter</span>
                        @endif
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Sign Out" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-xs font-semibold px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    Staff Login
                </a>
            @endauth
        </div>
    </header>

    <!-- Page Content Canvas -->
    <main class="flex-1 overflow-x-hidden">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
