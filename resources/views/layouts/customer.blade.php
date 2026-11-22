<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Order at Your Table - Deekshita\'s Family Restaurant' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

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
<body class="h-full flex flex-col text-slate-800 antialiased selection:bg-indigo-500 selection:text-white pb-24">

    <!-- Mobile-First App Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <img
                src="{{ asset('images/logo-icon.jpg') }}"
                alt="Deekshita's Logo"
                class="w-10 h-10 rounded-xl object-cover shadow-xs border border-amber-400/40"
            >
            <div>
                <h1 class="text-sm font-bold text-slate-900 leading-tight">Deekshita's Restaurant</h1>
                <p class="text-[10px] text-amber-700 font-semibold">Table-Side Ordering</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @yield('header_badge')
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-lg mx-auto w-full px-4 py-4">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
