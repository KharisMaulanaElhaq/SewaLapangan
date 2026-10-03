@extends('layouts.app')

@section('title', 'Dokumentasi REST API v1 — Platform Booking Lapangan Olahraga')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" x-data="apiExplorer()">
    <!-- Banner Header -->
    <div class="rounded-3xl bg-slate-900 text-white p-8 sm:p-10 shadow-xl border border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md bg-emerald-500/20 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/30">
                    REST API SPECIFICATION v1.0
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-3">
                    Dokumentasi Web Service Booking Lapangan
                </h1>
                <p class="text-slate-300 text-sm mt-2 max-w-3xl leading-relaxed">
                    Mata Kuliah: Pemrograman Web Servis · Kelompok 3 · Teknik Informatika, Universitas Harkat Negeri (2026).
                    Menyediakan endpoint lengkap ketersediaan slot real-time, penguncian atomik anti double booking, dan integrasi webhook Midtrans.
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-400 font-mono">
                    <span class="rounded-lg bg-slate-800 px-3 py-1.5 border border-slate-700">Base URL: <strong class="text-emerald-400">{{ url('/api/v1') }}</strong></span>
                    <span class="rounded-lg bg-slate-800 px-3 py-1.5 border border-slate-700">Format: <strong class="text-slate-200">application/json</strong></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('docs.postman') }}"
                    class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-600 hover:to-teal-600 transition flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Download Postman Collection</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Token Generator & Tester Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Bearer Token Generator (Pengujian Cepat)</h3>
                <p class="text-xs text-slate-600 mt-0.5">Pilih akun contoh untuk menginjeksi Sanctum Token secara instan ke API Tester di bawah:</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="fetchDemoToken('arif.penyewa@gmail.com')" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    👤 Token Penyewa
                </button>
                <button type="button" @click="fetchDemoToken('budi.pengelola@gmail.com')" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    🏢 Token Pengelola
                </button>
                <button type="button" @click="fetchDemoToken('admin@harkat.ac.id')" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    🛡️ Token Admin
                </button>
            </div>
        </div>

        <div class="flex gap-2">
            <input type="text" x-model="bearerToken" placeholder="Masukkan token Sanctum Bearer (atau klik salah satu tombol di atas)..."
                class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono text-slate-800 focus:border-emerald-500 focus:outline-none">
            <button type="button" @click="bearerToken = ''" class="rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-500 hover:bg-slate-50">
                Clear
            </button>
        </div>
    </div>

    <!-- Endpoints List -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-4 space-y-2">
            <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Daftar Endpoint API</h3>
                
                <div class="space-y-1 text-xs">
                    <template x-for="(ep, idx) in endpoints" :key="idx">
                        <button type="button" @click="selectEndpoint(idx)"
                            :class="activeIdx === idx ? 'bg-emerald-50 text-emerald-900 border-emerald-300 font-bold' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                            class="w-full text-left rounded-xl px-3 py-2 border transition flex items-center justify-between gap-2">
                            <span class="truncate" x-text="ep.title"></span>
                            <span class="rounded px-1.5 py-0.5 text-[10px] font-mono font-bold uppercase"
                                :class="{
                                    'bg-emerald-100 text-emerald-800': ep.method === 'GET',
                                    'bg-blue-100 text-blue-800': ep.method === 'POST',
                                    'bg-amber-100 text-amber-800': ep.method === 'PATCH' || ep.method === 'PUT',
                                    'bg-rose-100 text-rose-800': ep.method === 'DELETE',
                                }" x-text="ep.method"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Main Tester & Specification View -->
        <div class="lg:col-span-8 space-y-6">
            <template x-if="activeEndpoint">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded px-2.5 py-1 text-xs font-mono font-bold uppercase"
                                :class="{
                                    'bg-emerald-100 text-emerald-800': activeEndpoint.method === 'GET',
                                    'bg-blue-100 text-blue-800': activeEndpoint.method === 'POST',
                                    'bg-amber-100 text-amber-800': activeEndpoint.method === 'PATCH' || activeEndpoint.method === 'PUT',
                                    'bg-rose-100 text-rose-800': activeEndpoint.method === 'DELETE',
                                }" x-text="activeEndpoint.method"></span>
                            <span class="text-xs font-semibold text-slate-400 font-mono" x-text="activeEndpoint.auth ? '🔒 Perlu Autentikasi (' + activeEndpoint.auth + ')' : '🌐 Publik'"></span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-2" x-text="activeEndpoint.title"></h2>
                        <p class="text-xs text-slate-500 mt-1" x-text="activeEndpoint.description"></p>
                    </div>

                    <!-- URL Bar -->
                    <div class="flex items-center gap-2 rounded-2xl bg-slate-900 p-3 text-white font-mono text-xs">
                        <span class="text-emerald-400 font-bold" x-text="activeEndpoint.method"></span>
                        <input type="text" x-model="activeEndpoint.endpoint" class="flex-1 bg-transparent text-slate-200 focus:outline-none">
                        <button type="button" @click="executeRequest()" :disabled="isLoading"
                            class="rounded-xl bg-emerald-600 px-4 py-1.5 font-sans font-bold text-white hover:bg-emerald-500 transition disabled:opacity-50">
                            <span x-show="!isLoading">Send Request &rarr;</span>
                            <span x-show="isLoading">Loading...</span>
                        </button>
                    </div>

                    <!-- Request Body editor if POST / PUT / PATCH -->
                    <div x-show="['POST', 'PUT', 'PATCH'].includes(activeEndpoint.method)" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Request Body (JSON)</label>
                        <textarea x-model="activeEndpoint.body" rows="6"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-950 p-4 font-mono text-xs text-emerald-300 focus:outline-none focus:border-emerald-500"></textarea>
                    </div>

                    <!-- Response Viewer -->
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Response Output</span>
                            <div class="flex items-center gap-2 font-mono text-xs" x-show="responseStatus">
                                <span class="rounded px-2 py-0.5 font-bold"
                                    :class="responseStatus >= 200 && responseStatus < 300 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                    x-text="'Status: ' + responseStatus"></span>
                                <span class="text-slate-400" x-text="responseTime + ' ms'"></span>
                            </div>
                        </div>

                        <pre class="max-h-96 overflow-y-auto rounded-2xl bg-slate-950 p-5 font-mono text-xs text-slate-200"
                            x-text="responseText || '// Klik \"Send Request\" untuk menguji endpoint...'"></pre>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function apiExplorer() {
        return {
            bearerToken: '',
            activeIdx: 0,
            isLoading: false,
            responseStatus: null,
            responseTime: 0,
            responseText: '',

            endpoints: [
                {
                    title: '1. Login Pengguna (Ambil Token Sanctum)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/auth/login") }}',
                    auth: null,
                    description: 'Otentikasi pengguna (penyewa, pengelola, admin) menggunakan kredensial email & password, mengembalikan Token Sanctum.',
                    body: JSON.stringify({
                        email: "arif.penyewa@gmail.com",
                        password: "password123"
                    }, null, 2)
                },
                {
                    title: '2. Registrasi Akun Baru (Penyewa / Pengelola)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/auth/register") }}',
                    auth: null,
                    description: 'Mendaftar akun penyewa atau pengelola. Pengelola baru otomatis berstatus pending sampai diverifikasi oleh admin (FR-01 & FR-03).',
                    body: JSON.stringify({
                        name: "Budi Satria",
                        email: "budi.satria" + Math.floor(Math.random() * 1000) + "@gmail.com",
                        phone: "08123456789",
                        password: "password123",
                        password_confirmation: "password123",
                        role: "penyewa"
                    }, null, 2)
                },
                {
                    title: '3. Profil Pengguna Sedang Login',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/auth/me") }}',
                    auth: 'Sanctum',
                    description: 'Mengambil data profil pengguna yang terotentikasi.',
                    body: ''
                },
                {
                    title: '4. Cari Venue Olahraga (Filter & Pencarian)',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/venues?city=Jakarta&sport=futsal") }}',
                    auth: null,
                    description: 'Mencari daftar venue berdasarkan cabang olahraga, kota, kata kunci, dan rentang harga.',
                    body: ''
                },
                {
                    title: '5. Detail Venue & Daftar Lapangan',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/venues/1") }}',
                    auth: null,
                    description: 'Melihat rincian venue, aturan persentase DP, kebijakan pembatalan, dan lapangan aktif.',
                    body: ''
                },
                {
                    title: '6. Ketersediaan Slot Real-Time Per Lapangan (FR-10)',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/courts/1/availability?date=" . date("Y-m-d")) }}',
                    auth: null,
                    description: 'Mengambil ketersediaan slot jam pada tanggal tertentu: available, held (15m), booked, blocked.',
                    body: ''
                },
                {
                    title: '7. Buat Booking Baru (Atomic Lock & Hold 15m) (FR-11, FR-12, FR-13)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/bookings") }}',
                    auth: 'Penyewa',
                    description: 'Membuat booking satu/lebih slot berurutan dengan multi-layer concurrency protection: Cache::lock, row lock (lockForUpdate), dan DB unique constraint.',
                    body: JSON.stringify({
                        court_id: 1,
                        slots: [
                            "{{ gmdate('Y-m-d\TH:00:00\Z', strtotime('+2 days 10:00')) }}",
                            "{{ gmdate('Y-m-d\TH:00:00\Z', strtotime('+2 days 11:00')) }}"
                        ],
                        notes: "Pertandingan persahabatan futsal"
                    }, null, 2)
                },
                {
                    title: '8. Generate Snap Token / Pay DP (FR-17)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/bookings/1/pay") }}',
                    auth: 'Penyewa',
                    description: 'Membuat transaksi Midtrans Snap sandbox dan mengembalikan snap_token serta redirect_url.',
                    body: ''
                },
                {
                    title: '9. Batalkan Booking (FR-15)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/bookings/1/cancel") }}',
                    auth: 'Penyewa',
                    description: 'Membatalkan booking dengan perhitungan snapshot kebijakan venue (refund DP jika > cancel_free_hours).',
                    body: JSON.stringify({
                        reason: "Perubahan jadwal kegiatan tim"
                    }, null, 2)
                },
                {
                    title: '10. Webhook Midtrans Gateway (FR-18 & FR-19)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/payments/webhook/midtrans") }}',
                    auth: 'Signature Gateway',
                    description: 'Callback resmi Midtrans. Memvalidasi SHA-512 signature key dan menjamin idempotensi duplikat request.',
                    body: JSON.stringify({
                        order_id: "PAY-BK-SEED-DP",
                        status_code: "200",
                        gross_amount: "54000.00",
                        signature_key: "SIMULATED_SIGNATURE",
                        transaction_status: "settlement"
                    }, null, 2)
                },
                {
                    title: '11. Simulasi Pembayaran Sandbox (Demo Helper)',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/payments/simulate") }}',
                    auth: null,
                    description: 'Endpoint pengujian sandbox instan yang menghasilkan payload webhook otentik dengan signature HMAC SHA-512 terenkripsi.',
                    body: JSON.stringify({
                        order_id: "PAY-BK-SEED-DP",
                        transaction_status: "settlement",
                        payment_type: "qris"
                    }, null, 2)
                },
                {
                    title: '12. Admin: Verifikasi Calon Pengelola (FR-03)',
                    method: 'PATCH',
                    endpoint: '{{ url("/api/v1/admin/users/3/verify") }}',
                    auth: 'Admin',
                    description: 'Admin menyetujui (active) atau menolak (suspended) pendaftaran pengelola venue.',
                    body: JSON.stringify({
                        status: "active"
                    }, null, 2)
                }
            ],

            get activeEndpoint() {
                return this.endpoints[this.activeIdx];
            },

            selectEndpoint(idx) {
                this.activeIdx = idx;
                this.responseStatus = null;
                this.responseText = '';
            },

            async fetchDemoToken(email) {
                try {
                    const res = await fetch("{{ url('/api/v1/auth/login') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "Accept": "application/json" },
                        body: JSON.stringify({ email: email, password: "password123" })
                    });
                    const data = await res.json();
                    if (data.data && data.data.token) {
                        this.bearerToken = data.data.token;
                        alert('Token untuk ' + data.data.user.name + ' (' + data.data.user.role + ') berhasil disuntikkan!');
                    } else {
                        alert(data.message || 'Gagal login demo.');
                    }
                } catch (e) {
                    alert('Gagal mengambil token demo: ' + e.message);
                }
            },

            async executeRequest() {
                this.isLoading = true;
                this.responseStatus = null;
                this.responseText = 'Mengirim request...';

                const startTime = performance.now();

                const headers = {
                    "Accept": "application/json"
                };

                if (this.bearerToken) {
                    headers["Authorization"] = "Bearer " + this.bearerToken;
                }

                const options = {
                    method: this.activeEndpoint.method,
                    headers: headers
                };

                if (['POST', 'PUT', 'PATCH'].includes(this.activeEndpoint.method)) {
                    headers["Content-Type"] = "application/json";
                    options.body = this.activeEndpoint.body;
                }

                try {
                    const res = await fetch(this.activeEndpoint.endpoint, options);
                    const endTime = performance.now();
                    this.responseTime = Math.round(endTime - startTime);
                    this.responseStatus = res.status;

                    const json = await res.json();
                    this.responseText = JSON.stringify(json, null, 2);
                } catch (e) {
                    const endTime = performance.now();
                    this.responseTime = Math.round(endTime - startTime);
                    this.responseStatus = 500;
                    this.responseText = JSON.stringify({ error: e.message }, null, 2);
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }
</script>
@endpush
@endsection
