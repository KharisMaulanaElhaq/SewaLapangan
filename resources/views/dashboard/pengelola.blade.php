@extends('layouts.app')

@section('title', 'Dashboard Pengelola Venue')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" 
    x-data="{ 
        showBlockModal: false, 
        showAddCourtModal: false,
        showPriceModal: false,
        selectedCourtId: null,
        selectedCourtName: ''
    }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800 uppercase tracking-wider">Portal Pengelola</span>
                <span class="text-xs text-slate-400">·</span>
                <span class="text-xs text-slate-500">{{ Auth::user()->name }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mt-1">Dashboard & Manajemen Venue</h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" @click="showAddCourtModal = true"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm shadow-emerald-600/30 hover:bg-emerald-700 transition flex items-center gap-1.5">
                <span>+ Tambah Lapangan Baru</span>
            </button>
            <button type="button" @click="showBlockModal = true"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition flex items-center gap-1.5">
                <span>⛔ Blokir Jadwal (Maintenance)</span>
            </button>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-semibold uppercase tracking-wider">Pendapatan DP (Settled)</span>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">💰</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black font-mono text-slate-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Dana DP tervalidasi Midtrans</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-semibold uppercase tracking-wider">Total Booking Masuk</span>
                <span class="rounded-lg bg-blue-50 p-2 text-blue-600">📋</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black font-mono text-slate-900">{{ $totalBookings }}</span>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $confirmedBookings }} Terkonfirmasi / Lunas DP</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-semibold uppercase tracking-wider">Tingkat Okupansi</span>
                <span class="rounded-lg bg-purple-50 p-2 text-purple-600">📊</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black font-mono text-slate-900">{{ $occupancyRate }}%</span>
                <p class="text-[11px] text-purple-600 font-medium mt-0.5">Rasio pemakaian lapangan</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-semibold uppercase tracking-wider">Jadwal Hari Ini</span>
                <span class="rounded-lg bg-amber-50 p-2 text-amber-600">📅</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black font-mono text-slate-900">{{ $todayBookings->count() }}</span>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Pertandingan hari ini</p>
            </div>
        </div>
    </div>

    <!-- SECTION: KELOLA LAPANGAN & HARGA (FR-05 & FR-06) -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-extrabold text-slate-900">Manajemen Lapangan & Aturan Harga</h3>
                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">FR-05 & FR-06</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Kelola daftar lapangan, status aktif/nonaktif, jam operasional, dan aturan harga (weekday/weekend/prime time).</p>
            </div>

            <button type="button" @click="showAddCourtModal = true"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition flex items-center gap-1.5 self-start sm:self-auto">
                <span>+ Tambah Lapangan</span>
            </button>
        </div>

        <!-- Venues and their courts -->
        <div class="space-y-8">
            @foreach($venues as $venue)
                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/80 pb-3">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Venue</span>
                            <h4 class="text-base font-extrabold text-slate-900">{{ $venue->name }}</h4>
                            <p class="text-xs text-slate-500">📍 {{ $venue->address }}, {{ $venue->city }} · DP: {{ $venue->dp_percent }}%</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-lg bg-white border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                {{ $venue->courts->count() }} Lapangan
                            </span>
                        </div>
                    </div>

                    <!-- Court Cards Grid -->
                    @if($venue->courts->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400">
                            Belum ada lapangan terdaftar pada venue ini. Silakan klik "+ Tambah Lapangan".
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($venue->courts as $court)
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
                                    <div class="space-y-3">
                                        <!-- Court Photo Banner -->
                                        <div class="relative h-36 w-full overflow-hidden rounded-xl bg-slate-100 shadow-inner">
                                            <img src="{{ $court->primary_image_url }}" alt="{{ $court->name }}"
                                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105">
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent"></div>
                                            <span class="absolute bottom-2 left-2 rounded-md bg-slate-900/80 backdrop-blur-sm px-2 py-0.5 text-[10px] font-bold uppercase text-white font-mono">
                                                {{ strtoupper(str_replace('_', ' ', $court->sport_type)) }}
                                            </span>
                                            <span class="absolute top-2 right-2 rounded-full {{ $court->is_active ? 'bg-emerald-600/90 text-white' : 'bg-slate-700/90 text-slate-200' }} px-2.5 py-0.5 text-[10px] font-bold shadow-sm">
                                                {{ $court->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            @if($court->images && count($court->images) > 1)
                                                <span class="absolute top-2 left-2 rounded-full bg-slate-900/80 text-white px-2 py-0.5 text-[10px] font-semibold flex items-center gap-1">
                                                    📷 {{ count($court->images) }} Foto
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Header Court -->
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <h5 class="font-extrabold text-slate-900 text-sm">{{ $court->name }}</h5>
                                                <span class="inline-block mt-0.5 text-xs text-slate-400">
                                                    Venue: {{ $venue->name }}
                                                </span>
                                            </div>
                                        </div>


                                        <!-- Pricing Rules List -->
                                        <div class="border-t border-slate-100 pt-2 space-y-1.5 text-xs">
                                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Daftar Tarif / Jam:</span>
                                            @forelse($court->courtPrices as $cp)
                                                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-mono border border-slate-100">
                                                    <div>
                                                        <span class="font-bold uppercase text-slate-700">{{ $cp->day_type }}</span>
                                                        <span class="text-slate-400">({{ substr($cp->start_time, 0, 5) }}-{{ substr($cp->end_time, 0, 5) }})</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="font-extrabold text-emerald-700">Rp {{ number_format($cp->price, 0, ',', '.') }}</span>
                                                        @if($court->courtPrices->count() > 1)
                                                            <form action="{{ route('pengelola.price.destroy', $cp->id) }}" method="POST" onsubmit="return confirm('Hapus aturan harga ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold ml-1" title="Hapus tarif">&times;</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-[11px] text-slate-400 italic">Belum ada aturan harga.</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="border-t border-slate-100 pt-3 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-1">
                                            <!-- Add price rule button -->
                                            <button type="button" @click="selectedCourtId = {{ $court->id }}; selectedCourtName = '{{ $court->name }}'; showPriceModal = true;"
                                                class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition"
                                                title="Tambah aturan harga weekday / weekend / prime time">
                                                + Atur Harga
                                            </button>

                                            <!-- Toggle active button -->
                                            <form action="{{ route('pengelola.court.update', $court->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="toggle_active" value="1">
                                                <button type="submit" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition">
                                                    {{ $court->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Delete court button -->
                                        <form action="{{ route('pengelola.court.destroy', $court->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lapangan {{ $court->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-100 transition" title="Hapus lapangan">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Today's Schedule Card -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
            <span>Jadwal Penggunaan Hari Ini ({{ \Carbon\Carbon::now('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }})</span>
            <span class="text-xs text-slate-400 font-normal">Waktu Indonesia Barat (WIB)</span>
        </h3>

        @if($todayBookings->isEmpty())
            <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada pertandingan terjadwal untuk hari ini.</p>
        @else
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($todayBookings as $tb)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $tb->code }}</span>
                                <h4 class="font-bold text-emerald-800 text-sm mt-0.5">{{ $tb->court->name }}</h4>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                {{ $tb->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600">Penyewa: <strong>{{ $tb->user->name }}</strong> ({{ $tb->user->phone }})</p>
                        <div class="flex flex-wrap gap-1 font-mono text-[11px] text-slate-700">
                            @foreach($tb->bookingSlots as $s)
                                <span class="bg-white border border-slate-200 rounded px-2 py-0.5">
                                    {{ \Carbon\Carbon::parse($s->slot_start)->setTimezone('Asia/Jakarta')->format('H:i') }} - {{ \Carbon\Carbon::parse($s->slot_end)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Booking Management Table (FR-16: Pengelola menandai booking completed / no-show) -->
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Manajemen Semua Pemesanan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status pembayaran DP dan ubah status operasional kehadiran penyewa.</p>
            </div>

            <!-- Filter Status -->
            <form action="{{ route('pengelola.dashboard') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="CONFIRMED" {{ request('status') === 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED (Terkonfirmasi)</option>
                    <option value="PENDING_PAYMENT" {{ request('status') === 'PENDING_PAYMENT' ? 'selected' : '' }}>PENDING_PAYMENT</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Selesai)</option>
                    <option value="NO_SHOW" {{ request('status') === 'NO_SHOW' ? 'selected' : '' }}>NO_SHOW (Tidak Hadir)</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5">Kode Booking</th>
                        <th class="px-6 py-3.5">Penyewa</th>
                        <th class="px-6 py-3.5">Lapangan</th>
                        <th class="px-6 py-3.5">Slot Waktu</th>
                        <th class="px-6 py-3.5">DP / Total</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                {{ $booking->code }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 block">{{ $booking->user->name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $booking->user->phone }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $booking->court->name }}
                            </td>
                            <td class="px-6 py-4 font-mono text-[11px]">
                                @foreach($booking->bookingSlots as $s)
                                    <div>{{ \Carbon\Carbon::parse($s->slot_start)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</div>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 font-mono">
                                <span class="font-bold text-emerald-700 block">DP: Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400 block">Total: Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($booking->isPendingPayment())
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 border border-amber-200">PENDING DP</span>
                                @elseif($booking->isConfirmed())
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200">CONFIRMED</span>
                                @elseif($booking->isCompleted())
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 border border-blue-200">COMPLETED</span>
                                @elseif($booking->isNoShow())
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 border border-rose-200">NO SHOW</span>
                                @elseif($booking->isCancelled())
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500 border border-slate-200">CANCELLED</span>
                                @elseif($booking->isExpired())
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-400 border border-slate-200">EXPIRED</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($booking->isConfirmed())
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Form to mark as COMPLETED -->
                                        <form action="{{ route('pengelola.booking.status', $booking->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="COMPLETED">
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700 transition" title="Tandai penyewa hadir & selesai">
                                                ✓ Selesai
                                            </button>
                                        </form>

                                        <!-- Form to mark as NO_SHOW -->
                                        <form action="{{ route('pengelola.booking.status', $booking->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="NO_SHOW">
                                            <button type="submit" class="rounded-lg border border-rose-300 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-100 transition" title="Tandai penyewa tidak hadir">
                                                ✕ No Show
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">Tidak ada booking yang sesuai kriteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $bookings->links() }}
        </div>
    </div>

    <!-- MODAL 1: Tambah Lapangan Baru (FR-05 & FR-06) -->
    <div x-show="showAddCourtModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
        <div @click.outside="showAddCourtModal = false" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-4 my-8 max-h-[90vh] overflow-y-auto"
            x-data="{
                previewUrls: [],
                handleFileSelect(event) {
                    this.previewUrls = [];
                    const files = event.target.files;
                    if (!files) return;
                    for (let i = 0; i < files.length; i++) {
                        this.previewUrls.push(URL.createObjectURL(files[i]));
                    }
                }
            }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tambah Lapangan Baru (FR-05)</h3>
                    <p class="text-xs text-slate-500">Daftarkan lapangan baru, unggah foto, dan tentukan harga sewa dasar.</p>
                </div>
                <button type="button" @click="showAddCourtModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('pengelola.court.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Venue</label>
                    <select name="venue_id" required class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:outline-none">
                        @foreach($venues as $v)
                            <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lapangan</label>
                    <input type="text" name="name" required placeholder="Contoh: Lapangan Futsal B (Interlock)"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Olahraga</label>
                    <select name="sport_type" required class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:outline-none">
                        <option value="futsal">Futsal</option>
                        <option value="badminton">Badminton</option>
                        <option value="basketball">Basketball</option>
                        <option value="mini_soccer">Mini Soccer</option>
                        <option value="tennis">Tennis</option>
                        <option value="volleyball">Volleyball</option>
                    </select>
                </div>

                <!-- Foto-foto Lapangan -->
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800">Foto Lapangan (Bisa Unggah Banyak)</label>
                        <span class="text-[10px] text-slate-400">JPG, PNG, WebP (Max 5MB)</span>
                    </div>

                    <input type="file" name="photos[]" multiple accept="image/*" @change="handleFileSelect($event)"
                        class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">

                    <!-- Preview Thumbnail Grid -->
                    <template x-if="previewUrls.length > 0">
                        <div class="mt-2 space-y-1">
                            <span class="text-[10px] font-bold text-emerald-800 block">Preview Foto yang Dipilih (<span x-text="previewUrls.length"></span> foto):</span>
                            <div class="grid grid-cols-4 gap-2 pt-1">
                                <template x-for="(url, index) in previewUrls" :key="index">
                                    <div class="relative h-16 w-full rounded-lg overflow-hidden border border-slate-200 shadow-sm bg-white">
                                        <img :src="url" class="h-full w-full object-cover">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div>
                        <span class="text-[10px] text-slate-500 block mb-1">Atau masukkan URL Foto Langsung (opsional):</span>
                        <input type="url" name="image_url" placeholder="https://images.unsplash.com/... (opsional)"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Sewa Standar per Jam (Rp)</label>
                    <input type="number" name="price" required min="10000" step="5000" placeholder="100000"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-xs font-mono text-slate-800 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Buka Operasional</label>
                        <input type="time" name="open_time" value="08:00" required
                            class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Tutup Operasional</label>
                        <input type="time" name="close_time" value="23:00" required
                            class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAddCourtModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm">
                        Simpan Lapangan Baru
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL 2: Atur Harga Slot Khusus (FR-06: Weekday / Weekend / Prime Time) -->
    <div x-show="showPriceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div @click.outside="showPriceModal = false" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Atur Tarif Harga Slot (FR-06)</h3>
                    <p class="text-xs text-slate-500">Lapangan: <strong class="text-emerald-700" x-text="selectedCourtName"></strong></p>
                </div>
                <button type="button" @click="showPriceModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('pengelola.price.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="court_id" :value="selectedCourtId">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berlaku Pada Hari</label>
                    <select name="day_type" required class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:outline-none">
                        <option value="weekday">Hari Kerja (Weekday: Senin - Jumat)</option>
                        <option value="weekend">Akhir Pekan (Weekend: Sabtu - Minggu)</option>
                        <option value="all">Semua Hari (Setiap Hari)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" value="17:00" required
                            class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" name="end_time" value="23:00" required
                            class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Harga per Jam (Rp)</label>
                    <input type="number" name="price" required min="10000" step="5000" placeholder="Contoh: 180000 (Jam Prime)"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-xs font-mono text-slate-800 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showPriceModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm">
                        Simpan Aturan Harga
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Form Add Schedule Block (FR-08: Blokir jadwal manual oleh pengelola) -->
    <div x-show="showBlockModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div @click.outside="showBlockModal = false" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Blokir Jadwal Lapangan (Maintenance / Event)</h3>
                <button type="button" @click="showBlockModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('pengelola.block.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Lapangan</label>
                    <select name="court_id" required class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:outline-none">
                        @foreach($venues as $v)
                            <optgroup label="{{ $v->name }}">
                                @foreach($v->courts as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ strtoupper($c->sport_type) }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Blokir</label>
                        <input type="datetime-local" name="start_at" required
                            class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Selesai Blokir</label>
                        <input type="datetime-local" name="end_at" required
                            class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pemblokiran</label>
                    <input type="text" name="reason" required placeholder="Contoh: Perbaikan rumput sintetis gawang selatan"
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showBlockModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                        Simpan Blokir Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
