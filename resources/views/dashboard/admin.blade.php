@extends('layouts.app')

@section('title', 'Admin Platform Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" x-data="{ payloadModal: false, activePayload: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-800 uppercase tracking-wider">Super Administrator</span>
                <span class="text-xs text-slate-400">·</span>
                <span class="text-xs text-slate-500">Platform Control Center</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mt-1">Admin Platform Dashboard</h1>
        </div>
    </div>

    <!-- Overview Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Pengguna</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono text-slate-900">{{ $totalUsers }}</span>
                <span class="text-xs font-semibold text-emerald-600">{{ $activePengelola }} Pengelola Aktif</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Antrean Verifikasi Pengelola</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono {{ $pendingPengelola->count() > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                    {{ $pendingPengelola->count() }}
                </span>
                <span class="text-xs text-slate-400">Menunggu persetujuan</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Transaksi DP Settled</span>
            <div class="mt-2">
                <span class="text-2xl font-black font-mono text-emerald-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $transactions->total() }} record pembayaran</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Status Webhook Gateway</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono {{ $failedWebhooksCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $failedWebhooksCount }} Error
                </span>
                <span class="text-xs text-slate-500 font-medium">Dari {{ $webhookLogs->count() }} event</span>
            </div>
        </div>
    </div>

    <!-- FR-03: Pengelola Verification Section -->
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Verifikasi Calon Pengelola Venue (FR-03)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pengelola baru berstatus pending sampai diverifikasi dan diaktifkan oleh admin.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                {{ $pendingPengelola->count() }} Pending
            </span>
        </div>

        @if($pendingPengelola->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400 italic">
                Tidak ada pendaftaran pengelola yang sedang menunggu verifikasi saat ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-3.5">Nama Calon Pengelola</th>
                            <th class="px-6 py-3.5">Email</th>
                            <th class="px-6 py-3.5">No. Telepon / WhatsApp</th>
                            <th class="px-6 py-3.5">Waktu Registrasi</th>
                            <th class="px-6 py-3.5 text-right">Keputusan Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingPengelola as $p)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $p->name }}</td>
                                <td class="px-6 py-4 font-mono text-slate-600">{{ $p->email }}</td>
                                <td class="px-6 py-4 font-mono text-slate-600">{{ $p->phone ?: '-' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $p->created_at->setTimezone('Asia/Jakarta')->isoFormat('D MMM Y, H:mm') }} WIB</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Approve form -->
                                        <form action="{{ route('admin.pengelola.verify', $p->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition">
                                                ✓ Setujui (Aktifkan)
                                            </button>
                                        </form>

                                        <!-- Reject form -->
                                        <form action="{{ route('admin.pengelola.verify', $p->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                                                ✕ Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Webhook Gateway Monitoring (§7.7 & NFR-04) -->
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Monitoring Webhook Gateway Midtrans (§7.7)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Audit log callback gateway, validasi SHA-512 signature, dan deteksi idempotensi duplikat.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-mono font-semibold text-slate-600">
                Log Terbaru
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5">ID / Order ID</th>
                        <th class="px-6 py-3.5">Provider</th>
                        <th class="px-6 py-3.5">Signature Valid</th>
                        <th class="px-6 py-3.5">Status Proses</th>
                        <th class="px-6 py-3.5">Waktu Diterima</th>
                        <th class="px-6 py-3.5 text-right">Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($webhookLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-3.5 font-mono font-bold text-slate-900">
                                #{{ $log->id }} · {{ $log->order_id ?: '(tanpa order_id)' }}
                            </td>
                            <td class="px-6 py-3.5 uppercase font-mono font-semibold text-slate-700">
                                {{ $log->provider }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($log->signature_valid)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                        ✓ Valid (SHA512)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                        ✕ Invalid Signature
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="rounded px-2 py-0.5 text-[10px] font-mono font-bold {{ $log->status === 'processed' ? 'bg-emerald-100 text-emerald-800' : ($log->status === 'duplicate_ignored' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 font-mono text-[11px]">
                                {{ $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <button type="button" @click="activePayload = {{ json_encode($log->payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}; payloadModal = true;"
                                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    Lihat JSON
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada aktivitas webhook tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transactions Audit Table -->
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Transaksi Platform (§7.7)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Semua riwayat transaksi pembayaran DP dan status settlement Midtrans.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5">Order ID</th>
                        <th class="px-6 py-3.5">Kode Booking</th>
                        <th class="px-6 py-3.5">Pelanggan</th>
                        <th class="px-6 py-3.5">Nominal DP</th>
                        <th class="px-6 py-3.5">Status Gateway</th>
                        <th class="px-6 py-3.5">Metode Bayar</th>
                        <th class="px-6 py-3.5 text-right">Waktu Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-3.5 font-mono font-bold text-slate-900">{{ $t->order_id }}</td>
                            <td class="px-6 py-3.5 font-mono text-emerald-800">{{ $t->booking->code ?? '-' }}</td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">{{ $t->booking->user->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 font-mono font-black text-slate-900">Rp {{ number_format($t->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-3.5">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $t->status === 'settlement' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-mono uppercase text-slate-600">{{ $t->payment_type ?: '-' }}</td>
                            <td class="px-6 py-3.5 text-right font-mono text-[11px] text-slate-500">
                                {{ $t->paid_at ? \Carbon\Carbon::parse($t->paid_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada transaksi tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- JSON Payload Viewer Modal -->
    <div x-show="payloadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div @click.outside="payloadModal = false" class="w-full max-w-2xl rounded-3xl bg-slate-900 p-6 text-white shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold font-mono">Raw Webhook Payload JSON</h3>
                <button type="button" @click="payloadModal = false" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <pre class="max-h-96 overflow-y-auto rounded-xl bg-slate-950 p-4 text-xs font-mono text-emerald-400" x-text="JSON.stringify(activePayload, null, 2)"></pre>

            <div class="flex justify-end pt-2">
                <button type="button" @click="payloadModal = false" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
