<div class="space-y-8 max-w-7xl mx-auto py-4">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Admin</a>
                <span>&bull;</span>
                <span class="text-indigo-600">Website Customization</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Website Media, Slider &amp; Reviews</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage the homepage background video, 7-images showcase slider, and moderate customer reviews.</p>
        </div>

        <a href="{{ route('home') }}" target="_blank" class="self-start sm:self-auto px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 shadow-2xs flex items-center gap-1.5 transition-all">
            <span>View Live Website</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

    <!-- Session Status Alerts -->
    @if(session('status') || $statusMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ session('status') ?: $statusMessage }}</span>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 font-bold text-sm cursor-pointer">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1 shadow-xs">
            @foreach($errors->all() as $err)
                <p>&bull; {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <!-- SECTION 1: HERO BACKGROUND VIDEO (SINGLE VIDEO UPLOAD & REPLACE) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
                    Hero Section
                </span>
                <h2 class="text-lg font-black text-slate-900 tracking-tight mt-1">Homepage Hero Background Video</h2>
                <p class="text-xs text-slate-500">Ek hi video save rehti hai. Naya video upload karne par purana video automatically replace ho jata hai.</p>
            </div>

            @if($heroVideoPath)
                <form action="{{ route('admin.media.video.remove') }}" method="POST" onsubmit="return confirm('Kya aap custom video delete karke default video par reset karna chahte hain?');">
                    @csrf
                    <button
                        type="submit"
                        class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition-colors cursor-pointer"
                    >
                        Reset to Default Video
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left: Video Player Preview -->
            <div class="lg:col-span-7 space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Live Video Preview</label>
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 shadow-md">
                    @if($heroVideoPath)
                        <video controls autoplay muted loop playsinline class="w-full h-full object-cover">
                            <source src="{{ asset('storage/' . $heroVideoPath) }}" type="video/mp4">
                        </video>
                    @else
                        <video controls autoplay muted loop playsinline class="w-full h-full object-cover opacity-80">
                            <source src="https://assets.mixkit.co/videos/preview/mixkit-chef-garnishing-a-dish-in-a-restaurant-kitchen-42724-large.mp4" type="video/mp4">
                        </video>
                    @endif
                    <div class="absolute top-3 left-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                        {{ $heroVideoPath ? 'Custom Uploaded Video' : 'Default Cinematic Restaurant Loop' }}
                    </div>
                </div>
            </div>

            <!-- Right: Direct High-Performance Video Upload Form -->
            <div
                class="lg:col-span-5 space-y-4 bg-slate-50 p-5 sm:p-6 rounded-2xl border border-slate-200"
                x-data="{
                    uploading: false,
                    fileSelected: false,
                    fileName: '',
                    fileSize: '',
                    handleFileSelect(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileSelected = true;
                            this.fileName = file.name;
                            this.fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
                        }
                    }
                }"
            >
                <h4 class="font-extrabold text-sm text-slate-900">Upload / Replace Video</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    MP4, WebM ya MOV format mein video select karein (Max 50MB). Naya upload hote hi purana video automatic replace ho jayega.
                </p>

                <form
                    action="{{ route('admin.media.video.upload') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    @submit="uploading = true"
                    class="space-y-4"
                >
                    @csrf

                    <div>
                        <input
                            type="file"
                            name="video"
                            id="videoInput"
                            accept="video/mp4,video/webm,video/quicktime,video/x-matroska"
                            required
                            @change="handleFileSelect($event)"
                            onchange="var f = this.files[0]; if(f){ var el = document.getElementById('native-file-info'); if(el){ el.classList.remove('hidden'); el.innerText = '✓ Selected: ' + f.name + ' (' + (f.size / (1024*1024)).toFixed(1) + ' MB)'; } }"
                            class="block w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer bg-white p-2 rounded-xl border border-slate-300"
                        >
                    </div>

                    <!-- Native JS Fallback / File Details Badge -->
                    <div id="native-file-info" class="hidden p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-800"></div>

                    <!-- Upload Progress State -->
                    <div x-show="uploading" x-cloak class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-700 font-bold flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Uploading video to server... Kripya thoda wait karein.</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="uploading"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="uploading ? 'Uploading Video... Please Wait' : 'Upload &amp; Replace Video'">Upload &amp; Replace Video</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SECTION 2: 7 SLIDER IMAGES SHOWCASE (REPLACE ANY OF THE 7 SLIDES) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="pb-4 border-b border-slate-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
                Menu Showcase
            </span>
            <h2 class="text-lg font-black text-slate-900 tracking-tight mt-1">Homepage 7-Images Showcase Slider</h2>
            <p class="text-xs text-slate-500">Category tabs ke theek neeche aane wale slider ke 7 images yahan se update karein. Social media posts (Square 1:1, 4:5 ya landscape) bina crop hue poori display hongi.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @for($i = 0; $i < 7; $i++)
                <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 flex flex-col justify-between space-y-3">
                    <!-- Slide Header -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-indigo-600 uppercase">Slide #{{ $i + 1 }}</span>
                        <span class="text-[10px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-200">Slot {{ $i + 1 }}/7</span>
                    </div>

                    <!-- Slide Preview Image (Zero-Crop Social Media Post Preview) -->
                    <div class="relative w-full h-44 rounded-xl overflow-hidden bg-slate-950 border border-slate-300 shadow-2xs flex items-center justify-center p-1.5">
                        <img
                            src="{{ $sliderImages[$i]['url'] ?? 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800' }}"
                            alt="Slide {{ $i + 1 }}"
                            class="max-w-full max-h-full object-contain rounded-lg"
                        >
                    </div>

                    <!-- Title & Tag Inputs -->
                    <div class="space-y-2 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Title</label>
                            <input
                                type="text"
                                wire:model="slotTitles.{{ $i }}"
                                class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:border-indigo-600"
                            >
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Subtitle / Tag</label>
                            <input
                                type="text"
                                wire:model="slotTags.{{ $i }}"
                                class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:border-indigo-600"
                            >
                        </div>
                    </div>

                    <!-- Replace Image Form -->
                    <div class="space-y-2 pt-2 border-t border-slate-200">
                        <input
                            type="file"
                            wire:model="newSliderImages.{{ $i }}"
                            accept="image/*"
                            class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 cursor-pointer"
                        >

                        <div class="flex gap-2">
                            @if(isset($newSliderImages[$i]))
                                <button
                                    type="button"
                                    wire:click="uploadSliderImage({{ $i }})"
                                    class="flex-1 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] rounded-lg shadow-2xs"
                                >
                                    Save Image
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="updateSliderSlotMeta({{ $i }})"
                                    class="w-full py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-[11px] rounded-lg"
                                >
                                    Save Details
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- SECTION 3: CUSTOMER REVIEWS MODERATION -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                    Social Proof
                </span>
                <h2 class="text-lg font-black text-slate-900 tracking-tight mt-1">Customer Reviews &amp; Ratings</h2>
                <p class="text-xs text-slate-500">Website par guests ke submit kiye gaye reviews yahan monitor karein.</p>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-auto">
                <a
                    href="{{ route('print.review.qr') }}"
                    target="_blank"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Print Review QR Standee</span>
                </a>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                    Total Reviews: {{ $reviews->total() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px] bg-slate-50">
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Rating</th>
                        <th class="py-3 px-4">Review Text</th>
                        <th class="py-3 px-4">Favorite Dish</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($reviews as $rev)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <strong class="text-slate-900 block">{{ $rev->customer_name }}</strong>
                                <span class="text-[10px] text-slate-400">{{ $rev->created_at ? $rev->created_at->format('d M Y') : 'Recent' }}</span>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-amber-500 font-bold text-sm">
                                    {{ str_repeat('★', $rev->rating) }}{{ str_repeat('☆', 5 - $rev->rating) }}
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500 ml-1">({{ $rev->rating }}/5)</span>
                            </td>

                            <td class="py-3 px-4 max-w-sm">
                                <p class="line-clamp-2">{{ $rev->comment }}</p>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($rev->favorite_dish)
                                    <span class="bg-indigo-50 text-indigo-700 font-semibold px-2 py-0.5 rounded-full text-[10px]">
                                        {{ $rev->favorite_dish }}
                                    </span>
                                @else
                                    <span class="text-slate-400">&ndash;</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($rev->is_approved)
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        Approved (Visible)
                                    </span>
                                @else
                                    <span class="bg-slate-200 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        Hidden
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <button
                                    wire:click="toggleReviewApproval({{ $rev->id }})"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg {{ $rev->is_approved ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}"
                                >
                                    {{ $rev->is_approved ? 'Hide' : 'Approve' }}
                                </button>

                                <button
                                    wire:click="deleteReview({{ $rev->id }})"
                                    wire:confirm="Review delete karna chahte hain?"
                                    class="px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:bg-rose-50 rounded-lg"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                Abhi tak koi review submit nahi hua hai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3">
            {{ $reviews->links() }}
        </div>
    </div>

</div>
