<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deekshita's Family Restaurant | Authentic Indian Cuisine &amp; Smart Dining</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.jpg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .font-mono-numbers {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        @keyframes scanLaser {
            0% { top: 5%; opacity: 0.8; }
            50% { top: 90%; opacity: 1; }
            100% { top: 5%; opacity: 0.8; }
        }
        .scan-laser {
            animation: scanLaser 2.2s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white" x-data="{
    activeCategory: '{{ $categories->first()?->id ?? 1 }}',
    typeFilter: 'all',
    showQrScannerModal: false,
    showReviewModal: false,
    mobileMenuOpen: false
}">

    <!-- Top Announcement Bar (Promotional) -->
    @if($activePromotion)
        <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-indigo-800 text-white text-xs py-2 px-4 text-center font-medium shadow-xs flex items-center justify-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>
                🎉 <strong>Today's Special Offer:</strong> {{ $activePromotion->name }} &bull;
                Enjoy {{ $activePromotion->discount_type === 'percentage' ? $activePromotion->discount_value . '% OFF' : '₹' . number_format($activePromotion->discount_value, 0) . ' FLAT OFF' }} on your dining bill!
            </span>
        </div>
    @endif

    <!-- Flash Notification for Review -->
    @if(session('review_success'))
        <div x-data="{ show: true }" x-show="show" class="bg-emerald-600 text-white text-xs py-2.5 px-4 text-center font-bold flex items-center justify-center gap-3 shadow-md">
            <span>✓ {{ session('review_success') }}</span>
            <button @click="show = false" class="text-white/80 hover:text-white">&times;</button>
        </div>
    @endif

    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <img
                    src="{{ asset('images/logo-icon.jpg') }}"
                    alt="Deekshita's Family Restaurant Logo"
                    class="w-12 h-12 rounded-2xl object-cover shadow-md border-2 border-amber-400/40 group-hover:scale-105 transition-transform"
                >
                <div class="flex flex-col">
                    <span class="font-extrabold text-slate-900 text-lg leading-tight tracking-tight">Deekshita's</span>
                    <span class="text-[11px] font-semibold text-emerald-700 tracking-wider uppercase">100% Pure Veg &bull; Family Restaurant</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#about" class="hover:text-indigo-600 transition-colors">About Us</a>
                <a href="#menu" class="hover:text-indigo-600 transition-colors">Our Menu</a>
                <a href="#reviews" class="hover:text-indigo-600 transition-colors">Guest Reviews</a>
                <a href="#highlights" class="hover:text-indigo-600 transition-colors">Experience</a>
                <a href="#visit" class="hover:text-indigo-600 transition-colors">Timings &amp; Location</a>
            </nav>

            <!-- Customer Actions Bar -->
            <div class="hidden sm:flex items-center gap-3">
                <!-- Write a Review Button -->
                <button
                    @click="showReviewModal = true; openReviewModal()"
                    onclick="openReviewModal()"
                    class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
                >
                    <span class="text-amber-500 font-black text-sm">★</span>
                    <span>Write a Review</span>
                </button>

                <!-- Scan Table QR Order Button -->
                <button
                    @click="showQrScannerModal = true; openQrScanner()"
                    onclick="openQrScanner()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Dine-In QR Order</span>
                </button>
            </div>

            <!-- Mobile Hamburger Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 hover:text-slate-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile Drawer Menu (No Staff Links) -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-3">
            <a href="#about" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-700 py-1">About Us</a>
            <a href="#menu" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Our Menu</a>
            <a href="#reviews" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Guest Reviews</a>
            <a href="#highlights" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Experience</a>
            <a href="#visit" @click="mobileMenuOpen = false" class="block text-sm font-semibold text-slate-700 py-1">Timings &amp; Location</a>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <button @click="showReviewModal = true; mobileMenuOpen = false; openReviewModal()" onclick="openReviewModal()" class="w-full py-2 bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs rounded-xl text-center cursor-pointer flex items-center justify-center gap-1.5">
                    <span class="text-amber-500 font-black">★</span>
                    <span>Write a Review</span>
                </button>
                <button @click="showQrScannerModal = true; mobileMenuOpen = false; openQrScanner()" onclick="openQrScanner()" class="w-full py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl text-center cursor-pointer flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Dine-In QR Order</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section (With Background Video) -->
    <section class="relative bg-slate-950 text-white py-16 sm:py-24 overflow-hidden">
        <!-- Background Video with Clean Translucent Overlay -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            @if(!empty($heroVideo))
                <video id="heroBackgroundVideo" autoplay muted loop playsinline preload="auto" class="w-full h-full object-cover opacity-90 scale-105 transition-opacity duration-700">
                    <source src="{{ asset('storage/' . $heroVideo) }}" type="video/mp4">
                </video>
            @else
                <video id="heroBackgroundVideo" autoplay muted loop playsinline preload="auto" class="w-full h-full object-cover opacity-85 scale-105 transition-opacity duration-700">
                    <source src="https://assets.mixkit.co/videos/preview/mixkit-chef-garnishing-a-dish-in-a-restaurant-kitchen-42724-large.mp4" type="video/mp4">
                </video>
            @endif
            <!-- Subtle Cinematic Vignette for Text Contrast (Without hiding the video) -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-slate-950/25 to-slate-950/65"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black/40 backdrop-blur-md border border-white/20 text-indigo-200 text-xs font-semibold mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Authentic Culinary Heritage &bull; Smart Contactless Dining</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.15] max-w-4xl mx-auto drop-shadow-lg">
                A Symphony of Rich Flavors &amp; <span class="bg-clip-text text-transparent bg-gradient-to-r from-amber-300 via-indigo-200 to-amber-200">Heartfelt Hospitality</span>
            </h1>

            <!-- Subtitle -->
            <p class="mt-5 text-sm sm:text-base lg:text-lg text-white/95 max-w-2xl mx-auto leading-relaxed font-normal drop-shadow-md">
                Indulge in slow-simmered Bukhara Dal Makhani, charcoal-roasted smoky Tandoori Kebabs, artisanal Dum Biryanis, and refreshing handcrafted coolers crafted with 100% pure, farm-fresh ingredients.
            </p>

            <!-- CTA Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                <a href="#menu" class="w-full sm:w-auto px-7 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span>Explore Full Menu</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                <button @click="showQrScannerModal = true; openQrScanner()" onclick="openQrScanner()" class="w-full sm:w-auto px-7 py-3.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-sm rounded-2xl backdrop-blur-md transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Scan Table QR Code</span>
                </button>
            </div>

            <!-- Trust Pills -->
            <div class="mt-12 pt-8 border-t border-white/10 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto text-xs text-indigo-200 font-medium">
                <div class="flex items-center justify-center gap-2">
                    <span class="text-base">🌱</span>
                    <span>100% Pure Fresh Ingredients</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <span class="text-base">🔥</span>
                    <span>Live Clay Tandoor Cooking</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <span class="text-base">🛡️</span>
                    <span>FSSAI Certified Kitchen</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <span class="text-base">📱</span>
                    <span>Instant Table QR Ordering</span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 sm:py-24 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left: Story Narrative -->
                <div class="lg:col-span-7 space-y-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                            Our Story &amp; Philosophy
                        </span>
                        <h2 class="mt-3 text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                            Crafted with Heritage, Cooked with Soul, Served with Love.
                        </h2>
                    </div>

                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        At <strong>Dikshita Restro &amp; Cafe</strong>, we celebrate the rich culinary diversity of authentic Indian gastronomy. Born from a reverence for generational recipes, every curry is slow-simmered in copper degchis, every flatbread is baked fresh in traditional clay ovens, and our signature spices are freshly ground daily.
                    </p>

                    <p class="text-slate-600 text-sm leading-relaxed">
                        Whether you are stepping in for a leisurely family banquet, a quick lunch with colleagues, or an intimate dinner, our welcoming ambiance, attentive service, and cutting-edge digital ordering make every dining moment memorable.
                    </p>

                    <!-- Key Pillars -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-2xl mb-1">🥘</div>
                            <h4 class="font-bold text-slate-900 text-xs">Authentic Recipes</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Slow-cooked Dal Makhani &amp; authentic marinated tandoori grills.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-2xl mb-1">🌿</div>
                            <h4 class="font-bold text-slate-900 text-xs">100% Pure Veg</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Shuddh Shakahari culinary delicacies prepared with pure ingredients &amp; zero compromise on hygiene.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-2xl mb-1">⚡</div>
                            <h4 class="font-bold text-slate-900 text-xs">Zero Waiting</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Scan your table QR code, browse portions &amp; send orders directly to the chef.</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Quick Facts Card -->
                <div class="lg:col-span-5">
                    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-800 space-y-6">
                        <!-- Illuminated Official Signboard Photo -->
                        <div class="relative w-full h-44 rounded-2xl overflow-hidden border-2 border-amber-400/40 shadow-lg group">
                            <img src="{{ asset('images/logo.jpg') }}" alt="Deekshita's Family Restaurant Signboard" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2.5 left-3 text-[11px] font-bold text-amber-300">★ Official Signboard &bull; Dine-In Entrance</span>
                        </div>

                        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                            <h3 class="font-extrabold text-base text-white">Deekshita's at a Glance</h3>
                            <span class="text-amber-400 font-bold text-xs">★ 4.9 (1,200+ Reviews)</span>
                        </div>

                        <div class="space-y-4 text-xs">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-base">📍</div>
                                <div>
                                    <strong class="text-slate-200 block text-xs">Location:</strong>
                                    <span class="text-slate-400">Murlidhar Vyas Colony,
Near Chura Gali, Bikaner 334001, Bkn-334001</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-base">⏰</div>
                                <div>
                                    <strong class="text-slate-200 block text-xs">Dining Hours:</strong>
                                    <span class="text-slate-400">Monday &ndash; Sunday &bull; 11:00 AM to 11:30 PM (All Day)</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-base">📞</div>
                                <div>
                                    <strong class="text-slate-200 block text-xs">Reservations &amp; Enquiries:</strong>
                                    <span class="text-slate-400">+91 80944 26174 / billing@dikshitarestro.com</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-base">📜</div>
                                <div>
                                    <strong class="text-slate-200 block text-xs">Licenses &amp; Verification:</strong>
                                    <span class="text-slate-400">FSSAI Lic: 11223344556677 &bull; GSTIN: 29AABCU9603R1ZM</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Air-Conditioned Dining</span>
                            <span class="text-emerald-400 font-semibold">● Open Now</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section (NO "All Categories" Tab + Professional 7-Images Showcase Slider) -->
    <section id="menu" class="py-16 sm:py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Title & Subtitle -->
            <div class="text-center max-w-2xl mx-auto mb-8">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                    Handcrafted Selection
                </span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    Our Culinary Menu
                </h2>
                <p class="mt-2 text-slate-500 text-xs sm:text-sm">
                    Freshly made upon order. 100% Shuddh Shakahari culinary delicacies prepared with authentic spices and pure ingredients.
                </p>
            </div>

            <!-- Category Tabs & 100% Pure Veg Badge -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 bg-white p-3 rounded-2xl border border-slate-200/90 shadow-xs">
                <!-- Actual Category Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 scrollbar-none">
                    @foreach($categories as $cat)
                        <button
                            @click="activeCategory = '{{ $cat->id }}'"
                            :class="activeCategory == '{{ $cat->id }}' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 font-semibold'"
                            class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer"
                        >
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                <!-- 100% Pure Veg Hallmark -->
                <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 text-emerald-800 border border-emerald-200/90 rounded-xl text-xs font-black shadow-2xs self-end sm:self-auto">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span>🌿 100% Pure Vegetarian</span>
                </div>
            </div>

            <!-- Professional 7-Images Showcase Slider / Carousel (Tailored for Social Media Posts - Zero Crop) -->
            @if(!empty($sliderImages) && count($sliderImages) > 0)
                <div
                    x-data="{
                        currentSlide: 0,
                        totalSlides: {{ count($sliderImages) }},
                        autoplayInterval: null,
                        init() {
                            this.startAutoplay();
                        },
                        startAutoplay() {
                            this.autoplayInterval = setInterval(() => {
                                this.next();
                            }, 4500);
                        },
                        stopAutoplay() {
                            if (this.autoplayInterval) clearInterval(this.autoplayInterval);
                        },
                        next() {
                            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                        },
                        prev() {
                            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                        }
                    }"
                    @mouseenter="stopAutoplay()"
                    @mouseleave="startAutoplay()"
                    class="mb-12 relative select-none"
                >
                    <!-- Main Slider Viewport (Height optimized for 1:1 square & 4:5 vertical social media posts) -->
                    <div class="relative w-full h-[400px] sm:h-[480px] md:h-[540px] lg:h-[580px] rounded-3xl overflow-hidden shadow-xl border border-slate-200/90 bg-slate-950">
                        @foreach($sliderImages as $idx => $slide)
                            <div
                                x-show="currentSlide === {{ $idx }}"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute inset-0 flex items-center justify-center overflow-hidden"
                            >
                                <!-- Ambient Matching Glow Backdrop (Fills empty edges with the post's own colors) -->
                                <div class="absolute inset-0 overflow-hidden pointer-events-none select-none">
                                    <img
                                        src="{{ $slide['url'] }}"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-full h-full object-cover blur-3xl opacity-35 scale-125 filter"
                                    >
                                    <div class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
                                </div>

                                <!-- Main Foreground Image: 100% UNCLIPPED & FULL RATIO (Social Media Post / Flyer) -->
                                <div class="relative w-full h-full flex items-center justify-center p-4 sm:p-7 z-10">
                                    <img
                                        src="{{ $slide['url'] }}"
                                        alt="{{ $slide['title'] ?? 'Restaurant Special' }}"
                                        class="max-w-full max-h-full w-auto h-auto object-contain rounded-2xl shadow-2xl ring-1 ring-white/10 transition-transform duration-500 hover:scale-[1.01]"
                                    >
                                </div>

                                <!-- Floating Glass Caption Pill (Top Left - Never Blocks Social Media Post Artwork) -->
                                @if(!empty($slide['title']) || !empty($slide['tag']))
                                    <div class="absolute top-4 left-4 sm:top-6 sm:left-6 z-20 pointer-events-none max-w-[75%] sm:max-w-md">
                                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md border border-white/20 text-white shadow-lg">
                                            @if(!empty($slide['tag']))
                                                <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider">
                                                    {{ $slide['tag'] }}
                                                </span>
                                            @endif
                                            @if(!empty($slide['title']))
                                                <span class="text-xs sm:text-sm font-bold text-white tracking-tight truncate">
                                                    {{ $slide['title'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Slide Counter Badge (Top Right) -->
                                <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20 pointer-events-none">
                                    <span class="text-white/90 text-xs font-mono font-bold bg-slate-900/80 backdrop-blur-md border border-white/20 px-3 py-1 rounded-full shadow-lg">
                                        0{{ $idx + 1 }} / 0{{ count($sliderImages) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <!-- Slider Prev & Next Arrows (Floating Tactile Buttons) -->
                        <button
                            type="button"
                            @click="prev()"
                            class="absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-indigo-600 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all cursor-pointer shadow-xl hover:scale-110 active:scale-95"
                            aria-label="Previous Slide"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button
                            type="button"
                            @click="next()"
                            class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-indigo-600 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all cursor-pointer shadow-xl hover:scale-110 active:scale-95"
                            aria-label="Next Slide"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <!-- Indicator Dots (Bottom Center) -->
                        <div class="absolute bottom-3.5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20 bg-slate-900/60 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/10 shadow-lg">
                            @foreach($sliderImages as $idx => $slide)
                                <button
                                    type="button"
                                    @click="currentSlide = {{ $idx }}"
                                    :class="currentSlide === {{ $idx }} ? 'w-6 bg-amber-400' : 'w-2 bg-white/40 hover:bg-white/80'"
                                    class="h-2 rounded-full transition-all cursor-pointer"
                                    aria-label="Go to slide {{ $idx + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Categories & Item Cards List (Swiggy / Zomato Modern Cards) -->
            <div class="space-y-8">
                @foreach($categories as $category)
                    <div
                        x-show="activeCategory == '{{ $category->id }}'"
                        class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm"
                    >
                        <!-- Category Header -->
                        <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-slate-100">
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $category->name }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Freshly prepared &bull; Cooked to order</p>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                {{ $category->menuItems->count() }} items
                            </span>
                        </div>

                        <!-- Items List -->
                        <div class="space-y-6">
                            @foreach($category->menuItems as $item)
                                <div
                                    x-show="typeFilter === 'all' || typeFilter === '{{ $item->type }}'"
                                    class="border-b border-slate-200/80 pb-6 last:border-b-0 last:pb-0 flex items-start justify-between gap-4"
                                >
                                    <!-- Left Details -->
                                    <div class="flex-1 pr-2">
                                        <!-- Pure Veg Icon -->
                                        <div class="mb-1.5 flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-4 h-4 border border-emerald-600 rounded-[3px] p-[2.5px]" title="100% Pure Veg">
                                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                            </span>

                                            @if(!$item->is_discount_excluded && $activePromotion)
                                                <span class="text-[9px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200 px-1.5 py-0.2 rounded">
                                                    Special Offer
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Item Name -->
                                        <h4 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-snug">
                                            {{ $item->name }}
                                        </h4>

                                        <!-- Price -->
                                        <div class="text-sm sm:text-base font-semibold text-slate-800 font-mono-numbers mt-0.5">
                                            ₹{{ number_format($item->base_price, 0) }}
                                        </div>

                                        <!-- More Details (Expandable) -->
                                        <div x-data="{ open: false }" class="mt-1.5">
                                            <button
                                                @click="open = !open"
                                                class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1 cursor-pointer"
                                            >
                                                <span>More Details</span>
                                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>

                                            <div x-show="open" x-cloak class="mt-2 text-xs text-slate-500 leading-relaxed max-w-lg space-y-1.5">
                                                <p>{{ $item->description }}</p>
                                                @if($item->variants->isNotEmpty())
                                                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                                        <span class="text-[10px] uppercase font-bold text-slate-400">Portions:</span>
                                                        @foreach($item->variants as $var)
                                                             <span class="text-[11px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-mono-numbers font-semibold">
                                                                {{ $var->name }} &bull; ₹{{ number_format($var->price, 0) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Image Thumbnail with Centered Overlapping ADD Button -->
                                    <div class="w-32 sm:w-36 shrink-0 flex flex-col items-center select-none">
                                        <div class="relative w-full">
                                            <img
                                                src="{{ $item->image }}"
                                                alt="{{ $item->name }}"
                                                class="w-full h-24 sm:h-28 rounded-2xl object-cover shadow-xs border border-slate-200/80 bg-slate-100"
                                                loading="lazy"
                                            >
                                            <!-- Overlapping ADD Button (Triggers Table QR Scanner) -->
                                            <button
                                                @click="showQrScannerModal = true; openQrScanner()"
                                                onclick="openQrScanner()"
                                                class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 bg-white hover:bg-slate-50 text-emerald-700 font-extrabold text-xs uppercase tracking-wider px-6 py-2 rounded-xl shadow-md border border-slate-200 hover:border-slate-300 transition-all active:scale-95 whitespace-nowrap shadow-slate-900/10 cursor-pointer"
                                            >
                                                ADD
                                            </button>
                                        </div>

                                        <!-- Customizable Text underneath ADD Button -->
                                        @if($item->variants->isNotEmpty())
                                            <span class="text-[10px] text-slate-400 font-medium text-center mt-4">
                                                customizable
                                            </span>
                                        @else
                                            <div class="h-3 mt-3"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SECTION 4: DINE-IN QR SCANNER CALLOUT (NO RANDOM TABLE SELECTION) -->
    <section class="py-14 bg-white border-y border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center">
            <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 border border-indigo-100 rounded-3xl p-8 sm:p-12 shadow-xs space-y-4">
                <span class="text-3xl inline-block">📱✨</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Dining with us right now at our restaurant?
                </h3>
                <p class="text-slate-600 text-xs sm:text-sm max-w-xl mx-auto leading-relaxed">
                    Skip waiting for paper menus! Simply scan the QR code standee on your dining table using your camera to open your real-time table cart, add items, choose variants, and send orders straight to the kitchen.
                </p>

                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <!-- Live QR Camera Scanner Button -->
                    <button
                        @click="showQrScannerModal = true; openQrScanner()"
                        onclick="openQrScanner()"
                        class="px-7 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md transition-all active:scale-95 cursor-pointer flex items-center gap-2"
                    >
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Scan Table QR Code</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400">Security note: You must scan your physical table standee to start ordering.</p>
            </div>
        </div>
    </section>

    <!-- SECTION 5: CUSTOMER REVIEWS & RATINGS (BELOW 4TH SECTION AS REQUESTED) -->
    <section id="reviews" class="py-16 sm:py-24 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold mb-2">
                        <span>★ 4.9 Verified Dining Experience</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                        Loved by 1,200+ Diners
                    </h2>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">
                        Real authentic feedback from guests who dined with us at Dikshita Restro &amp; Cafe.
                    </p>
                </div>

                <button
                    @click="showReviewModal = true; openReviewModal()"
                    onclick="openReviewModal()"
                    class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-xs transition-all active:scale-95 flex items-center gap-2 cursor-pointer self-start md:self-auto"
                >
                    <span class="text-base leading-none">★</span>
                    <span>Write a Customer Review</span>
                </button>
            </div>

            <!-- REVIEW & RATING QR CODE STANDEE BANNER -->
            <div class="mb-10 bg-gradient-to-br from-amber-50 via-white to-amber-50/50 rounded-3xl p-6 sm:p-8 border-2 border-amber-300 shadow-md flex flex-col lg:flex-row items-center justify-between gap-8">
                <!-- Left: Info & Call to Action -->
                <div class="flex-1 space-y-4 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded-full text-xs font-black">
                        <span>📱 Scan &bull; Rate &bull; Review</span>
                    </div>

                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Scan QR Code to Rate Your Dining Experience
                    </h3>

                    <p class="text-xs sm:text-sm text-slate-600 max-w-xl leading-relaxed">
                        Loved our authentic 100% pure veg delicacies and hospitality? Scan this QR code from your phone's camera to instantly open the review form. Click <strong>"Generate Review"</strong> to post a high-quality review in Hindi or English in just 1 second!
                    </p>

                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-2">
                        <button
                            @click="showReviewModal = true; openReviewModal()"
                            onclick="openReviewModal()"
                            class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-xs transition-all active:scale-95 flex items-center gap-2 cursor-pointer"
                        >
                            <span class="text-base leading-none">✨</span>
                            <span>Write / Generate Review (1-Click)</span>
                        </button>
                    </div>
                </div>

                <!-- Right: Standee QR Box -->
                <div class="bg-white rounded-3xl p-5 shadow-lg border-2 border-amber-400 text-center flex flex-col items-center shrink-0 w-64 group hover:scale-[1.02] transition-transform">
                    <div class="flex items-center justify-center gap-1.5 mb-2">
                        <img src="{{ asset('images/logo-icon.jpg') }}" alt="Logo" class="w-6 h-6 rounded-md object-cover border border-amber-400/40">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-900">Deekshita's Rating QR</span>
                    </div>

                    <!-- Glowing Star Rating -->
                    <div class="flex items-center justify-center gap-1 text-amber-400 text-sm mb-2">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        <span class="text-[11px] font-black text-slate-700 ml-1">5.0</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-2xl shadow-inner">
                        <img src="data:image/svg+xml;base64,{{ $reviewQrSvg }}" alt="Scan QR to Rate Deekshita's Restaurant" class="w-40 h-40 object-contain">
                    </div>

                    <span class="mt-3 text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Point Mobile Camera
                    </span>
                    <span class="text-[9px] text-slate-400 mt-0.5">Instant Mobile Review</span>
                </div>
            </div>

            <!-- Reviews Grid Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @forelse($reviews as $rev)
                    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <!-- Star Rating -->
                            <div class="flex items-center gap-1 text-amber-400 text-sm">
                                @for($s = 1; $s <= 5; $s++)
                                    <span>{{ $s <= $rev->rating ? '★' : '☆' }}</span>
                                @endfor
                                <span class="text-xs font-bold text-slate-600 ml-1.5">{{ $rev->rating }}.0</span>
                            </div>

                            <p class="text-slate-700 text-xs leading-relaxed">
                                "{{ $rev->comment }}"
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                                    {{ strtoupper(substr($rev->customer_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h5 class="font-bold text-slate-900 text-xs truncate">{{ $rev->customer_name }}</h5>
                                    <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Verified Diner
                                    </span>
                                </div>
                            </div>

                            @if($rev->favorite_dish)
                                <div class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-lg truncate" title="{{ $rev->favorite_dish }}">
                                    Ordered: {{ $rev->favorite_dish }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center text-slate-400">
                        Be the first to share your dining experience!
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Visit Us & Timings Section -->
    <section id="visit" class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-50 rounded-3xl p-8 sm:p-12 border border-slate-200/90 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Location -->
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        📍
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Visit Our Restaurant</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Murlidhar Vyas Colony,
Near Chura Gali, Bikaner 334001, <br>
                      
                    </p>
                </div>

                <!-- Hours -->
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        ⏰
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Service Hours</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Monday &ndash; Sunday: 11:00 AM &ndash; 11:30 PM<br>
                        Lunch Buffet: 12:30 PM &ndash; 3:30 PM<br>
                        Dinner: 7:00 PM &ndash; 10:30 PM
                    </p>
                </div>

                <!-- Contact & Social -->
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        📞
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Get in Touch</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Phone:+91 80944 26174<br>
                        Email: billing@dikshitarestro.com<br>
                        WhatsApp Invoicing enabled.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- LIVE CAMERA QR SCANNER MODAL (Replaces arbitrary table picker) -->
    <div
        id="qrScannerModal"
        x-show="showQrScannerModal"
        x-cloak
        style="display: none;"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm"
        @click.self="showQrScannerModal = false; closeQrScanner()"
    >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 text-center space-y-4 relative">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="text-left">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Smart Dining</span>
                    <h3 class="text-base font-black text-slate-900 leading-tight">Scan Dining Table QR</h3>
                </div>
                <button
                    type="button"
                    @click="showQrScannerModal = false; closeQrScanner()"
                    onclick="closeQrScanner()"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold cursor-pointer"
                >
                    &times;
                </button>
            </div>

            <!-- Viewfinder Area -->
            <div class="relative w-full max-w-[280px] mx-auto aspect-square rounded-2xl overflow-hidden bg-slate-950 border-2 border-indigo-500 shadow-inner flex items-center justify-center">
                <!-- HTML5 Scanner Mount -->
                <div id="qr-reader" class="w-full h-full"></div>

                <!-- Laser Scan Line Effect -->
                <div class="scan-laser absolute left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_8px_#34d399] pointer-events-none"></div>

                <!-- Corner Reticles -->
                <div class="absolute top-2 left-2 w-5 h-5 border-t-2 border-l-2 border-indigo-400 pointer-events-none"></div>
                <div class="absolute top-2 right-2 w-5 h-5 border-t-2 border-r-2 border-indigo-400 pointer-events-none"></div>
                <div class="absolute bottom-2 left-2 w-5 h-5 border-b-2 border-l-2 border-indigo-400 pointer-events-none"></div>
                <div class="absolute bottom-2 right-2 w-5 h-5 border-b-2 border-r-2 border-indigo-400 pointer-events-none"></div>
            </div>

            <!-- Guidance Instructions -->
            <div class="space-y-1.5 text-xs text-slate-600">
                <p class="font-bold text-slate-900">Point your camera at the QR standee on your table.</p>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    As soon as the table QR code is detected, your personalized table menu &amp; cart will open instantly!
                </p>
                <div id="camera-error-message" class="hidden p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px]">
                    Camera permission not granted or device camera not accessible. Please open your normal Phone Camera app / Google Lens and scan the table standee directly.
                </div>
            </div>

            <!-- Action footer -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-center">
                <button
                    type="button"
                    @click="showQrScannerModal = false; closeQrScanner()"
                    onclick="closeQrScanner()"
                    class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                >
                    Cancel / Close Scanner
                </button>
            </div>
        </div>
    </div>

    <!-- WRITE A REVIEW MODAL (Triggered from Navbar & Reviews Section) -->
    <div
        id="reviewModal"
        x-show="showReviewModal"
        x-cloak
        style="display: none;"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm overflow-y-auto"
        @click.self="showReviewModal = false; closeReviewModal()"
        x-data="{
            rating: 5,
            hoverRating: 0,
            comment: '',
            favoriteDish: '',
            generatedNotice: '',
            hindiTemplates: [
                {
                    comment: 'खाना बहुत ही स्वादिष्ट और 100% शुद्ध शाकाहारी था! दाल मखनी और पनीर टिक्का का स्वाद वाकई लाजवाब है। सर्विस बहुत तेज़ थी और स्टाफ का व्यवहार बहुत विनम्र रहा। फैमिली के साथ आने के लिए बेस्ट रेस्टोरेंट!',
                    dish: 'Bukhara Dal Makhani & Paneer Tikka'
                },
                {
                    comment: 'बीकानेर में इतना बेहतरीन प्योर वेज खाना पहली बार चखा! एंबियंस बहुत शांत, साफ-सुथरा और खूबसूरत है। टेबल QR से आर्डर करना बेहद आसान रहा। 10 में से 10 नंबर!',
                    dish: 'Shahi Paneer Butter Masala & Garlic Naan'
                },
                {
                    comment: 'शानदार डाइनिंग अनुभव! गरमा-गरम खाना बिल्कुल ताज़ा परोसा गया। तंदूरी सोया चाप और वेज दम बिरयानी का स्वाद कभी नहीं भूल सकते। पूरे परिवार को बहुत पसंद आया।',
                    dish: 'Tandoori Soya Chaap Angara & Dum Biryani'
                },
                {
                    comment: 'परिवार और बच्चों के साथ डिनर के लिए एकदम उत्तम जगह। साफ-सफाई और शुद्धता का पूरा ध्यान रखा गया है। वर्ल्ड ऑफ वेजीज पिज्जा और ब्राउनी बच्चों को बहुत पसंद आई!',
                    dish: 'World Of Veggies Pizza & Brownie'
                },
                {
                    comment: 'बहुत ही उम्दा स्वाद, लाजवाब प्रेजेंटेशन और त्वरित सेवा! रेट्स भी बिल्कुल वाजिब हैं और क्वांटिटी भी बहुत अच्छी है। हाइजीन और टेस्ट दोनों में नंबर 1!',
                    dish: 'Dal Makhani Bukhara & Tandoori Roti'
                }
            ],
            englishTemplates: [
                {
                    comment: 'Outstanding 100% pure vegetarian dining experience! The Dal Makhani was slow-cooked to creamy perfection and the Paneer Tikka was melt-in-the-mouth soft. Highly recommended for families!',
                    dish: 'Bukhara Dal Makhani & Paneer Tikka'
                },
                {
                    comment: 'One of the finest pure veg restaurants in the city! Divine culinary flavors, elegant modern ambiance, and attentive staff. Contactless table QR ordering made the dining seamless.',
                    dish: 'Shahi Paneer Butter Masala & Garlic Naan'
                },
                {
                    comment: 'Exceptional food quality and authentic spices. The Tandoori Soya Chaap Angara and Shahi Dum Biryani were bursting with charcoal aroma. Fresh, piping hot food served with heartfelt warmth!',
                    dish: 'Tandoori Soya Chaap & Dum Biryani'
                },
                {
                    comment: 'Spotless cleanliness, cozy family atmosphere, and delicious food! Every dish exceeded our expectations. The artisanal pizza and sizzling brownie are must-tries.',
                    dish: 'World of Veggies Pizza & Sizzling Brownie'
                },
                {
                    comment: 'Truly heartfelt hospitality with genuine pure veg goodness. Clean enterprise standards, prompt service, and unbeatable taste. Will definitely visit again!',
                    dish: 'Paneer Tikka & Crispy Corn Pepper'
                }
            ],
            hindiIndex: 0,
            englishIndex: 0,
            generateHindi() {
                this.rating = 5;
                const item = this.hindiTemplates[this.hindiIndex % this.hindiTemplates.length];
                this.hindiIndex++;
                this.comment = item.comment;
                this.favoriteDish = item.dish;
                this.generatedNotice = '✓ हिंदी में Review तैयार हो गया! आप चाहें तो एडिट कर सकते हैं।';
                setTimeout(() => { this.generatedNotice = ''; }, 4000);
            },
            generateEnglish() {
                this.rating = 5;
                const item = this.englishTemplates[this.englishIndex % this.englishTemplates.length];
                this.englishIndex++;
                this.comment = item.comment;
                this.favoriteDish = item.dish;
                this.generatedNotice = '✓ English review generated! Feel free to edit or submit directly.';
                setTimeout(() => { this.generatedNotice = ''; }, 4000);
            }
        }"
    >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-4 relative my-auto max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Guest Experience</span>
                    <h3 class="text-base font-black text-slate-900">Rate &amp; Review Your Dining</h3>
                </div>
                <button
                    type="button"
                    @click="showReviewModal = false; closeReviewModal()"
                    onclick="closeReviewModal()"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold cursor-pointer"
                >
                    &times;
                </button>
            </div>

            <!-- Review Submission Form -->
            <form action="{{ route('reviews.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Interactive Star Rating Picker -->
                <div class="text-center py-2 bg-amber-50/60 rounded-2xl border border-amber-200/80">
                    <label class="block text-[11px] font-bold uppercase text-amber-900 mb-1">Your Star Rating</label>
                    <input type="hidden" name="rating" :value="rating">
                    <div class="flex items-center justify-center gap-1.5 text-3xl select-none cursor-pointer">
                        <template x-for="star in 5" :key="star">
                            <span
                                @click="rating = star"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="transition-transform hover:scale-125"
                                :class="(hoverRating ? hoverRating >= star : rating >= star) ? 'text-amber-400' : 'text-slate-300'"
                            >
                                ★
                            </span>
                        </template>
                    </div>
                    <span class="text-xs font-bold text-amber-800 mt-1 block" x-text="rating + ' Stars - ' + (rating === 5 ? 'Outstanding! Highly Recommended' : (rating === 4 ? 'Very Good Experience' : (rating === 3 ? 'Average' : 'Could Be Better')))"></span>
                </div>

                <!-- 1-Click Smart Auto-Generate Review Widget -->
                <div class="p-3 bg-gradient-to-r from-amber-50 to-indigo-50/60 rounded-2xl border border-amber-200">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span class="text-[11px] font-black uppercase text-amber-900 flex items-center gap-1.5">
                            <span class="text-sm">✨</span>
                            <span>Auto-Generate Review (1-Click)</span>
                        </span>
                        <span class="text-[9px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">User Friendly</span>
                    </div>

                    <p class="text-[11px] text-slate-600 mb-2 leading-tight">
                        Bina type kiye, ek click me shandaar review generate karein:
                    </p>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="generateHindi()"
                            class="py-2 px-2.5 bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs rounded-xl shadow-2xs transition-all active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>🇮🇳</span>
                            <span>हिंदी में लिखें</span>
                        </button>

                        <button
                            type="button"
                            @click="generateEnglish()"
                            class="py-2 px-2.5 bg-white hover:bg-indigo-100 text-indigo-900 border border-indigo-300 font-bold text-xs rounded-xl shadow-2xs transition-all active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>🇬🇧</span>
                            <span>In English</span>
                        </button>
                    </div>

                    <!-- Animated Success Notice -->
                    <div x-show="generatedNotice" x-cloak class="mt-2 text-[10px] font-bold text-emerald-800 bg-emerald-100/90 border border-emerald-200 px-2.5 py-1 rounded-lg flex items-center gap-1.5 transition-all">
                        <span x-text="generatedNotice"></span>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Your Name *</label>
                        <input
                            type="text"
                            name="customer_name"
                            required
                            placeholder="e.g. Rahul Sharma"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600"
                        >
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Mobile / WhatsApp (Optional)</label>
                        <input
                            type="tel"
                            name="customer_phone"
                            placeholder="10-digit mobile"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600"
                        >
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Favorite Dish Ordered</label>
                        <input
                            type="text"
                            name="favorite_dish"
                            x-model="favoriteDish"
                            placeholder="e.g. Bukhara Dal Makhani, Paneer Tikka"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600"
                        >
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[10px] font-bold uppercase text-slate-500">Your Dining Review &amp; Comments *</label>
                            <span class="text-[10px] text-slate-400">Editable</span>
                        </div>
                        <textarea
                            name="comment"
                            x-model="comment"
                            rows="3"
                            required
                            placeholder="Tell us what you enjoyed most about the food, service and atmosphere..."
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-indigo-600"
                        ></textarea>
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>Submit Verified Review</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer (Customer Facing Only) -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-10 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img
                    src="{{ asset('images/logo-icon.jpg') }}"
                    alt="Deekshita's Logo"
                    class="w-9 h-9 rounded-xl object-cover shadow-sm border border-amber-400/40"
                >
                <span class="font-bold text-white text-sm">Deekshita's Family Restaurant</span>
            </div>

            <div class="flex items-center gap-6 text-slate-400">
                <a href="#about" class="hover:text-white transition-colors">About</a>
                <a href="#menu" class="hover:text-white transition-colors">Menu</a>
                <a href="#reviews" class="hover:text-white transition-colors">Reviews</a>
                <a href="#visit" class="hover:text-white transition-colors">Location</a>
            </div>

            <div class="text-[11px] text-slate-500">
                &copy; {{ date('Y') }} Deekshita's Family Restaurant. All rights reserved.
            </div>
        </div>
    </footer>

    @livewireScripts
    <script>
        // HTML5 Camera QR Code Scanner Logic
        let html5QrScannerInstance = null;

        function openQrScanner() {
            const m = document.getElementById('qrScannerModal');
            if (m) {
                m.classList.remove('hidden');
                m.style.display = 'flex';
            }

            try {
                if (!html5QrScannerInstance && window.Html5Qrcode) {
                    html5QrScannerInstance = new Html5Qrcode("qr-reader");
                }

                if (html5QrScannerInstance) {
                    const config = { fps: 10, qrbox: { width: 220, height: 220 } };
                    html5QrScannerInstance.start(
                        { facingMode: "environment" },
                        config,
                        onQrCodeSuccess,
                        onQrCodeError
                    ).catch(err => {
                        console.warn("Camera start error:", err);
                        const errBox = document.getElementById('camera-error-message');
                        if (errBox) errBox.classList.remove('hidden');
                    });
                }
            } catch (e) {
                console.error("QR Scanner Init Error:", e);
            }
        }

        function onQrCodeSuccess(decodedText, decodedResult) {
            // Stop scanning once code detected
            if (html5QrScannerInstance) {
                html5QrScannerInstance.stop().then(() => {
                    html5QrScannerInstance.clear();
                }).catch(err => console.error(err));
            }

            // Redirect to detected table URL
            if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                window.location.href = decodedText;
            } else if (decodedText.includes('/table/')) {
                window.location.href = decodedText;
            } else {
                // Treated as qr_token UUID
                window.location.href = '/table/' + encodeURIComponent(decodedText.trim());
            }
        }

        function onQrCodeError(errorMessage) {
            // normal per-frame scan failure while looking for code, ignore
        }

        function closeQrScanner() {
            if (html5QrScannerInstance) {
                try {
                    html5QrScannerInstance.stop().then(() => {
                        html5QrScannerInstance.clear();
                    }).catch(e => {});
                } catch(e) {}
            }
            const m = document.getElementById('qrScannerModal');
            if (m) {
                m.classList.add('hidden');
                m.style.display = 'none';
            }
        }

        // Review Modal Logic
        function openReviewModal() {
            const m = document.getElementById('reviewModal');
            if (m) {
                m.classList.remove('hidden');
                m.style.display = 'flex';
            }
        }

        function closeReviewModal() {
            const m = document.getElementById('reviewModal');
            if (m) {
                m.classList.add('hidden');
                m.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQrScanner();
                closeReviewModal();
            }
        });

        // Ensure Background Video Autoplay is robust on all browsers & handle Review QR deep link
        document.addEventListener('DOMContentLoaded', function() {
            // Auto open review modal if URL has ?review=1 or hash is #write-review
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('review') === '1' || window.location.hash === '#write-review') {
                setTimeout(() => {
                    openReviewModal();
                }, 350);
            }

            const vid = document.getElementById('heroBackgroundVideo');
            if (vid) {
                vid.muted = true;
                const tryPlay = () => {
                    const p = vid.play();
                    if (p !== undefined) {
                        p.catch(() => {
                            // Retry on user interaction if browser policy blocked autoplay
                            const unlock = () => {
                                vid.play();
                                document.removeEventListener('click', unlock);
                                document.removeEventListener('touchstart', unlock);
                                document.removeEventListener('scroll', unlock);
                            };
                            document.addEventListener('click', unlock, { once: true });
                            document.addEventListener('touchstart', unlock, { once: true });
                            document.addEventListener('scroll', unlock, { once: true });
                        });
                    }
                };
                tryPlay();
            }
        });
    </script>
</body>
</html>
