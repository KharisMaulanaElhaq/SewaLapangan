@extends('layouts.app')

@section('title', 'Cari & Booking Lapangan Olahraga Online')

@section('content')
<div class="space-y-12 pb-16">
    <!-- Hero & Search Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-emerald-950 via-slate-900 to-slate-900 py-16 sm:py-24 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:24px_24px] opacity-20"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400 backdrop-blur-sm">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Sistem Booking Real-Time & Garansi 0 Double Booking
                </div>
                <h1 class="mt-4 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
                    Booking Lapangan Olahraga <span class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">Cepat & Terpercaya</span>
                </h1>
                <p class="mt-4 text-base sm:text-lg text-slate-300 leading-relaxed">
                    Kunci slot otomatis 15 menit, bayar DP mudah via Midtrans (QRIS, VA, E-Wallet), dan kelola jadwal olahraga favoritmu tanpa khawatir double booking.
                </p>
            </div>

            <!-- Search Filter Bar -->
            <div class="mt-8 rounded-2xl border border-white/10 bg-white/10 p-3 sm:p-4 backdrop-blur-xl shadow-2xl">
                <form action="{{ route('home') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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
                        <label class="block text-xs font-medium text-slate-300 mb-1">Kota / Lokasi</label>
                        <select name="city" class="w-full rounded-xl border border-white/10 bg-slate-900 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sport Type Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Cabang Olahraga</label>
                        <select name="sport" class="w-full rounded-xl border border-white/10 bg-slate-900 px-3.5 py-2.5 text-sm text-white focus:border-emerald-400 focus:outline-none transition">
                            <option value="">Semua Olahraga</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport }}" {{ request('sport') === $sport ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $sport)) }}</option>
                            @endforeach
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

    <!-- Venues Section -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Daftar Venue Olahraga Tersedia</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih venue untuk melihat ketersediaan slot jam dan memesan lapangan secara langsung.</p>
            </div>
            <div class="text-xs font-medium text-slate-500 bg-slate-100 rounded-lg px-3 py-1.5 self-start sm:self-auto">
                Menampilkan <strong class="text-slate-800">{{ $venues->count() }}</strong> venue mitra
            </div>
        </div>

        @if($venues->isEmpty())
            <div class="my-12 rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Tidak ada venue ditemukan</h3>
                <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter pencarian Anda.</p>
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
                                    <span class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider">DP Di Muka</span>
                                    <span class="text-sm font-bold text-emerald-950">{{ $venue->dp_percent }}% Nilai Total</span>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ketentuan Batal</span>
                                    <span class="text-sm font-bold text-slate-800">Gratis s/d H-{{ $venue->cancel_free_hours }} Jam</span>
                                </div>
                            </div>

                            <!-- Courts Available in this Venue -->
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-xs font-semibold text-slate-500 mb-2">Lapangan Tersedia:</p>
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
                                    <span class="text-[11px] text-slate-500 block">Mulai dari</span>
                                    <span class="text-base font-extrabold text-slate-900 font-mono">
                                        Rp {{ number_format($venue->courts->flatMap->courtPrices->min('price') ?: 80000, 0, ',', '.') }}
                                        <span class="text-xs font-normal text-slate-500">/jam</span>
                                    </span>
                                </div>

                                <a href="{{ route('venues.show', $venue->id) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/30 hover:bg-emerald-700 transition">
                                    <span>Pilih Jadwal & Booking</span>
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
                    <span class="rounded-md bg-emerald-500/20 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/30">
                        ENGINE ARSITEKTUR
                    </span>
                    <h3 class="text-2xl font-bold mt-2">Strategi Anti Double Booking Berlapis (§9.2)</h3>
                    <p class="text-sm text-slate-300 mt-1 max-w-2xl">
                        Mekanisme terpadu menjamin tepat 1 request berhasil saat ≥ 50 request paralel mencoba memesan slot yang sama.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('docs') }}" class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                        Lihat Spesifikasi OpenAPI &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">1</div>
                    <h4 class="mt-4 text-base font-bold text-white">Database Unique Index</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Constraint unik <code class="text-emerald-300 bg-slate-800 px-1 py-0.5 rounded">unique(court_id, slot_start)</code> pada tabel <code class="text-slate-300">booking_slots</code> sebagai benteng terakhir di level InnoDB.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">2</div>
                    <h4 class="mt-4 text-base font-bold text-white">DB Transaction & Row Lock</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Penggunaan <code class="text-emerald-300 bg-slate-800 px-1 py-0.5 rounded">DB::transaction</code> dengan <code class="text-emerald-300 bg-slate-800 px-1 py-0.5 rounded">lockForUpdate()</code> pada baris lapangan saat cek-dan-tulis.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">3</div>
                    <h4 class="mt-4 text-base font-bold text-white">Distributed Atomic Lock</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Kunci sementara <code class="text-emerald-300 bg-slate-800 px-1 py-0.5 rounded">Cache::lock("court:{id}:slot:{ts}", 5)</code> untuk meredam kontensi request paralel tinggi.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 font-bold font-mono">4</div>
                    <h4 class="mt-4 text-base font-bold text-white">Auto-Expire Hold 15 Menit</h4>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Slot di-hold selama batas waktu bayar DP. Scheduler tiap menit otomatis melepas slot jika tidak dibayar (status EXPIRED).
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
