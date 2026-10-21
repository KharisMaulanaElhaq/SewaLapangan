# Platform Web Service Booking Lapangan Olahraga

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Midtrans](https://img.shields.io/badge/Midtrans-Snap_Payment-002D62?style=for-the-badge)](https://midtrans.com)

Aplikasi Web Service & Platform Booking Lapangan Olahraga berbasis **RESTful API** dan Web UI modern. Dirancang sesuai spesifikasi PRD dengan arsitektur **Anti-Double Booking Berlapis**, integrasi pembayaran DP melalui **Midtrans Snap Payment Gateway**, dan dokumentasi API interaktif.

---

## 👥 Tim Pengembang (Kelompok 3)
**Mata Kuliah:** Pemrograman Web Service  
**Program Studi:** Teknik Informatika — Universitas Harkat Negeri (2026)  
**Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom

| No | Nama Mahasiswa | NIM | Fokus Area & Modul |
|:--:|:---|:---:|:---|
| 1 | **Muhamad Djafar Ilyasa** | 23215028 | Autentikasi Pengguna, Role Permission & User Management |
| 2 | **Moh. Arif Prasetyo** | 23215043 | Discovery Venue, Manajemen Lapangan, Dynamic Pricing & Availability Engine |
| 3 | **Lutfi Laeli Nur Afiyah** | 23215046 | Core Booking Engine, Concurrency Locking & Cancellation Policy |
| 4 | **Muh. Kharis Maulana Elhaq** | 23215059 | Payment Gateway Midtrans (Snap & Webhook), Security Signature & Refund |

---

## 🚀 Fitur Unggulan Arsitektur

### 1. Garansi 0 Double Booking (§9.2 PRD)
- **Database Unique Constraint**: Constraint unik `unique(court_id, slot_start)` pada tabel `booking_slots` sebagai benteng di level database engine (InnoDB).
- **Pessimistic Locking**: `DB::transaction` dengan `lockForUpdate()` saat pengecekan slot hingga komit data.
- **Distributed Atomic Lock**: `Cache::lock("court:{id}:slot:{ts}", 5)` untuk meredam lonjakan request bersamaan.
- **Auto-Expire Hold**: Slot otomatis di-hold selama 15 menit. Scheduler akan melepas slot secara berkala jika pembayaran DP belum diselesaikan.

### 2. Multi-Role User Management
- **Penyewa**: Registrasi langsung aktif, eksplorasi venue, cek slot real-time, booking dengan DP, bayar via Midtrans.
- **Pengelola**: Pendaftaran dengan status *pending approval*, manajemen profil venue, lapangan, harga dinamis (reguler/peak/weekend), dan blokir jadwal khusus (maintenance/event).
- **Admin**: Verifikasi pengelola venue baru, monitoring transaksi & performa platform.

### 3. Payment Gateway Midtrans Snap
- Mendukung metode pembayaran beragam: QRIS, Virtual Account (BCA, BNI, Mandiri, BRI), E-Wallet (GoPay, ShopeePay).
- Webhook asynchronous dengan validasi signature HMAC SHA-512.
- Endpoint sandbox simulation untuk kemudahan pengujian lokal.

---

## 🛠️ Persyaratan Sistem
- PHP >= 8.2 (dengan ekstensi: `pdo_mysql`, `curl`, `mbstring`, `openssl`)
- Composer >= 2.x
- MySQL / MariaDB >= 10.4
- Node.js >= 18.x & NPM

---

## ⚡ Panduan Instalasi Lokal

```bash
# 1. Clone repository
git clone https://github.com/KharisMaulanaElhaq/SewaLapangan.git
cd SewaLapangan

# 2. Install dependensi PHP
composer install

# 3. Buat file environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di file .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_lapangan
DB_USERNAME=root
DB_PASSWORD=

# 5. Jalankan migrasi dan seeder data awal
php artisan migrate:fresh --seed

# 6. Install dependensi frontend (opsional untuk asset compile)
npm install

# 7. Jalankan server lokal
php artisan serve
```

Aplikasi dapat diakses melalui browser:
- **Web UI & Home**: [http://localhost:8000](http://localhost:8000)
- **Base Endpoint API**: [http://localhost:8000/api/v1](http://localhost:8000/api/v1)

---

## 🔑 Akun Demo Pengujian

| Role | Email | Password | Keterangan |
|:---|:---|:---|:---|
| **Admin** | `admin@sportvenue.id` | `password` | Superadmin platform |
| **Pengelola** | `budi.champions@gmail.com` | `password` | Pengelola Champions Arena (Approved) |
| **Penyewa** | `djafar.penyewa@gmail.com` | `password` | Akun penyewa aktif |
| **Penyewa** | `arif.penyewa@gmail.com` | `password` | Akun penyewa aktif |

---

## 🧪 Menjalankan Automated Test

Sistem telah dilengkapi dengan test suite lengkap (Unit, Feature, Concurrency 50 parallel requests, dan Webhook Idempotency):

```bash
php artisan test
```

---

## 📄 Lisensi
Proyek ini dibuat untuk keperluan akademik dan praktikum mata kuliah Pemrograman Web Service di bawah lisensi [MIT](LICENSE).
