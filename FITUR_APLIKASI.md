# 📋 Dokumentasi Fitur Aplikasi Web Service Booking Lapangan

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Midtrans](https://img.shields.io/badge/Midtrans-Snap_Payment-002D62?style=for-the-badge)](https://midtrans.com)

Dokumen ini memuat daftar lengkap fitur fungsional, arsitektur teknis, dan kapabilitas sistem yang telah diimplementasikan pada aplikasi **Platform Web Service Booking Lapangan Olahraga (SportVenue)** sesuai spesifikasi PRD (*Product Requirements Document*).

---

## 👥 Informasi Proyek & Tim Pengembang
- **Mata Kuliah:** Pemrograman Web Service
- **Kelompok:** Kelompok 3 — Teknik Informatika, Universitas Harkat Negeri (2026)
- **Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom

| No | Nama Mahasiswa | NIM | Tanggung Jawab & Modul Utama |
|:--:|:---|:---:|:---|
| 1 | **Muhamad Djafar Ilyasa** | 23215028 | Autentikasi Pengguna, Hak Akses Multi-Role & User Management |
| 2 | **Moh. Arif Prasetyo** | 23215043 | Discovery Venue, Manajemen Lapangan, Dynamic Pricing & Availability Engine |
| 3 | **Lutfi Laeli Nur Afiyah** | 23215046 | Core Booking Engine, Concurrency Locking & Kebijakan Pembatalan |
| 4 | **Muh. Kharis Maulana Elhaq** | 23215059 | Payment Gateway Midtrans (Snap & Webhook), Security Signature & Refund |

---

## 🛡️ 1. Arsitektur Inti: Garansi 0 Double Booking (§9.2 PRD)
Sistem dirancang dengan pertahanan berlapis untuk menjamin bahwa **tepat 1 request berhasil** saat terjadi lonjakan ≥ 50 request konkuren pada slot lapangan dan jam yang sama.

| Lapis Pertahanan | Mekanisme Teknis | Implementasi di Kode |
|:---|:---|:---|
| **Lapis 1: Distributed Lock** | Atomic cache locking per slot selama 5 detik untuk meredam kontensi paralel. | `Cache::lock("court:{$courtId}:slot:{$ts}", 5)` |
| **Lapis 2: DB Transaction & Lock** | Transaksi database dengan *pessimistic row locking* saat pengecekan ketersediaan hingga penulisan. | `DB::transaction()` & `lockForUpdate()` pada entitas lapangan |
| **Lapis 3: Database Unique Constraint** | Constraint unik tingkat database engine (InnoDB) sebagai benteng terakhir yang tidak dapat ditembus. | `table->unique(['court_id', 'slot_start'])` pada tabel `booking_slots` |
| **Lapis 4: Auto-Expire Hold 15 Menit** | Slot yang dipesan otomatis dikunci selama 15 menit. Jika DP tidak dibayar dalam batas waktu, slot otomatis dilepas kembali ke publik. | Scheduled Command: `php artisan bookings:expire` |

---

## 👤 2. Fitur Berdasarkan Role Pengguna

### A. Fitur Penyewa (Customer / Pengguna Umum)
1. **Registrasi Akun Instan:** Mendaftar akun penyewa dengan email, nomor telepon, dan password; akun langsung aktif dan dapat digunakan.
2. **Autentikasi Aman:** Mendukung login sesi web maupun penerbitan Sanctum Bearer Token untuk integrasi REST API.
3. **Pencarian & Filter Venue:**
   - Pencarian berdasarkan nama venue atau alamat.
   - Filter lokasi kota (contoh: Jakarta Selatan, Tangerang).
   - Filter cabang olahraga (Futsal, Badminton, Basketball, Mini Soccer).
4. **Matriks Ketersediaan Slot Real-Time:**
   - Menampilkan slot jam (08:00–23:00) per tanggal tertentu secara langsung.
   - Indikator status slot visual: *Tersedia (Available)*, *Sedang di-Hold*, *Terisi (Booked)*, dan *Diblokir/Maintenance*.
5. **Kalkulasi Tarif Sewa Dinamis:**
   - Tarif otomatis menyesuaikan jam reguler siang, jam malam (*peak hour*), dan akhir pekan (*weekend*).
6. **Multi-Slot Booking:** Penyewa dapat memilih beberapa slot jam sekaligus dalam satu transaksi pemesanan.
7. **Perhitungan DP Otomatis:** Sistem menghitung persentase uang muka (DP 30%) secara otomatis dari total tagihan.
8. **Integrasi Pembayaran Midtrans Snap:**
   - Pop-up transaksi digital langsung di halaman checkout: QRIS (GoPay, ShopeePay), Virtual Account (BCA, BNI, BRI, Mandiri).
9. **Riwayat Pemesanan (My Bookings):**
   - Halaman daftar booking lengkap dengan status: `PENDING_PAYMENT`, `CONFIRMED`, `CANCELLED`, `EXPIRED`, `COMPLETED`.
10. **Pembatalan Mandiri & Refund Otomatis:**
    - Penyewa dapat membatalkan booking sebelum batas waktu (misal H-24 atau H-12 jam sebelum jadwal).
    - Menghitung persentase refund uang muka sesuai snapshot kebijakan venue saat transaksi dibuat.

---

### B. Fitur Pengelola Venue (Mitra / Partner)
1. **Registrasi Mitra:** Pendaftaran akun pengelola dengan status awal `pending approval` demi verifikasi keamanan platform.
2. **Dashboard Khusus Pengelola:** Antarmuka khusus untuk mengontrol operasional seluruh lapangan di venue miliknya.
3. **Pengelolaan Profil Venue:**
   - Mengubah nama venue, deskripsi, alamat lengkap, kota, dan nomor kontak.
   - Mengatur foto banner venue melalui URL gambar.
   - Menetapkan persentase uang muka (DP) di muka.
   - Menetapkan aturan pembatalan (*cancel free hours* dan persentase refund).
4. **Manajemen Lapangan (Courts CRUD):**
   - Menambah lapangan baru dengan tipe cabang olahraga (Futsal, Badminton, Basketball, Mini Soccer, Tennis, Volleyball).
   - Mengubah nama lapangan dan status aktif/non-aktif lapangan.
   - Menghapus lapangan yang sudah tidak beroperasi.
5. **Manajemen Aturan Harga Dinamis (Court Prices):**
   - Menambahkan rentang tarif berbeda untuk `weekday` (hari kerja), `weekend` (akhir pekan), atau `all`.
   - Menyesuaikan tarif jam reguler (misal 08:00–17:00) dan jam sibuk/malam (misal 17:00–23:00).
   - Menghapus aturan harga yang sudah tidak berlaku.
6. **Jadwal Blokir Lapangan (Schedule Blocks):**
   - Memblokir jam tertentu agar tidak bisa dipesan umum untuk keperluan: perawatan rutin rumput sintetis, perbaikan lampu, atau turnamen internal.
   - Menghapus jadwal blokir jika perawatan telah selesai lebih cepat.
7. **Pembaruan Status Operasional Booking:**
   - Mengubah status reservasi lapangan yang telah berjalan menjadi `COMPLETED` (selesai main) atau `NO_SHOW` (penyewa tidak hadir).

---

### C. Fitur Administrator Platform
1. **Dashboard Super Administrator:** Ringkasan metrik performa platform secara menyeluruh.
2. **Verifikasi Mitra Pengelola Baru:**
   - Meninjau pengelola yang baru mendaftar (status `pending`).
   - Melakukan persetujuan (*Approve*) atau penolakan akun mitra.
3. **Pemantauan Seluruh Transaksi:** Melihat seluruh transaksi sewa lapangan yang terjadi lintas venue di platform.
4. **Monitoring Audit Log:**
   - Log webhook transaksi pembayaran dari payment gateway.
   - Log notifikasi pengingat pembayaran dan konfirmasi slot.

---

## 💳 3. Integrasi Payment Gateway Midtrans
1. **Snap Token Generator:** Backend menginisialisasi transaksi sandbox ke Midtrans API dan menghasilkan Snap Token unik beserta redirect URL.
2. **Asynchronous Webhook Callback:** Endpoint penerima notifikasi HTTP POST saat transaksi berhasil dibayar oleh pengguna.
3. **Validasi Signature HMAC SHA-512:**
   - Memvalidasi keaslian signature key (`order_id + status_code + gross_amount + server_key`) untuk mencegah pemalsuan pembayaran.
4. **Idempotency Webhook:** Sistem aman dari pengiriman webhook berulang (*duplicate callback*) tanpa menduplikasi status atau nominal.
5. **Sandbox Simulator Endpoint:** Endpoint pengujian lokal (`POST /api/v1/payments/simulate`) untuk mensimulasikan pembayaran tanpa memerlukan dashboard eksternal.

---

## 🔌 4. Daftar Endpoint RESTful API v1 (`/api/v1`)

| Method | Endpoint (URI) | Hak Akses | Deskripsi Fungsi |
|:---:|:---|:---:|:---|
| `POST` | `/api/v1/auth/register` | Publik | Registrasi akun baru (penyewa langsung aktif, pengelola pending approval) |
| `POST` | `/api/v1/auth/login` | Publik | Login pengguna & penerbitan Sanctum Bearer Token |
| `GET` | `/api/v1/auth/me` | Bearer Token | Mendapatkan data profil pengguna yang sedang login |
| `POST` | `/api/v1/auth/logout` | Bearer Token | Mencabut token autentikasi aktif (logout API) |
| `GET / POST` | `/api/v1/venues` | Publik | Pencarian & filter venue (mendukung query param maupun JSON Body) |
| `GET` | `/api/v1/venues/{id}` | Publik | Mengambil detail satu venue beserta daftar lapangan aktif |
| `GET / POST` | `/api/v1/courts/{id}/availability` | Publik | Cek ketersediaan slot jam & tarif dinamis per tanggal |
| `GET` | `/api/v1/bookings` | Penyewa | Menampilkan seluruh riwayat booking milik pengguna |
| `GET` | `/api/v1/bookings/{id}` | Penyewa/Mitra | Melihat rincian detail satu transaksi booking |
| `POST` | `/api/v1/bookings` | Penyewa | Membuat booking baru dengan kunci atomik & hold 15 menit |
| `POST` | `/api/v1/bookings/{id}/pay` | Penyewa | Mengambil Snap Token Midtrans untuk pembayaran DP |
| `POST` | `/api/v1/bookings/{id}/cancel` | Penyewa | Membatalkan booking aktif & kalkulasi otomatis refund DP |
| `POST` | `/api/v1/payments/webhook/midtrans` | Midtrans | Callback notifikasi settlement pembayaran (validasi SHA-512) |
| `POST` | `/api/v1/payments/simulate` | Publik/Dev | Simulasi webhook settlement sandbox untuk pengujian cepat |
| `POST` | `/api/v1/manage/courts` | Pengelola/Admin | Menambahkan lapangan baru ke dalam venue |
| `PUT` | `/api/v1/manage/courts/{id}` | Pengelola/Admin | Mengubah data informasi atau status aktif lapangan |
| `DELETE` | `/api/v1/manage/courts/{id}` | Pengelola/Admin | Menghapus data lapangan dari venue |
| `POST` | `/api/v1/manage/prices` | Pengelola/Admin | Menambahkan konfigurasi aturan tarif sewa dinamis |
| `DELETE` | `/api/v1/manage/prices/{id}` | Pengelola/Admin | Menghapus aturan tarif sewa |
| `POST` | `/api/v1/manage/blocks` | Pengelola/Admin | Membuat jadwal pemblokiran slot maintenance/event |
| `DELETE` | `/api/v1/manage/blocks/{id}` | Pengelola/Admin | Menghapus jadwal blokir lapangan |
| `PATCH` | `/api/v1/manage/bookings/{id}/status` | Pengelola/Admin | Memperbarui status booking operasional (COMPLETED/NO_SHOW) |
| `GET` | `/api/v1/admin/users` | Admin | Menampilkan daftar seluruh pengguna platform |
| `PATCH` | `/api/v1/admin/users/{id}/verify` | Admin | Verifikasi dan aktivasi akun pengelola baru |
| `GET` | `/api/v1/admin/transactions` | Admin | Menampilkan rekap seluruh transaksi platform |
| `GET` | `/api/v1/admin/webhook-logs` | Admin | Memantau log riwayat callback webhook payment gateway |

---

## 🧪 5. Hasil Pengujian Otomatis (Automated Test Suite)
Sistem dilengkapi dengan test suite lengkap berbasis PHPUnit / Pest dengan **15/15 Pengujian Lolos (100% Pass, 49 Assertions)**:

```bash
php artisan test
```

- `AuthAndRoleTest`:
  - ✅ Registrasi penyewa langsung berstatus active.
  - ✅ Registrasi pengelola venue berstatus pending approval.
  - ✅ Super Administrator dapat memverifikasi pengelola yang pending.
- `BookingServiceTest`:
  - ✅ Mengambil ketersediaan slot jam harian secara akurat.
  - ✅ Kunci atomik slot 15 menit dan kalkulasi DP 30%.
  - ✅ Pencegahan double booking menolak pemesanan kedua pada slot yang sama.
  - ✅ Pelepasan slot otomatis saat booking kadaluarsa (*expired*).
  - ✅ Perhitungan refund pembatalan sesuai snapshot kebijakan venue.
- `ConcurrencyTest`:
  - ✅ **Pengujian 50 Request Paralel Simultan**: Menghasilkan tepat 1 request berhasil dan 49 request ditolak dengan status `409 Conflict`.
- `PaymentWebhookTest`:
  - ✅ Konfirmasi booking otomatis saat signature SHA-512 valid.
  - ✅ Penolakan signature palsu dengan `401 Unauthorized`.
  - ✅ Idempotensi webhook aman dari duplikasi callback berulang.
  - ✅ Endpoint simulasi sandbox berfungsi sempurna.

---

## 💻 6. Antarmuka Web (UI/UX)
- Dibangun dengan arsitektur modern **Tailwind CSS** dan **Laravel Livewire 3**.
- Desain bersih dan responsif di perangkat desktop, tablet, maupun smartphone.
- Menggunakan skema warna profesional (*Emerald & Dark Slate*).
- Form pemesanan interaktif dengan umpan balik pesan (*flash messages*) dan penanganan error validasi instan.
