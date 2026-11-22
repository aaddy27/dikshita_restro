<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable Table QR Codes - Dikshita Restro</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 min-h-screen">

    <!-- Screen Toolbar -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-base font-extrabold text-slate-900">Printable Table Standees & QR Cards</h1>
            <p class="text-xs text-slate-500">Print on A4 or card stock paper. Place on each dining table for contactless ordering.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Standee Cards
            </button>
            <a href="{{ route('admin.tables') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs">
                Back to Admin
            </a>
        </div>
    </div>

    <!-- Cards Grid (3 Columns) -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tables as $table)
            <div class="bg-white rounded-3xl p-6 shadow-sm border-2 border-slate-300 text-center flex flex-col items-center justify-between min-h-[380px]">
                <div>
                    <div class="flex items-center justify-center gap-2 mb-1.5">
                        <img src="{{ asset('images/logo-icon.jpg') }}" alt="Logo" class="w-7 h-7 rounded-lg object-cover border border-amber-400/40">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-700">Deekshita's Restaurant</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 mt-1">TABLE {{ $table->table_number }}</h2>
                    <p class="text-[11px] text-slate-500 font-medium">Capacity: {{ $table->capacity }} Guests</p>
                </div>

                <!-- QR Code Box -->
                <div class="p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl my-4">
                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($table->qr_url) !!}
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-800">Scan QR to View Menu & Order</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">No app download required &bull; Live Bill Status</p>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
