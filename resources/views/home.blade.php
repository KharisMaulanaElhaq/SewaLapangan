@extends('layouts.app')

@section('title', 'Cari & Booking Lapangan Olahraga Online')

@section('content')
<div class="space-y-12 pb-20">
    <!-- Hero & Search Section with Background Banner -->
    <section class="relative overflow-hidden bg-ink-950 py-16 sm:py-24 text-white">
        <!-- Background Hero Photo Banner & Gradient Overlay -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-banner.webp') }}"
                alt="SportVenue Hero Banner"
                class="h-full w-full object-cover object-right md:object-center opacity-40">
            <div class="absolute inset-0 bg-gradient-to-r from-ink-950 via-ink-950/80 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-transparent to-transparent"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Hero Copywriting & Value Propositions -->
            <div class="max-w-2xl space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/15 px-3.5 py-1.5 text-xs font-bold text-emerald-400 backdrop-blur-md shadow-soft-xs">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Booking Lapangan Real-Time
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Booking Lapangan. <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200 bg-clip-text text-transparent">Pasti Dapat Slot.</span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl font-normal">
                    Cek jadwal kosong secara live, kunci slot 15 menit tanpa bentrok, dan bayar DP instan via QRIS atau Virtual Account.
                </p>

                <!-- Trust Highlights Badges with Clean Inline SVGs -->
                <div class="flex flex-wrap items-center gap-2.5 pt-1 text-xs">
                    <div class="flex items-center gap-2 rounded-xl bg-white/10 border border-white/15 px-3.5 py-2 backdrop-blur-md">
                        <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="font-semibold text-slate-200">Kunci Slot 15 Menit</span>
                    </div>

                    <div class="flex items-center gap-2 rounded-xl bg-white/10 border border-white/15 px-3.5 py-2 backdrop-blur-md">
                        <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="font-semibold text-slate-200">DP QRIS & VA Otomatis</span>
                    </div>

                    <div class="flex items-center gap-2 rounded-xl bg-white/10 border border-white/15 px-3.5 py-2 backdrop-blur-md">
                        <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        <span class="font-semibold text-slate-200">E-Ticket QR Langsung Jadi</span>
                    </div>
                </div>
            </div>

            <!-- Search Filter Bar with Mobile-First Stack & Loading Spinner -->
            <div class="mt-10 rounded-3xl border border-white/15 bg-ink-900/80 p-4 sm:p-5 backdrop-blur-xl shadow-soft-xl"
                x-data="{ isSubmitting: false }">
                <form action="{{ route('home') }}" method="GET" @submit="isSubmitting = true"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                    
                    <!-- Search Keyword -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Cari Venue</label>
                        <div class="relative">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama venue atau alamat..."
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-emerald-400 focus:bg-ink-950 focus:outline-none transition min-h-[44px]">
                        </div>
                    </div>

                    <!-- City Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Kota</label>
                        <select name="city" class="w-full rounded-xl border border-white/15 bg-ink-950 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition min-h-[44px]">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sport Type Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Olahraga</label>
                        @php $selectedSport = request('sport_type', request('sport')); @endphp
                        <select name="sport_type" class="w-full rounded-xl border border-white/15 bg-ink-950 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition min-h-[44px]">
                            <option value="">Semua Olahraga</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport }}" {{ $selectedSport === $sport ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $sport)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Whitelist Sort Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Urutkan</label>
                        @php $selectedSort = request('sort', 'name'); @endphp
                        <select name="sort" class="w-full rounded-xl border border-white/15 bg-ink-950 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition min-h-[44px]">
                            <option value="name" {{ $selectedSort === 'name' ? 'selected' : '' }}>Nama (A - Z)</option>
                            <option value="-name" {{ $selectedSort === '-name' ? 'selected' : '' }}>Nama (Z - A)</option>
                            <option value="starting_price" {{ $selectedSort === 'starting_price' ? 'selected' : '' }}>Harga Terendah</option>
                            <option value="-starting_price" {{ $selectedSort === '-starting_price' ? 'selected' : '' }}>Harga Tertinggi</option>
                            <option value="-created_at" {{ $selectedSort === '-created_at' ? 'selected' : '' }}>Terbaru</option>
                            <option value="created_at" {{ $selectedSort === 'created_at' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    <!-- Submit Button with Loading State -->
                    <div class="flex items-end">
                        <button type="submit" :disabled="isSubmitting"
                            class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 py-2.5 px-4 font-bold text-white text-sm shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-600 active:scale-[0.98] transition flex items-center justify-center gap-2 min-h-[44px] disabled:opacity-60 disabled:cursor-wait">
                            
                            <svg x-show="!isSubmitting" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <svg x-show="isSubmitting" x-cloak class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <span x-text="isSubmitting ? 'Mencari...' : 'Cari Lapangan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Visual Sport Categories Photo Banner Bar (Inline SVG, Links to Filter) -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Futsal Banner -->
            <a href="{{ route('home', ['sport_type' => 'futsal']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/90 bg-ink-900 shadow-soft-md hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1 {{ request('sport_type') === 'futsal' ? 'ring-2 ring-emerald-500' : '' }}">
                <img src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=600&q=80"
                    alt="Futsal"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-emerald-400 uppercase">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8"/>
                        </svg>
                        Futsal
                    </span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Lapangan Futsal</h4>
                    <span class="text-[10px] text-slate-300">Vinyl & Interlock</span>
                </div>
            </a>

            <!-- Badminton Banner -->
            <a href="{{ route('home', ['sport_type' => 'badminton']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/90 bg-ink-900 shadow-soft-md hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1 {{ request('sport_type') === 'badminton' ? 'ring-2 ring-emerald-500' : '' }}">
                <img src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80"
                    alt="Badminton"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-teal-400 uppercase">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                        </svg>
                        Badminton
                    </span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Lapangan Badminton</h4>
                    <span class="text-[10px] text-slate-300">Karpet Yonex / BWF</span>
                </div>
            </a>

            <!-- Mini Soccer Banner -->
            <a href="{{ route('home', ['sport_type' => 'mini_soccer']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/90 bg-ink-900 shadow-soft-md hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1 {{ request('sport_type') === 'mini_soccer' ? 'ring-2 ring-emerald-500' : '' }}">
                <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=600&q=80"
                    alt="Mini Soccer"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-emerald-400 uppercase">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Mini Soccer
                    </span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Mini Soccer 7v7</h4>
                    <span class="text-[10px] text-slate-300">Rumput Sintetis</span>
                </div>
            </a>

            <!-- Basketball Banner -->
            <a href="{{ route('home', ['sport_type' => 'basketball']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/90 bg-ink-900 shadow-soft-md hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1 {{ request('sport_type') === 'basketball' ? 'ring-2 ring-emerald-500' : '' }}">
                <img src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80"
                    alt="Basketball"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-amber-400 uppercase">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a15 15 0 010 18M3 12h18"/>
                        </svg>
                        Basket
                    </span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Lapangan Basket</h4>
                    <span class="text-[10px] text-slate-300">Lantai Kayu Indoor</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Venues Section -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4">
        <x-ui.section-heading
            badge="VENUE PILIHAN"
            title="Pilihan Venue Olahraga"
            description="Pilih venue terdekat untuk mengecek jadwal lapangan secara real-time dan reservasi online instan.">
            <x-slot:action>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 border border-slate-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Tersedia <strong class="text-ink-900 ml-0.5">{{ $venues->count() }}</strong> venue
                </span>
            </x-slot:action>
        </x-ui.section-heading>

        @if($venues->isEmpty())
            <x-ui.empty-state
                title="Tidak ada venue ditemukan"
                description="Coba sesuaikan kata kunci pencarian, pilihan kota, atau filter cabang olahraga Anda."
                actionText="Reset Filter Pencarian"
                :actionHref="route('home')">
            </x-ui.empty-state>
        @else
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach($venues as $venue)
                    <div class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-soft-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-soft-xl hover:border-slate-300">
                        <!-- Venue Image Banner -->
                        <div class="relative h-60 sm:h-64 w-full overflow-hidden bg-slate-100">
                            <img src="{{ $venue->image_url ?: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80' }}"
                                alt="{{ $venue->name }}"
                                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=1200&q=80';"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-ink-950/90 via-ink-950/30 to-transparent"></div>

                            <div class="absolute top-3.5 left-3.5 flex gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-ink-900/85 backdrop-blur-md px-3 py-1 text-xs font-bold text-white shadow-soft-xs border border-white/10">
                                    <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $venue->city }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4 text-white">
                                <h3 class="text-xl sm:text-2xl font-black leading-tight tracking-tight drop-shadow-sm">{{ $venue->name }}</h3>
                                <p class="text-xs text-slate-200 truncate mt-1 flex items-center gap-1.5 opacity-90">
                                    <span>{{ $venue->address }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Venue Body -->
                        <div class="flex flex-1 flex-col p-6">
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $venue->description }}
                            </p>

                            <!-- Venue Policies Highlights (PRD FR-07 & FR-15) -->
                            <div class="mt-4 grid grid-cols-2 gap-2.5 text-xs">
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-3">
                                    <span class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Komitmen DP</span>
                                    <span class="text-sm font-extrabold text-emerald-950 font-mono">{{ $venue->dp_percent }}% Nilai Total</span>
                                </div>
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-3">
                                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kebijakan Batal</span>
                                    <span class="text-sm font-bold text-slate-800 font-mono">Gratis s/d H-{{ $venue->cancel_free_hours }} Jam</span>
                                </div>
                            </div>

                            <!-- Courts Available in this Venue -->
                            <div class="mt-5 border-t border-slate-100 pt-3.5">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Unit Lapangan Aktif:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($venue->courts as $court)
                                        <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50/80 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            {{ $court->name }}
                                            <span class="text-[10px] text-slate-400 font-mono font-normal">({{ strtoupper($court->sport_type) }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <span class="text-[11px] text-slate-500 block font-medium">Tarif Mulai Dari</span>
                                    <span class="text-lg font-black text-ink-900 font-mono tracking-tight">
                                        Rp {{ number_format($venue->courts->flatMap->courtPrices->min('price') ?: 80000, 0, ',', '.') }}
                                        <span class="text-xs font-normal text-slate-500">/jam</span>
                                    </span>
                                </div>

                                <x-ui.button :href="route('venues.show', $venue->id)" variant="primary" size="md">
                                    <span>Eksplorasi Jadwal & Reservasi</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Technical Guarantee Showcase (PRD §9.2 Anti Double Booking) -->
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-16">
        <div class="rounded-3xl border border-slate-800 bg-gradient-to-br from-ink-950 via-ink-900 to-emerald-950 p-8 sm:p-12 text-white shadow-soft-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-white/10 pb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/30 font-mono">
                        GARANSI SISTEM REAL-TIME
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight">Garansi 0 Double Booking (§9.2)</h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Slot yang Anda pilih otomatis terkunci. Arsitektur database kami menjamin tidak akan ada dua pemesan di jam yang sama.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">1</div>
                    <h4 class="mt-4 text-base font-bold text-white">Unique Index</h4>
                    <p class="mt-1.5 text-xs text-slate-400 leading-relaxed">
                        Database InnoDB otomatis menolak duplikasi jam di lapangan yang sama pada layer basis data.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">2</div>
                    <h4 class="mt-4 text-base font-bold text-white">Row Lock</h4>
                    <p class="mt-1.5 text-xs text-slate-400 leading-relaxed">
                        Mengunci baris jadwal di transaksi database saat alur checkout sedang berlangsung.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">3</div>
                    <h4 class="mt-4 text-base font-bold text-white">Atomic Lock</h4>
                    <p class="mt-1.5 text-xs text-slate-400 leading-relaxed">
                        Mencegah race condition meski ratusan pengguna menekan tombol pesan di milidetik yang sama.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">4</div>
                    <h4 class="mt-4 text-base font-bold text-white">Hold 15 Menit</h4>
                    <p class="mt-1.5 text-xs text-slate-400 leading-relaxed">
                        Waktu aman untuk bayar DP. Slot otomatis dirilis kembali jika pembayaran tidak diselesaikan.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
