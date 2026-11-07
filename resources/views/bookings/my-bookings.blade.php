@extends('layouts.app')

@section('title', 'Riwayat Booking Saya')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6 pb-16">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-200/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Akun Penyewa
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-ink-900 mt-1">Riwayat Pemesanan Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola status pesanan, pembayaran DP Midtrans, dan kebijakan pembatalan jadwal Anda.</p>
        </div>

        <x-ui.button :href="route('home')" variant="primary" size="md">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Pesan Lapangan Baru</span>
        </x-ui.button>
    </div>

    <!-- Filter Status Tabs (Horizontal Scrollable on Mobile) -->
    <div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="flex items-center gap-2 text-xs font-bold min-w-max">
            <a href="{{ route('my.bookings', request()->except(['status', 'page'])) }}"
                class="rounded-xl px-4 py-2.5 border transition-all duration-200 min-h-[44px] flex items-center gap-2 {{ !request('status') ? 'bg-ink-900 text-white border-ink-900 shadow-soft-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <span>Semua</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ !request('status') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ \App\Models\Booking::where('user_id', Auth::id())->count() }}
                </span>
            </a>

            <a href="{{ route('my.bookings', array_merge(request()->except('page'), ['status' => 'PENDING_PAYMENT'])) }}"
                class="rounded-xl px-4 py-2.5 border transition-all duration-200 min-h-[44px] flex items-center gap-2 {{ request('status') === 'PENDING_PAYMENT' ? 'bg-amber-500 text-white border-amber-500 shadow-soft-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ request('status') === 'PENDING_PAYMENT' ? 'bg-white' : 'bg-amber-500' }}"></span>
                <span>Menunggu Bayar</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ request('status') === 'PENDING_PAYMENT' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'PENDING_PAYMENT')->count() }}
                </span>
            </a>

            <a href="{{ route('my.bookings', array_merge(request()->except('page'), ['status' => 'CONFIRMED'])) }}"
                class="rounded-xl px-4 py-2.5 border transition-all duration-200 min-h-[44px] flex items-center gap-2 {{ request('status') === 'CONFIRMED' ? 'bg-emerald-600 text-white border-emerald-600 shadow-soft-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ request('status') === 'CONFIRMED' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                <span>Terkonfirmasi</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ request('status') === 'CONFIRMED' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'CONFIRMED')->count() }}
                </span>
            </a>

            <a href="{{ route('my.bookings', array_merge(request()->except('page'), ['status' => 'COMPLETED'])) }}"
                class="rounded-xl px-4 py-2.5 border transition-all duration-200 min-h-[44px] flex items-center gap-2 {{ request('status') === 'COMPLETED' ? 'bg-blue-600 text-white border-blue-600 shadow-soft-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ request('status') === 'COMPLETED' ? 'bg-white' : 'bg-blue-500' }}"></span>
                <span>Selesai</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ request('status') === 'COMPLETED' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'COMPLETED')->count() }}
                </span>
            </a>

            <a href="{{ route('my.bookings', array_merge(request()->except('page'), ['status' => 'CANCELLED'])) }}"
                class="rounded-xl px-4 py-2.5 border transition-all duration-200 min-h-[44px] flex items-center gap-2 {{ request('status') === 'CANCELLED' ? 'bg-rose-600 text-white border-rose-600 shadow-soft-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ request('status') === 'CANCELLED' ? 'bg-white' : 'bg-rose-500' }}"></span>
                <span>Dibatalkan</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ request('status') === 'CANCELLED' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'CANCELLED')->count() }}
                </span>
            </a>
        </div>
    </div>

    <!-- Search Code, Date Range Filter & Sort Bar -->
    <form method="GET" action="{{ route('my.bookings') }}" class="rounded-3xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-soft-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cari Kode Booking</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: BK-2026..."
                class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-mono text-slate-800 focus:border-emerald-500 focus:outline-none min-h-[44px]">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none min-h-[44px]">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none min-h-[44px]">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Urutkan</label>
            <select name="sort" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none min-h-[44px]">
                <option value="-created_at" {{ request('sort', '-created_at') === '-created_at' ? 'selected' : '' }}>Terbaru Dibuat</option>
                <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Terlama Dibuat</option>
                <option value="-total_price" {{ request('sort') === '-total_price' ? 'selected' : '' }}>Total Harga Tertinggi</option>
                <option value="total_price" {{ request('sort') === 'total_price' ? 'selected' : '' }}>Total Harga Terendah</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-ink-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition min-h-[44px] flex items-center justify-center">
                Terapkan Filter
            </button>
            @if(request()->hasAny(['q', 'date_from', 'date_to', 'sort', 'status']))
                <a href="{{ route('my.bookings') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition min-h-[44px] flex items-center justify-center">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($bookings->isEmpty())
        <x-ui.empty-state
            title="Belum ada riwayat booking"
            description="Temukan lapangan olahraga favorit Anda dan buat jadwal pertandingan sekarang."
            actionText="Cari & Pesan Lapangan Sekarang"
            :actionHref="route('home')">
            <x-slot:icon>
                <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </x-slot:icon>
        </x-ui.empty-state>
    @else
        <div class="space-y-4">
            @foreach($bookings as $b)
                <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-soft-sm hover:shadow-soft-md transition-all duration-300 p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-6"
                    x-data="{ showCancelModal: false }">
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-black text-ink-900 text-sm tracking-wide">{{ $b->code }}</span>

                            @if($b->isPendingPayment())
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-300/80">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Bayar DP
                                </span>
                            @elseif($b->isConfirmed())
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200/80">
                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Terkonfirmasi
                                </span>
                            @elseif($b->isCompleted())
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800 border border-blue-200/80">
                                    <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Selesai
                                </span>
                            @elseif($b->isExpired())
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 border border-slate-200">
                                    Kedaluwarsa
                                </span>
                            @elseif($b->isCancelled())
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-800 border border-rose-200/80">
                                    Dibatalkan
                                </span>
                            @endif
                        </div>

                        <div>
                            <h3 class="font-black text-ink-900 text-base sm:text-lg leading-tight">{{ $b->court->venue->name }} — {{ $b->court->name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $b->court->venue->city }} · {{ $b->court->venue->address }}</p>
                        </div>

                        <!-- Slots List (FIXED END TIME FORMAT: format('H:i')) -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($b->bookingSlots as $s)
                                <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100/90 px-3 py-1 text-xs font-mono font-semibold text-slate-700 border border-slate-200">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($s->slot_start)->setTimezone('Asia/Jakarta')->isoFormat('D MMM Y, HH:mm') }} - {{ \Carbon\Carbon::parse($s->slot_end)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col md:items-end justify-between gap-3.5 border-t md:border-t-0 border-slate-100 pt-4 md:pt-0">
                        <div class="text-left md:text-right">
                            <span class="text-[11px] text-slate-400 block font-medium">Total DP</span>
                            <span class="text-xl font-black font-mono text-emerald-600 leading-tight">Rp {{ number_format($b->dp_amount, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-slate-500 block font-medium">Total Sewa: Rp {{ number_format($b->total_price, 0, ',', '.') }}</span>
                        </div>

                        <!-- Action Buttons Hierarchy: Primary / Secondary / Danger -->
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ \Illuminate\Support\Facades\Route::has('booking.invoice') ? route('booking.invoice', $b->id) : route('booking.checkout', ['id' => $b->id, 'view' => 'invoice']) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition min-h-[44px]">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>E-Ticket / Invoice</span>
                            </a>

                            @if($b->isPendingPayment())
                                <a href="{{ route('booking.checkout', $b->id) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/25 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition min-h-[44px]">
                                    <span>Bayar DP Sekarang</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                                <button type="button" @click="showCancelModal = true"
                                    class="rounded-xl border border-rose-200 bg-white px-3.5 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition min-h-[44px]">
                                    Batal
                                </button>
                            @elseif($b->isConfirmed())
                                <a href="{{ route('booking.checkout', $b->id) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition min-h-[44px]">
                                    <span>Detail Booking</span>
                                </a>
                                <button type="button" @click="showCancelModal = true"
                                    class="rounded-xl border border-rose-200 bg-white px-3.5 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition min-h-[44px]">
                                    Batalkan Booking
                                </button>
                            @else
                                <a href="{{ route('booking.checkout', $b->id) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition min-h-[44px]">
                                    <span>Lihat Riwayat</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Cancel Modal Form with venue policy calculation -->
                    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/70 backdrop-blur-sm p-4">
                        <div @click.outside="showCancelModal = false" class="w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-soft-xl space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3 class="text-base font-black text-ink-900">Batalkan Booking</h3>
                                    <span class="text-xs font-mono font-bold text-slate-500">{{ $b->code }}</span>
                                </div>
                                <button type="button" @click="showCancelModal = false" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 transition" aria-label="Tutup modal">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-900 space-y-1.5 shadow-soft-xs">
                                <p class="font-bold flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>Ketentuan Pembatalan Venue:</span>
                                </p>
                                <p>• Pembatalan gratis: minimal <strong>{{ $b->cancel_free_hours_snapshot }} jam</strong> sebelum slot dimulai.</p>
                                <p>• Pengembalian dana DP: <strong>{{ $b->cancel_refund_percent_snapshot }}%</strong> jika memenuhi batas waktu.</p>
                            </div>

                            <form action="{{ route('booking.cancel', $b->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Pembatalan</label>
                                    <textarea name="reason" rows="3" required placeholder="Contoh: Perubahan jadwal anggota tim..."
                                        class="w-full rounded-2xl border border-slate-200 p-3 text-xs text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20"></textarea>
                                </div>

                                <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100">
                                    <button type="button" @click="showCancelModal = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 min-h-[44px]">
                                        Kembali
                                    </button>
                                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-rose-700 min-h-[44px] shadow-sm shadow-rose-600/30">
                                        Konfirmasi Batalkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="pt-4">
                {{ $bookings->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
