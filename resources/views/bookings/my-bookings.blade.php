@extends('layouts.app')

@section('title', 'Riwayat Booking Saya')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat Pemesanan Saya</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola status pesanan, pembayaran DP, dan pembatalan jadwal olahraga Anda.</p>
        </div>

        <a href="{{ route('home') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition self-start sm:self-auto">
            + Pesan Lapangan Baru
        </a>
    </div>

    <!-- Filter Status Tabs -->
    <div class="flex flex-wrap gap-2 text-xs font-semibold">
        <a href="{{ route('my.bookings') }}" class="rounded-xl px-3.5 py-2 border transition {{ !request('status') ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Semua ({{ \App\Models\Booking::where('user_id', Auth::id())->count() }})
        </a>
        <a href="{{ route('my.bookings', ['status' => 'PENDING_PAYMENT']) }}" class="rounded-xl px-3.5 py-2 border transition {{ request('status') === 'PENDING_PAYMENT' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Menunggu Bayar ({{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'PENDING_PAYMENT')->count() }})
        </a>
        <a href="{{ route('my.bookings', ['status' => 'CONFIRMED']) }}" class="rounded-xl px-3.5 py-2 border transition {{ request('status') === 'CONFIRMED' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Terkonfirmasi ({{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'CONFIRMED')->count() }})
        </a>
        <a href="{{ route('my.bookings', ['status' => 'COMPLETED']) }}" class="rounded-xl px-3.5 py-2 border transition {{ request('status') === 'COMPLETED' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Selesai ({{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'COMPLETED')->count() }})
        </a>
        <a href="{{ route('my.bookings', ['status' => 'CANCELLED']) }}" class="rounded-xl px-3.5 py-2 border transition {{ request('status') === 'CANCELLED' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Dibatalkan ({{ \App\Models\Booking::where('user_id', Auth::id())->where('status', 'CANCELLED')->count() }})
        </a>
    </div>

    @if($bookings->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center my-6">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-3 text-sm font-semibold text-slate-900">Belum ada riwayat booking</h3>
            <p class="mt-1 text-xs text-slate-500">Temukan lapangan favorit Anda dan buat jadwal pertandingan sekarang.</p>
            <div class="mt-6">
                <a href="{{ route('home') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500">
                    Cari Lapangan &rarr;
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $b)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition p-5 flex flex-col md:flex-row md:items-center justify-between gap-6"
                    x-data="{ showCancelModal: false }">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-slate-900 text-sm">{{ $b->code }}</span>

                            @if($b->isPendingPayment())
                                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200">
                                    ⏳ Menunggu Bayar DP
                                </span>
                            @elseif($b->isConfirmed())
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">
                                    ✓ Terkonfirmasi
                                </span>
                            @elseif($b->isCompleted())
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 border border-blue-200">
                                    ✓ Selesai
                                </span>
                            @elseif($b->isExpired())
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-500 border border-slate-200">
                                    Kedaluwarsa
                                </span>
                            @elseif($b->isCancelled())
                                <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-bold text-rose-700 border border-rose-200">
                                    Dibatalkan
                                </span>
                            @endif
                        </div>

                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base">{{ $b->court->venue->name }} — {{ $b->court->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $b->court->venue->city }} · {{ $b->court->venue->address }}</p>
                        </div>

                        <!-- Slots List -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($b->bookingSlots as $s)
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-mono font-semibold text-slate-700">
                                    {{ \Carbon\Carbon::parse($s->slot_start)->setTimezone('Asia/Jakarta')->isoFormat('D MMM Y, H:mm') }} - {{ \Carbon\Carbon::parse($s->slot_end)->setTimezone('Asia/Jakarta')->format('H:mm') }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col md:items-end justify-between gap-3 border-t md:border-t-0 border-slate-100 pt-4 md:pt-0">
                        <div class="text-left md:text-right">
                            <span class="text-[11px] text-slate-400 block">Total DP</span>
                            <span class="text-lg font-black font-mono text-emerald-600">Rp {{ number_format($b->dp_amount, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-500 block">Total Sewa: Rp {{ number_format($b->total_price, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($b->isPendingPayment())
                                <a href="{{ route('booking.checkout', $b->id) }}" class="rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                                    Bayar DP Sekarang &rarr;
                                </a>
                                <button type="button" @click="showCancelModal = true" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                    Batal
                                </button>
                            @elseif($b->isConfirmed())
                                <a href="{{ route('booking.checkout', $b->id) }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    Detail Booking
                                </a>
                                <button type="button" @click="showCancelModal = true" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                    Batalkan Booking
                                </button>
                            @else
                                <a href="{{ route('booking.checkout', $b->id) }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    Lihat Riwayat
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Cancel Modal Form with venue policy calculation -->
                    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                        <div @click.outside="showCancelModal = false" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-base font-bold text-slate-900">Batalkan Booking {{ $b->code }}</h3>
                                <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                            </div>

                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-900 space-y-1">
                                <p class="font-bold">Ketentuan Pembatalan Venue:</p>
                                <p>• Pembatalan gratis: minimal {{ $b->cancel_free_hours_snapshot }} jam sebelum slot dimulai.</p>
                                <p>• Pengembalian dana DP: {{ $b->cancel_refund_percent_snapshot }}% jika memenuhi batas waktu.</p>
                            </div>

                            <form action="{{ route('booking.cancel', $b->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan</label>
                                    <textarea name="reason" rows="2" required placeholder="Contoh: Perubahan jadwal anggota tim..."
                                        class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-rose-500 focus:outline-none"></textarea>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="showCancelModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                        Kembali
                                    </button>
                                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700">
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
