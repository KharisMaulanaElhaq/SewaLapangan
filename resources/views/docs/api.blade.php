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
                    Dokumentasi interaktif untuk seluruh REST API: ketersediaan slot real-time, penguncian atomik anti double-booking, manajemen venue, dan webhook pembayaran Midtrans.
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-400 font-mono">
                    <span class="rounded-lg bg-slate-800 px-3 py-1.5 border border-slate-700">Base URL: <strong class="text-emerald-400">{{ url('/api/v1') }}</strong></span>
                    <span class="rounded-lg bg-slate-800 px-3 py-1.5 border border-slate-700">Format: <strong class="text-slate-200">application/json</strong></span>
                    <span class="rounded-lg bg-slate-800 px-3 py-1.5 border border-slate-700">Auth: <strong class="text-slate-200">Laravel Sanctum (Bearer Token)</strong></span>
                </div>
            </div>

            <!-- Download Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('docs.postman') }}"
                    class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-600 hover:to-teal-600 transition flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Postman Collection</span>
                </a>
                <a href="{{ route('docs.openapi') }}" target="_blank"
                    class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-bold text-slate-200 hover:bg-slate-700 transition flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>OpenAPI Spec (YAML)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Token Generator & Tester Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Bearer Token Generator (Pengujian Cepat)</h3>
                <p class="text-xs text-slate-600 mt-0.5">Pilih akun demo untuk menginjeksi Sanctum Token secara instan ke API Tester di bawah:</p>
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
            <input type="text" x-model="bearerToken" placeholder="Token Sanctum Bearer (atau klik salah satu tombol di atas untuk login otomatis)..."
                class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono text-slate-800 focus:border-emerald-500 focus:outline-none">
            <button type="button" @click="bearerToken = ''" class="rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-500 hover:bg-slate-50">
                Clear
            </button>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <template x-for="cat in categories" :key="cat.id">
            <button type="button" @click="selectedCategory = cat.id"
                :class="selectedCategory === cat.id ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition flex items-center gap-1.5">
                <span x-text="cat.name"></span>
                <span class="rounded-full px-1.5 py-0.2 text-[10px]"
                    :class="selectedCategory === cat.id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
                    x-text="getCategoryCount(cat.id)"></span>
            </button>
        </template>
    </div>

    <!-- Endpoints List & Interactive Playground -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-4 space-y-3">
            <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
                <!-- Search Filter in Sidebar -->
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Cari endpoint atau URL..."
                        class="w-full rounded-xl border border-slate-200 pl-8 pr-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <svg class="absolute left-2.5 top-2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="space-y-1 text-xs max-h-[600px] overflow-y-auto pr-1">
                    <template x-for="(ep, idx) in filteredEndpoints" :key="idx">
                        <button type="button" @click="selectEndpoint(ep.originalIdx)"
                            :class="activeIdx === ep.originalIdx ? 'bg-emerald-50 text-emerald-900 border-emerald-300 font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                            class="w-full text-left rounded-xl px-3 py-2 border transition flex items-center justify-between gap-2">
                            <div class="truncate">
                                <span class="block truncate" x-text="ep.title"></span>
                                <span class="text-[10px] text-slate-400 font-mono block truncate" x-text="ep.endpoint.replace('{{ url('/api/v1') }}', '')"></span>
                            </div>
                            <span class="rounded px-1.5 py-0.5 text-[10px] font-mono font-bold uppercase flex-shrink-0"
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
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded px-2.5 py-1 text-xs font-mono font-bold uppercase"
                                :class="{
                                    'bg-emerald-100 text-emerald-800': activeEndpoint.method === 'GET',
                                    'bg-blue-100 text-blue-800': activeEndpoint.method === 'POST',
                                    'bg-amber-100 text-amber-800': activeEndpoint.method === 'PATCH' || activeEndpoint.method === 'PUT',
                                    'bg-rose-100 text-rose-800': activeEndpoint.method === 'DELETE',
                                }" x-text="activeEndpoint.method"></span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600" x-text="activeEndpoint.category"></span>
                            <span class="text-xs font-semibold text-slate-400 font-mono" x-text="activeEndpoint.auth ? '🔒 Perlu Autentikasi (' + activeEndpoint.auth + ')' : '🌐 Publik (Tanpa Token)'"></span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2" x-text="activeEndpoint.title"></h2>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1" x-text="activeEndpoint.description"></p>
                    </div>

                    <!-- URL Bar & Action Buttons -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 rounded-2xl bg-slate-900 p-3 text-white font-mono text-xs shadow-inner">
                            <span class="text-emerald-400 font-bold" x-text="activeEndpoint.method"></span>
                            <input type="text" x-model="activeEndpoint.endpoint" class="flex-1 bg-transparent text-slate-200 focus:outline-none">
                            <button type="button" @click="executeRequest()" :disabled="isLoading"
                                class="rounded-xl bg-emerald-600 px-4 py-2 font-sans font-bold text-white hover:bg-emerald-500 transition disabled:opacity-50 flex items-center gap-1 shadow-sm">
                                <span x-show="!isLoading">Send Request &rarr;</span>
                                <span x-show="isLoading">Loading...</span>
                            </button>
                        </div>

                        <div class="flex justify-end gap-2 text-xs">
                            <button type="button" @click="copyCurl()" class="text-slate-500 hover:text-slate-800 flex items-center gap-1 font-mono text-[11px]">
                                <span>📋 Copy cURL</span>
                            </button>
                        </div>
                    </div>

                    <!-- Request Body editor if POST / PUT / PATCH -->
                    <div x-show="['POST', 'PUT', 'PATCH'].includes(activeEndpoint.method)" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700">Request Body (application/json)</label>
                            <span class="text-[11px] text-slate-400">Bisa diedit langsung sebelum dikirim</span>
                        </div>
                        <textarea x-model="activeEndpoint.body" rows="7"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-950 p-4 font-mono text-xs text-emerald-300 focus:outline-none focus:border-emerald-500 shadow-inner"></textarea>
                    </div>

                    <!-- Response Viewer -->
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Response Output</span>
                            <div class="flex items-center gap-2 font-mono text-xs" x-show="responseStatus">
                                <span class="rounded px-2 py-0.5 font-bold"
                                    :class="responseStatus >= 200 && responseStatus < 300 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                    x-text="'HTTP ' + responseStatus"></span>
                                <span class="text-slate-400" x-text="responseTime + ' ms'"></span>
                            </div>
                        </div>

                        <pre class="max-h-96 overflow-y-auto rounded-2xl bg-slate-950 p-5 font-mono text-xs text-slate-200 shadow-inner selection:bg-emerald-600"
                            x-text="responseText || '// Klik \"Send Request\" untuk mengirim request langsung ke server...'"></pre>
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
            selectedCategory: 'all',
            searchQuery: '',

            categories: [
                { id: 'all', name: 'Semua Endpoint' },
                { id: 'auth', name: 'Autentikasi' },
                { id: 'discovery', name: 'Discovery & Publik' },
                { id: 'booking', name: 'Pemesanan (Penyewa)' },
                { id: 'manage', name: 'Pengelola Venue' },
                { id: 'payment', name: 'Pembayaran & Webhook' },
                { id: 'admin', name: 'Admin Platform' },
            ],

            endpoints: [
                // 1. AUTHENTICATION
                {
                    title: 'Login Pengguna (Ambil Token Sanctum)',
                    category: 'auth',
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
                    title: 'Registrasi Akun Baru (Penyewa / Pengelola)',
                    category: 'auth',
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
                    title: 'Profil Pengguna Sedang Login (Me)',
                    category: 'auth',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/auth/me") }}',
                    auth: 'Sanctum',
                    description: 'Mengambil data profil pengguna yang sedang terotentikasi.',
                    body: ''
                },
                {
                    title: 'Update Profil Pengguna (Me)',
                    category: 'auth',
                    method: 'PUT',
                    endpoint: '{{ url("/api/v1/auth/me") }}',
                    auth: 'Sanctum',
                    description: 'Memperbarui nama dan nomor telepon akun.',
                    body: JSON.stringify({
                        name: "Arif Prasetyo Updated",
                        phone: "081299998888"
                    }, null, 2)
                },
                {
                    title: 'Logout Pengguna',
                    category: 'auth',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/auth/logout") }}',
                    auth: 'Sanctum',
                    description: 'Menghapus token sesi Sanctum yang sedang aktif (204 No Content).',
                    body: ''
                },

                // 2. PUBLIC DISCOVERY
                {
                    title: 'Pencarian Venue Olahraga (Filter & Sort)',
                    category: 'discovery',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/venues?city=Jakarta&sport=futsal") }}',
                    auth: null,
                    description: 'Mencari daftar venue berdasarkan cabang olahraga, kota, kata kunci nama venue, dan rentang harga.',
                    body: ''
                },
                {
                    title: 'Detail Venue & Daftar Unit Lapangan',
                    category: 'discovery',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/venues/1") }}',
                    auth: null,
                    description: 'Melihat rincian venue, aturan persentase DP, kebijakan pembatalan, dan daftar lapangan aktif.',
                    body: ''
                },
                {
                    title: 'Ketersediaan Slot Real-Time (FR-10)',
                    category: 'discovery',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/courts/1/availability?date=" . date("Y-m-d")) }}',
                    auth: null,
                    description: 'Mengambil ketersediaan slot jam pada tanggal tertentu: available, held (15m), booked, blocked.',
                    body: ''
                },

                // 3. BOOKINGS (PENYEWA)
                {
                    title: 'Daftar Booking Saya (History)',
                    category: 'booking',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/bookings") }}',
                    auth: 'Penyewa',
                    description: 'Mengambil riwayat seluruh transaksi pemesanan milik pengguna yang sedang login.',
                    body: ''
                },
                {
                    title: 'Detail Booking & Tiket QR',
                    category: 'booking',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/bookings/1") }}',
                    auth: 'Penyewa',
                    description: 'Melihat rincian pemesanan, kode booking, rincian slot, status pembayaran DP, dan snapshot kebijakan venue.',
                    body: ''
                },
                {
                    title: 'Buat Booking Baru (Atomic Lock & Hold 15m)',
                    category: 'booking',
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
                    title: 'Generate Snap Token / Bayar DP Midtrans (FR-17)',
                    category: 'booking',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/bookings/1/pay") }}',
                    auth: 'Penyewa',
                    description: 'Membuat transaksi Midtrans Snap sandbox dan mengembalikan snap_token serta redirect_url.',
                    body: ''
                },
                {
                    title: 'Batalkan Booking (FR-15: Snapshot Refund)',
                    category: 'booking',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/bookings/1/cancel") }}',
                    auth: 'Penyewa',
                    description: 'Membatalkan booking dengan perhitungan snapshot kebijakan venue (refund DP jika > cancel_free_hours).',
                    body: JSON.stringify({
                        reason: "Perubahan jadwal kegiatan tim"
                    }, null, 2)
                },

                // 4. PENGELOLA VENUE & LAPANGAN
                {
                    title: 'Kelola: Daftar Venue Milik Pengelola (FR-04)',
                    category: 'manage',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/manage/venues") }}',
                    auth: 'Pengelola',
                    description: 'Menampilkan seluruh venue yang dimiliki oleh pengelola yang sedang login.',
                    body: ''
                },
                {
                    title: 'Kelola: Tambah Venue Baru (FR-04)',
                    category: 'manage',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/manage/venues") }}',
                    auth: 'Pengelola',
                    description: 'Mendaftarkan venue baru beserta nomor kontak, persentase DP, dan aturan pembatalan.',
                    body: JSON.stringify({
                        name: "Champion Arena Sport Hub",
                        city: "Jakarta Selatan",
                        address: "Jl. TB Simatupang No. 88",
                        phone: "081234567890",
                        dp_percent: 30,
                        cancel_free_hours: 24,
                        cancel_refund_percent: 100,
                        description: "Arena olahraga terlengkap dengan lantai vinyl dan karpet BWF"
                    }, null, 2)
                },
                {
                    title: 'Kelola: Update Data Venue (FR-04)',
                    category: 'manage',
                    method: 'PUT',
                    endpoint: '{{ url("/api/v1/manage/venues/1") }}',
                    auth: 'Pengelola',
                    description: 'Memperbarui profil venue, persentase DP, atau batas waktu refund pembatalan.',
                    body: JSON.stringify({
                        name: "Champions Sport Arena Jakarta (Updated)",
                        city: "Jakarta Selatan",
                        address: "Jl. TB Simatupang No. 88, Cilandak",
                        phone: "021-7890123",
                        dp_percent: 35,
                        cancel_free_hours: 24,
                        cancel_refund_percent: 100
                    }, null, 2)
                },
                {
                    title: 'Kelola: Tambah Lapangan Baru (FR-05)',
                    category: 'manage',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/manage/courts") }}',
                    auth: 'Pengelola',
                    description: 'Menambahkan unit lapangan baru ke venue beserta jam operasional dan harga default.',
                    body: JSON.stringify({
                        venue_id: 1,
                        name: "Lapangan Badminton Karpet 3",
                        sport_type: "badminton",
                        is_active: true,
                        open_time: "08:00",
                        close_time: "23:00",
                        price: 75000
                    }, null, 2)
                },
                {
                    title: 'Kelola: Update Lapangan & Status Aktif (FR-05)',
                    category: 'manage',
                    method: 'PUT',
                    endpoint: '{{ url("/api/v1/manage/courts/1") }}',
                    auth: 'Pengelola',
                    description: 'Mengubah nama lapangan, cabang olahraga, atau mengaktifkan/menonaktifkan lapangan.',
                    body: JSON.stringify({
                        name: "Lapangan Futsal A (Vinyl Pro)",
                        sport_type: "futsal",
                        is_active: true
                    }, null, 2)
                },
                {
                    title: 'Kelola: Tambah Aturan Harga Tier (FR-06)',
                    category: 'manage',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/manage/prices") }}',
                    auth: 'Pengelola',
                    description: 'Menetapkan harga khusus weekday, weekend, atau jam sibuk (prime time).',
                    body: JSON.stringify({
                        court_id: 1,
                        day_type: "weekend",
                        start_time: "17:00",
                        end_time: "23:00",
                        price: 180000
                    }, null, 2)
                },
                {
                    title: 'Kelola: Blokir Jadwal Lapangan / Maintenance (FR-08)',
                    category: 'manage',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/manage/blocks") }}',
                    auth: 'Pengelola',
                    description: 'Memblokir rentang jam lapangan untuk perbaikan atau event internal agar tidak dapat dipesan pengguna.',
                    body: JSON.stringify({
                        court_id: 1,
                        start_at: "{{ date('Y-m-d 10:00:00', strtotime('+1 day')) }}",
                        end_at: "{{ date('Y-m-d 14:00:00', strtotime('+1 day')) }}",
                        reason: "Perawatan rutin rumput sintetis"
                    }, null, 2)
                },
                {
                    title: 'Kelola: Ubah Status Booking (COMPLETED / NO_SHOW) (FR-16)',
                    category: 'manage',
                    method: 'PATCH',
                    endpoint: '{{ url("/api/v1/manage/bookings/1/status") }}',
                    auth: 'Pengelola',
                    description: 'Pengelola menandai penyewa telah selesai bermain (COMPLETED) atau tidak hadir (NO_SHOW).',
                    body: JSON.stringify({
                        status: "COMPLETED"
                    }, null, 2)
                },

                // 5. PAYMENT & WEBHOOK
                {
                    title: 'Midtrans Webhook Callback (FR-18 & FR-19)',
                    category: 'payment',
                    method: 'POST',
                    endpoint: '{{ url("/api/v1/payments/webhook/midtrans") }}',
                    auth: 'Signature Gateway',
                    description: 'Endpoint callback resmi Midtrans. Memvalidasi SHA-512 signature key dan menjamin idempotensi duplikat webhook.',
                    body: JSON.stringify({
                        order_id: "PAY-BK-SEED-DP",
                        status_code: "200",
                        gross_amount: "54000.00",
                        signature_key: "SIMULATED_SIGNATURE",
                        transaction_status: "settlement"
                    }, null, 2)
                },
                {
                    title: 'Simulasi Pembayaran Sandbox (Demo Helper)',
                    category: 'payment',
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

                // 6. ADMIN PLATFORM
                {
                    title: 'Admin: Daftar Pengguna & Filter Status',
                    category: 'admin',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/admin/users?role=pengelola") }}',
                    auth: 'Admin',
                    description: 'Admin melihat daftar pengguna, memfilter berdasarkan role penyewa/pengelola dan status pending/active.',
                    body: ''
                },
                {
                    title: 'Admin: Verifikasi Calon Pengelola (FR-03)',
                    category: 'admin',
                    method: 'PATCH',
                    endpoint: '{{ url("/api/v1/admin/users/3/verify") }}',
                    auth: 'Admin',
                    description: 'Admin menyetujui (status: active) atau menolak/menangguhkan (status: suspended) pendaftaran pengelola venue.',
                    body: JSON.stringify({
                        status: "active"
                    }, null, 2)
                },
                {
                    title: 'Admin: Rekap Transaksi Pembayaran',
                    category: 'admin',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/admin/transactions") }}',
                    auth: 'Admin',
                    description: 'Admin melihat ringkasan keuangan dan riwayat transaksi pembayaran DP dari seluruh venue.',
                    body: ''
                },
                {
                    title: 'Admin: Audit Log Webhook Gateway',
                    category: 'admin',
                    method: 'GET',
                    endpoint: '{{ url("/api/v1/admin/webhook-logs") }}',
                    auth: 'Admin',
                    description: 'Melihat riwayat log audit webhook pembayaran dari Midtrans (status validitas signature & payload).',
                    body: ''
                }
            ],

            get activeEndpoint() {
                return this.endpoints[this.activeIdx];
            },

            get filteredEndpoints() {
                return this.endpoints
                    .map((ep, idx) => ({ ...ep, originalIdx: idx }))
                    .filter(ep => {
                        const matchCat = this.selectedCategory === 'all' || ep.category === this.selectedCategory;
                        const matchSearch = !this.searchQuery || 
                            ep.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                            ep.endpoint.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            ep.method.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchCat && matchSearch;
                    });
            },

            getCategoryCount(catId) {
                if (catId === 'all') return this.endpoints.length;
                return this.endpoints.filter(e => e.category === catId).length;
            },

            selectEndpoint(idx) {
                this.activeIdx = idx;
                this.responseStatus = null;
                this.responseText = '';
            },

            copyCurl() {
                let cmd = `curl -X ${this.activeEndpoint.method} "${this.activeEndpoint.endpoint}" \\\n  -H "Accept: application/json"`;
                if (this.bearerToken) {
                    cmd += ` \\\n  -H "Authorization: Bearer ${this.bearerToken}"`;
                }
                if (['POST', 'PUT', 'PATCH'].includes(this.activeEndpoint.method) && this.activeEndpoint.body) {
                    cmd += ` \\\n  -H "Content-Type: application/json" \\\n  -d '${this.activeEndpoint.body.replace(/'/g, "\\'")}'`;
                }
                navigator.clipboard.writeText(cmd);
                alert('Perintah cURL berhasil disalin ke clipboard!');
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
                this.responseText = 'Mengirim request ke server...';

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

                    const contentType = res.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        const json = await res.json();
                        this.responseText = JSON.stringify(json, null, 2);
                    } else {
                        this.responseText = await res.text();
                    }
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
