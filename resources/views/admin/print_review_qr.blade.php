<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable Customer Review QR Standee - Deekshita's Family Restaurant</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .standee-card {
                box-shadow: none !important;
                border: 2px solid #0f172a !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 min-h-screen">

    <!-- Screen Toolbar (Hidden during print) -->
    <div class="no-print max-w-xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-sm font-extrabold text-slate-900">Guest Review & Rating QR Standee</h1>
            <p class="text-[11px] text-slate-500">Print on A4 or 4x6 photo card. Place in acrylic standees on dining tables.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl font-black text-xs flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Standee
            </button>
            <a href="{{ route('admin.media') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs">
                Back to Admin
            </a>
        </div>
    </div>

    <!-- Official Review Standee Card (Acrylic Stand / Table Card Size) -->
    <div class="standee-card max-w-md mx-auto bg-white rounded-3xl p-8 shadow-xl border-4 border-amber-400 text-center flex flex-col items-center justify-between space-y-6 relative overflow-hidden">
        <!-- Gold Luxury Header Accent -->
        <div class="absolute top-0 left-0 right-0 h-3 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500"></div>

        <!-- Restaurant Branding -->
        <div class="pt-2 flex flex-col items-center">
            <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-amber-400/60 shadow-md mb-2.5">
                <img src="{{ asset('images/logo.jpg') }}" alt="Deekshita's Logo" class="w-full h-full object-cover">
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">DEEKSHITA'S</h2>
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-700 mt-0.5">🌿 100% Pure Vegetarian Family Restaurant</p>
        </div>

        <!-- Rating Callout & Stars -->
        <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 w-full">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-800 block">How Was Your Experience?</span>
            <div class="flex items-center justify-center gap-1.5 text-amber-400 text-3xl my-1.5 select-none">
                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
            </div>
            <h3 class="text-base font-black text-slate-900">Rate &amp; Review Us</h3>
            <p class="text-[11px] text-slate-600 mt-0.5">Your feedback helps us serve you the best culinary experience!</p>
        </div>

        <!-- High Resolution Crisp QR Code -->
        <div class="p-4 bg-white border-2 border-slate-900 rounded-3xl shadow-sm flex flex-col items-center">
            <img src="data:image/svg+xml;base64,{{ $reviewQrSvg }}" alt="Scan QR Code to Review" class="w-56 h-56 object-contain">
            <div class="mt-2 flex items-center gap-1 text-[11px] font-bold text-slate-700">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Point Phone Camera to Scan</span>
            </div>
        </div>

        <!-- 1-Click Auto Review Info -->
        <div class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs text-slate-700">
            <div class="flex items-center justify-center gap-1.5 font-bold text-slate-900 text-xs">
                <span>✨</span>
                <span>Fast &amp; 1-Click Review Generator Available!</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-0.5">Scan karein aur "Auto-Generate Review" button par click karke 1 second me review post karein.</p>
        </div>

        <!-- Footer Coordinates -->
        <div class="pt-2 border-t border-slate-200 w-full text-[10px] text-slate-400 space-y-0.5">
            <p class="font-bold text-slate-700">Murlidhar Vyas Colony, Near Chura Gali, Bikaner</p>
            <p>11:00 AM to 11:30 PM &bull; Reservations: +91 80944 26174</p>
        </div>
    </div>
</body>
</html>
