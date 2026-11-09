# Changelog

Semua perubahan penting pada proyek **SportVenue (Platform Web Service Booking Lapangan Olahraga)** didokumentasikan di berkas ini.

Format berkas ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), dan penomoran versi mengikuti prinsip [Semantic Versioning](https://semver.org/lang/id/).

---

## [1.1.0] - 2026-11-09

### ✨ Fitur Baru (Added)
- **Venue & Court Management Mitra Pengelola:**
  - Penambahan modul manajemen venue terpadu untuk mitra pengelola lapangan (`/manage/venues`).
  - Pembuatan data lapangan baru dengan fitur upload multi-foto lapangan disertai pratinjau (*live image preview*).
  - Fitur toggle status aktif/nonaktif lapangan secara instan melalui `PATCH /manage/courts/{id}` serta proteksi penghapusan `409 Conflict` jika lapangan memiliki transaksi atau jadwal aktif.
- **Ringkasan Pendapatan & Dashboard Pengelola:**
  - Penambahan kartu metrik ringkasan pendapatan Down Payment (DP) pada dashboard pengelola untuk memantau omzet harian dan bulanan secara terpusat.
- **E-Ticket & Cetak Invoice Pelanggan:**
  - Halaman invoice e-ticket resmi (`/bookings/{id}/invoice`) berformat *print-ready* yang memuat kode booking, detail venue, rincian biaya, jadwal slot sewa, dan status pembayaran.
- **Manajemen Profil Pengguna Web:**
  - Antarmuka manajemen profil akun untuk memperbarui nama lengkap, nomor telepon, dan informasi profil penyewa maupun pengelola.
- **Filter & Sorting Fleksibel pada Endpoint Booking & Admin:**
  - Filter rentang tanggal transaksi (`date_from`, `date_to`) pada riwayat booking.
  - Sorting whitelist dan pencarian dinamis berbasis query string pada endpoint `GET /admin/users` dan katalog lapangan.
- **Payment Security & Midtrans Snap Hardening:**
  - Standarisasi skema respons Midtrans Snap Token dan validasi status `409 Conflict` pada pembayaran yang kadaluarsa atau sudah diselesaikan.
  - Penegakan validasi ketat signature webhook HMAC SHA-512 dengan pesan audit trail error terperinci.

### 🎨 Tampilan & Pengalaman Pengguna (UI/UX Polish)
- **Sistem Desain Premium & Tokens Baru:**
  - Implementasi *color palette* modern, tipografi harmonis, dan micro-interaction berbasis Tailwind CSS.
  - Navigasi responsif dengan drawer menu seluler (*mobile navigation*) yang halus dan modern.
- **Penyempurnaan Halaman Publik:**
  - Desain ulang *Hero Section*, katalog venue unggulan, dan kartu informasi detail venue.
  - Redesain halaman autentikasi (Login & Registrasi) dengan validasi form yang lebih jelas dan ramah pengguna.
- **Penyempurnaan Alur Booking & Checkout:**
  - Perbaikan format waktu jam selesai slot (`end_time`) agar presisi dan tidak membingungkan pengguna.
  - Visualisasi ringkasan pesanan dan status pembayaran yang lebih intuitif.
- **Dokumentasi API Responsif:**
  - Redesain antarmuka dokumentasi API interaktif agar lebih cepat diakses di perangkat seluler dan mudah di-scan oleh developer.

### 🔧 Perbaikan & Keandalan Sistem (Fixed & Refined)
- **Spesifikasi Postman Collection v2.1:**
  - Perbaikan struktur canonical URL object Postman v2.1 beserta definisi *collection variables* untuk pengujian endpoint otomatis.
- **Penyelarasan Status Code RFC 9110:**
  - Memastikan seluruh endpoint mematuhi status code OpenAPI v1 (`200 OK`, `201 Created`, `204 No Content`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `409 Conflict`, `422 Unprocessable Content`).

---

## [1.0.0] - 2026-10-30

### Inisialisasi Fondasi Platform
- **Arsitektur Inti Garansi 0 Double Booking:**
  - Lapis 1: Cache Atomic Lock (`Cache::lock`) per slot 5 detik.
  - Lapis 2: Database Pessimistic Row Locking (`lockForUpdate`).
  - Lapis 3: Database Unique Composite Index (`unique(court_id, slot_start)`).
  - Lapis 4: Auto-expire booking hold 15 menit melalui background scheduler.
- **RESTful API v1 Core Endpoints:**
  - Autentikasi & Autorisasi (Sanctum Token, Multi-Role: Penyewa, Mitra Pengelola, Administrator).
  - Discovery venue, listing lapangan, pricing matrix, dan engine ketersediaan slot real-time.
  - Booking creation & Midtrans Snap payment gateway integration.
  - Webhook callback handler dengan validasi HMAC SHA-512 signature.
- **Dokumentasi API & Kontrak Desain:**
  - Dokumen REST API Design Document Minggu ke-4.
  - Spesifikasi kontrak OpenAPI 3.1.0 (`docs/openapi.yaml`) mencakup 31 operation endpoints.
  - Swagger UI & Redocly offline-ready documentation viewer.
