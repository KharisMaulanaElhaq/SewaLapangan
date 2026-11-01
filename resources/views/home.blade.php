@extends('layouts.app')

@section('title', 'Cari & Booking Lapangan Olahraga Online')

@section('content')
<div class="space-y-12 pb-16">
    <!-- Hero & Search Section with Background Banner -->
    <section class="relative overflow-hidden bg-slate-950 py-16 sm:py-24 text-white">
        <!-- Background Hero Photo Banner & Gradient Overlay -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-banner.webp') }}"
                alt="SportVenue Hero Banner"
                class="h-full w-full object-cover object-right md:object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-950/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Hero Copywriting & Value Propositions -->
            <div class="max-w-2xl space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-400 backdrop-blur-md shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Booking Lapangan Real-Time
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Booking Lapangan. <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">Pasti Dapat Slot.</span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl font-normal">
                    Cek jadwal kosong, kunci slot 15 menit, dan bayar DP instan via QRIS. Bebas bentrok.
                </p>

                <!-- Trust Highlights Badges -->
                <div class="flex flex-wrap items-center gap-2.5 pt-1 text-xs">
                    <div class="flex items-center gap-2 rounded-xl bg-white/5 border border-white/10 px-3 py-1.5 backdrop-blur-md">
                        <span class="text-emerald-400">⚡</span>
                        <span class="font-medium text-slate-200">Kunci Slot 15 Menit</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl bg-white/5 border border-white/10 px-3 py-1.5 backdrop-blur-md">
                        <span class="text-emerald-400">💳</span>
                        <span class="font-medium text-slate-200">DP QRIS & VA Otomatis</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl bg-white/5 border border-white/10 px-3 py-1.5 backdrop-blur-md">
                        <span class="text-emerald-400">🎟️</span>
                        <span class="font-medium text-slate-200">E-Ticket QR Langsung Jadi</span>
                    </div>
                </div>
            </div>

            <!-- Search Filter Bar -->
            <div class="mt-10 rounded-2xl border border-white/10 bg-white/10 p-3 sm:p-4 backdrop-blur-xl shadow-2xl">
                <form action="{{ route('home') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Search Keyword -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Cari Venue</label>
                        <div class="relative">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama venue atau alamat..."
                                class="w-full rounded-xl border border-white/10 bg-white/10 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-emerald-400 focus:bg-slate-900 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- City Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Kota</label>
                        <select name="city" class="w-full rounded-xl border border-white/10 bg-slate-900 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sport Type Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Olahraga</label>
                        @php $selectedSport = request('sport_type', request('sport')); @endphp
                        <select name="sport_type" class="w-full rounded-xl border border-white/10 bg-slate-900 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition">
                            <option value="">Semua Olahraga</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport }}" {{ $selectedSport === $sport ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $sport)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Whitelist Sort Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Urutkan</label>
                        @php $selectedSort = request('sort', 'name'); @endphp
                        <select name="sort" class="w-full rounded-xl border border-white/10 bg-slate-900 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition">
                            <option value="name" {{ $selectedSort === 'name' ? 'selected' : '' }}>Nama (A - Z)</option>
                            <option value="-name" {{ $selectedSort === '-name' ? 'selected' : '' }}>Nama (Z - A)</option>
                            <option value="starting_price" {{ $selectedSort === 'starting_price' ? 'selected' : '' }}>Harga Terendah</option>
                            <option value="-starting_price" {{ $selectedSort === '-starting_price' ? 'selected' : '' }}>Harga Tertinggi</option>
                            <option value="-created_at" {{ $selectedSort === '-created_at' ? 'selected' : '' }}>Terbaru</option>
                            <option value="created_at" {{ $selectedSort === 'created_at' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 py-2.5 px-4 font-bold text-white text-sm shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-600 transition flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Cari Lapangan</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </section>

    <!-- Visual Sport Categories Photo Banner Bar -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Futsal Banner -->
            <a href="{{ route('home', ['sport_type' => 'futsal']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-900 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <img src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=600&q=80"
                    alt="Futsal"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="text-xs font-bold tracking-wider text-emerald-400 uppercase">⚽ Futsal</span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Lapangan Futsal</h4>
                    <span class="text-[10px] text-slate-300">Vinyl & Interlock</span>
                </div>
            </a>

            <!-- Badminton Banner -->
            <a href="{{ route('home', ['sport_type' => 'badminton']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-900 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <img src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80"
                    alt="Badminton"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="text-xs font-bold tracking-wider text-teal-400 uppercase">🏸 Badminton</span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Lapangan Badminton</h4>
                    <span class="text-[10px] text-slate-300">Karpet Yonex / BWF</span>
                </div>
            </a>

            <!-- Mini Soccer Banner -->
            <a href="{{ route('home', ['sport_type' => 'mini_soccer']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-900 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=600&q=80"
                    alt="Mini Soccer"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="text-xs font-bold tracking-wider text-emerald-400 uppercase">🌿 Mini Soccer</span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Mini Soccer 7v7</h4>
                    <span class="text-[10px] text-slate-300">Rumput Sintetis</span>
                </div>
            </a>

            <!-- Basketball Banner -->
            <a href="{{ route('home', ['sport_type' => 'basketball']) }}"
                class="group relative h-28 sm:h-32 overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-900 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <img src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80"
                    alt="Basketball"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <span class="text-xs font-bold tracking-wider text-amber-400 uppercase">🏀 Basket</span>
                    <h4 class="text-sm sm:text-base font-extrabold leading-tight">Lapangan Basket</h4>
                    <span class="text-[10px] text-slate-300">Lantai Kayu Indoor</span>
                </div>
            </a>

        </div>
    </div>


    <!-- Venues Section -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Pilihan Venue</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih venue untuk melihat jadwal lapangan dan booking online.</p>
            </div>
            <div class="text-xs font-medium text-slate-500 bg-slate-100 rounded-lg px-3 py-1.5 self-start sm:self-auto">
                Tersedia <strong class="text-slate-800">{{ $venues->count() }}</strong> venue
            </div>
        </div>


        @if($venues->isEmpty())
            <div class="my-12 rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Tidak ada venue ditemukan</h3>
                <p class="mt-1 text-sm text-slate-500">Coba sesuaikan kata kunci atau filter pencarian wilayah Anda.</p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" class="rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Reset Filter</a>
                </div>
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                @foreach($venues as $venue)
                    <div class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
                        <!-- Venue Image Banner -->
                        <div class="relative h-56 w-full overflow-hidden bg-slate-100">
                            <img src="{{ $venue->image_url ?: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80' }}"
                                alt="{{ $venue->name }}"
                                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=1200&q=80';"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                            <div class="absolute top-3 left-3 flex gap-2">
                                <span class="rounded-full bg-slate-900/80 backdrop-blur-md px-3 py-1 text-xs font-semibold text-white">
                                    📍 {{ $venue->city }}
                                </span>
                            </div>

                            <div class="absolute bottom-3 left-3 right-3 text-white">
                                <h3 class="text-xl font-bold leading-tight">{{ $venue->name }}</h3>
                                <p class="text-xs text-slate-200 truncate mt-0.5">{{ $venue->address }}</p>
                            </div>
                        </div>

                        <!-- Venue Body -->
                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $venue->description }}
                            </p>

                            <!-- Venue Policies Highlights (PRD FR-07 & FR-15) -->
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-2.5">
                                    <span class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Komitmen DP</span>
                                    <span class="text-sm font-bold text-emerald-950">{{ $venue->dp_percent }}% Nilai Total</span>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kebijakan Pembatalan</span>
                                    <span class="text-sm font-bold text-slate-800">Gratis s/d H-{{ $venue->cancel_free_hours }} Jam</span>
                                </div>
                            </div>

                            <!-- Courts Available in this Venue -->
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-xs font-semibold text-slate-500 mb-2">Unit Lapangan Aktif:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($venue->courts as $court)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            {{ $court->name }}
                                            <span class="text-[10px] text-slate-400 font-mono">({{ strtoupper($court->sport_type) }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] text-slate-500 block">Tarif Mulai Dari</span>
                                    <span class="text-base font-extrabold text-slate-900 font-mono">
                                        Rp {{ number_format($venue->courts->flatMap->courtPrices->min('price') ?: 80000, 0, ',', '.') }}
                                        <span class="text-xs font-normal text-slate-500">/jam</span>
                                    </span>
                                </div>

                                <a href="{{ route('venues.show', $venue->id) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/30 hover:bg-emerald-700 transition">
                                    <span>Eksplorasi Jadwal & Reservasi</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Technical Guarantee Showcase (PRD §9.2 Strategi Anti Double Booking) -->
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-12">
        <div class="rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 p-8 sm:p-10 text-white shadow-2xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-white/10 pb-6">
                <div>
                    <span class="rounded-md bg-emerald-500/20 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/30 font-mono">
                        GARANSI SISTEM
                    </span>
                    <h3 class="text-2xl font-bold mt-2">Garansi 0 Double Booking (§9.2)</h3>
                    <p class="text-sm text-slate-300 mt-1 max-w-2xl">
                        Slot yang Anda pilih otomatis terkunci. Tidak akan ada dua pemesan di jam yang sama.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">1</div>
                    <h4 class="mt-4 text-base font-bold text-white">Unique Index</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Database InnoDB otomatis menolak duplikasi jam di lapangan yang sama.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">2</div>
                    <h4 class="mt-4 text-base font-bold text-white">Row Lock</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Mengunci baris jadwal di database saat proses booking berlangsung.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">3</div>
                    <h4 class="mt-4 text-base font-bold text-white">Atomic Lock</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Mencegah bentrok meski banyak orang klik booking di detik yang sama.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">4</div>
                    <h4 class="mt-4 text-base font-bold text-white">Hold 15 Menit</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Waktu aman untuk bayar DP. Slot otomatis dilepas jika tidak dibayar.
                    </p>
                </div>
            </div>
        </div>
    </section>


</div>
@endsection
