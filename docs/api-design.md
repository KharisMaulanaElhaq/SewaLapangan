# API Design Document v1 — SportVenue
**Milestone M2: API Blueprint**  
**Mata Kuliah:** Pemrograman Web Service — Pertemuan Minggu ke-4  
**Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.  
**Institusi:** Teknik Informatika, Universitas Harkat Negeri (2026/2027)

---

## 👥 Identitas Tim Pengembang (Kelompok 3)
| No | Nama Mahasiswa | NIM | Peran / Fokus Jobdesk |
|:--:|:---|:---:|:---|
| 1 | **Moh. Arif Prasetyo** | 23215043 | Discovery Venue & Availability Engine |
| 2 | **Muhamad Djafar Ilyasa** | 23215028 | Authentication, Authorization & User Management |
| 3 | **Lutfi Laeli Nur Afiyah** | 23215046 | Core Booking Engine, Concurrency Locking & Policies |
| 4 | **Muh. Kharis Maulana Elhaq** | 23215059 | Payment Gateway Integration (Midtrans Snap & Webhook) |

- **Nama Project:** SportVenue (Platform Web Service Booking Lapangan Olahraga Online)
- **Base URL:** `http://localhost:8000/api/v1`
- **Versi API:** `v1` (URI path-based versioning)
- **Bahasa Penamaan:** Bahasa Inggris (kata benda jamak, huruf kecil, *kebab-case* selaras konvensi RESTful & Laravel `Route::apiResource`)
- **Format Pertukaran Data:** JSON (`Content-Type: application/json`, `Accept: application/json`)

---

## 20.1 Aktivitas 1 – Pemodelan Resource (15 menit)
Resource diturunkan secara presisi dari kebutuhan fungsional dan kata benda (*nouns*) pada alur bisnis SportVenue: cari lapangan → cek slot → booking → bayar DP → konfirmasi / batalkan sewa.

Sesuai prinsip REST (*Modul Ajar Point 9.3*), model resource adalah representasi yang dilihat dan diakses oleh client melalui URI, sehingga tidak harus identik 1-banding-1 dengan nama tabel database fisik.

### A. Tabel Pemodelan Resource Lengkap
| No | Resource URI (Jamak) | Tabel Database | Atribut Utama | Relasi Antar Resource | Jenis Resource | Diekspos API |
|:--:|:---|:---|:---|:---|:---|:---:|
| 1 | `users` | `users` | `id, name, email, phone, role, status, email_verified_at, created_at` | `1 : N` ke `venues` (pengelola)<br/>`1 : N` ke `bookings` (penyewa) | Collection | Ya |
| 2 | `venues` | `venues` | `id, owner_id, name, slug, address, city, phone, dp_percent, cancel_free_hours, cancel_refund_percent, is_active` | `N : 1` ke `users` (owner)<br/>`1 : N` ke `courts` | Collection | Ya |
| 3 | `courts` | `courts` | `id, venue_id, name, sport_type, is_active, created_at` | `N : 1` ke `venues`<br/>`1 : N` ke `prices`, `operating-hours`, `blocks`, `bookings` | Sub-resource / Collection | Ya |
| 4 | `prices` | `court_prices` | `id, court_id, day_type, start_time, end_time, price` | `N : 1` ke `courts` | Sub-resource | Ya |
| 5 | `operating-hours` | `operating_hours` | `id, court_id, day_of_week, open_time, close_time, slot_duration_minutes` | `N : 1` ke `courts` | Sub-resource | Ya |
| 6 | `blocks` | `schedule_blocks` | `id, court_id, start_at, end_at, reason, created_at` | `N : 1` ke `courts` | Sub-resource | Ya |
| 7 | `availability` | *(Kalkulasi Dinamis)* | `court_id, play_date, slots [slot_time, status, price]` | Turunan dari `courts` (kalkulasi jam operasional, blokir, dan slot terisi) | Sub-resource (Virtual / Read-Only) | Ya (GET) |
| 8 | `bookings` | `bookings` | `id, code, user_id, court_id, total_price, dp_amount, status, expires_at, cancelled_at, refund_amount` | `N : 1` ke `users`<br/>`N : 1` ke `courts`<br/>`1 : N` ke `booking_slots`, `1 : N` ke `payments` | Collection | Ya |
| 9 | `payments` | `payments` | `id, booking_id, order_id, type, amount, status, payment_type, snap_token, snap_redirect_url, paid_at` | `N : 1` ke `bookings` | Sub-resource / Collection | Ya |
| 10 | `booking_slots` | `booking_slots` | `id, booking_id, court_id, slot_start, slot_end, price` | `N : 1` ke `bookings`<br/>`unique(court_id, slot_start)` | Internal Engine (Anti Double Booking) | Tidak (Internal) |
| 11 | `me` | `users` | `id, name, email, phone, role, status` | N/A (merujuk ke subjek token terautentikasi) | Singleton | Ya |
| 12 | `webhook-logs` | `webhook_logs` | `id, provider, order_id, payload, signature_valid, status, processed_at` | N/A (audit trail callback Midtrans) | Collection (Admin) | Ya (GET) |

### B. Diagram Relasi Antar-Resource (ERD Ringkas)
```
+-------------+ 1       N +-------------+ 1       N +-------------+ 1       N +-----------------+
|    users    | --------> |   venues    | --------> |   courts    | --------> | prices (prices) |
+-------------+           +-------------+           +-------------+           +-----------------+
       | 1                                                 | 1       1      N +-----------------+
       |                                                   | --------+------> | operating-hours |
       |                                                   | 1       |        +-----------------+
       |                                                   | --------+------> | blocks (blocks) |
       |                                                   |                  +-----------------+
       v N                                                 v N
+-------------+ 1                               N +-----------------+
|  bookings   | --------------------------------> |  booking_slots  | (Unique Index: anti collision)
+-------------+                                   +-----------------+
       | 1
       v N
+-------------+
|  payments   | (Midtrans Snap Token & Webhook Callback Idempotent)
+-------------+
```

---

## 20.2 Aktivitas 2 – Endpoint Matrix (Base URL: `/api/v1`)
Disusun 26 endpoint RESTful yang mencakup operasi CRUD pada seluruh resource platform. Setiap baris memuat HTTP method semantik, URI terstandar, fungsi operasi, aktor pengguna, status sukses, dan status gagal yang relevan:

| No | Method | Endpoint (URI) | Resource Terkait | Fungsi & Deskripsi Operasi | Aktor Akses | Status Sukses | Status Gagal Umum |
|:--:|:---:|:---|:---|:---|:---:|:---:|:---|
| 1 | `POST` | `/auth/register` | `users` | Registrasi akun pengguna baru (penyewa langsung aktif, pengelola pending verifikasi) | Publik | `201 Created` | `400, 422` |
| 2 | `POST` | `/auth/login` | `users` | Autentikasi kredensial pengguna dan menerbitkan Sanctum Bearer Token | Publik | `200 OK` | `401, 422` |
| 3 | `POST` | `/auth/logout` | `users` | Mencabut token autentikasi aktif pengguna | Login | `204 No Content` | `401` |
| 4 | `GET` | `/me` | `me` (Singleton) | Mengambil profil data diri pengguna yang sedang login | Login | `200 OK` | `401` |
| 5 | `GET` | `/venues` | `venues` | Menampilkan katalog venue dengan filter kota, cabang olahraga, search, pagination | Publik | `200 OK` | `400, 422` |
| 6 | `GET` | `/venues/{id}` | `venues` | Mengambil informasi detail satu venue beserta fasilitas lapangannya | Publik | `200 OK` | `404` |
| 7 | `GET` | `/venues/{id}/courts` | `courts` | Menampilkan seluruh lapangan yang tersedia di dalam venue tertentu | Publik | `200 OK` | `404` |
| 8 | `GET` | `/courts/{id}` | `courts` | Mengambil detail spesifikasi satu lapangan (tipe lantai, ukuran, status aktif) | Publik | `200 OK` | `404` |
| 9 | `GET` | `/courts/{id}/prices` | `prices` | Menampilkan daftar struktur tarif sewa reguler, peak hour, dan weekend | Publik | `200 OK` | `404` |
| 10 | `GET` | `/courts/{id}/operating-hours` | `operating-hours` | Menampilkan jam buka-tutup operasional lapangan dalam seminggu | Publik | `200 OK` | `404` |
| 11 | `GET` | `/courts/{id}/availability` | `availability` | Matriks status ketersediaan slot jam (08:00–23:00) dan tarif per tanggal | Publik | `200 OK` | `404, 422` |
| 12 | `POST` | `/bookings` | `bookings` | Membuat reservasi booking baru, mengunci slot atomik, hold DP 15 menit | Penyewa | `201 Created` | `400, 409, 422` |
| 13 | `GET` | `/bookings` | `bookings` | Menampilkan seluruh riwayat transaksi booking milik penyewa aktif | Penyewa | `200 OK` | `401` |
| 14 | `GET` | `/bookings/{id}` | `bookings` | Mengambil detail faktur booking, slot yang diambil, dan rincian bayar | Penyewa, Pengelola | `200 OK` | `401, 403, 404` |
| 15 | `POST` | `/bookings/{id}/payments` | `payments` | Menginisialisasi token transaksi Midtrans Snap dan redirect payment URL | Penyewa | `201 Created` | `401, 403, 409` |
| 16 | `POST` | `/bookings/{id}/cancellation` | `bookings` | Membatalkan reservasi aktif dan memproses refund otomatis kebijakan venue | Penyewa | `200 OK` | `401, 403, 409` |
| 17 | `POST` | `/payments/webhook/midtrans` | `payments` | Endpoint callback notifikasi pembayaran gateway (validasi HMAC SHA-512) | Midtrans | `200 OK` | `401, 403` |
| 18 | `POST` | `/manage/venues` | `venues` | Mendaftarkan lokasi venue baru milik mitra pengelola | Pengelola | `201 Created` | `401, 403, 422` |
| 19 | `PUT` | `/manage/venues/{id}` | `venues` | Memperbarui data venue (alamat, persentase DP, kebijakan pembatalan) | Pengelola | `200 OK` | `401, 403, 404, 422` |
| 20 | `DELETE` | `/manage/venues/{id}` | `venues` | Menonaktifkan/menghapus lokasi venue dari sistem | Pengelola | `204 No Content` | `401, 403, 404, 409` |
| 21 | `POST` | `/manage/courts` | `courts` | Menambahkan unit lapangan baru pada venue mitra | Pengelola | `201 Created` | `401, 403, 422` |
| 22 | `PUT` | `/manage/courts/{id}` | `courts` | Memperbarui spesifikasi lapangan secara menyeluruh | Pengelola | `200 OK` | `401, 403, 404, 422` |
| 23 | `PATCH` | `/manage/courts/{id}` | `courts` | Mengubah status aktif/non-aktif lapangan secara parsial | Pengelola | `200 OK` | `401, 403, 404` |
| 24 | `DELETE` | `/manage/courts/{id}` | `courts` | Menghapus atau menonaktifkan lapangan dari sistem | Pengelola | `204 No Content` | `401, 403, 404, 409` |
| 25 | `POST` | `/manage/prices` | `prices` | Menambahkan konfigurasi aturan tarif sewa lapangan | Pengelola | `201 Created` | `401, 403, 422` |
| 26 | `DELETE` | `/manage/prices/{id}` | `prices` | Menghapus aturan konfigurasi tarif sewa | Pengelola | `204 No Content` | `401, 403, 404` |
| 27 | `POST` | `/manage/blocks` | `blocks` | Membuat jadwal pemblokiran slot operasional (perawatan/event turnamen) | Pengelola | `201 Created` | `401, 403, 422` |
| 28 | `DELETE` | `/manage/blocks/{id}` | `blocks` | Membatalkan jadwal pemblokiran lapangan | Pengelola | `204 No Content` | `401, 403, 404` |
| 29 | `PATCH` | `/manage/bookings/{id}/status` | `bookings` | Mengubah status operasional pesanan (`COMPLETED` atau `NO_SHOW`) | Pengelola | `200 OK` | `401, 403, 422` |
| 30 | `GET` | `/admin/users` | `users` | Menampilkan seluruh data pengguna platform dengan filter role dan status | Admin | `200 OK` | `401, 403` |
| 31 | `PATCH` | `/admin/users/{id}/verify` | `users` | Melakukan verifikasi dan persetujuan aktivasi akun pengelola venue baru | Admin | `200 OK` | `401, 403, 404` |
| 32 | `GET` | `/admin/webhook-logs` | `webhook-logs` | Menampilkan rekap jejak callback webhook payment gateway | Admin | `200 OK` | `401, 403` |

---

## 20.3 Aktivitas 3 – Desain Query untuk Endpoint Collection (15 menit)
Dirancang query parameter terstandar pada endpoint bertipe collection untuk menyaring (*filter*), mencari (*search*), mengurutkan (*sort*), dan memaginasi (*pagination*):

| Endpoint Collection | Filtering (Whitelist) | Searching | Sorting (Default & Whitelist) | Pagination (Default / Maksimum) |
|:---|:---|:---|:---|:---|
| **`GET /api/v1/venues`** | `city` (string)<br>`sport_type` (futsal, badminton, basketball, tennis, mini_soccer)<br>`is_active` (boolean)<br>`min_price` (integer)<br>`max_price` (integer) | `q` (mencari substring pada `name`, `address`, dan `city`) | Default: `name`<br>Whitelist: `name`, `-name`, `created_at`, `-created_at`, `starting_price`, `-starting_price` | Model: Offset/page-based<br>Parameter: `page`, `per_page`<br>Default: `10`<br>Maksimum: `50` data/layar |
| **`GET /api/v1/bookings`** | `status` (`PENDING_PAYMENT`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `EXPIRED`, `NO_SHOW`)<br>`court_id` (integer)<br>`date_from` (YYYY-MM-DD)<br>`date_to` (YYYY-MM-DD) | `q` (mencari kode booking `code`, misal `BK-20261011-8899A`) | Default: `-created_at` (terbaru lebih dulu)<br>Whitelist: `created_at`, `-created_at`, `play_date`, `-play_date`, `total_price`, `-total_price` | Model: Offset/page-based<br>Parameter: `page`, `per_page`<br>Default: `15`<br>Maksimum: `100` data/layar |
| **`GET /api/v1/admin/users`** | `role` (`penyewa`, `pengelola`, `admin`)<br>`status` (`active`, `pending`, `suspended`) | `q` (mencari pada `name`, `email`, dan `phone`) | Default: `-created_at`<br>Whitelist: `name`, `-name`, `email`, `created_at`, `-created_at` | Model: Offset/page-based<br>Parameter: `page`, `per_page`<br>Default: `20`<br>Maksimum: `100` data/layar |

---

## 20.4 Aktivitas 4 – Peer Review & Quality Checklist (10 menit)
Hasil evaluasi dan penilaian rancangan API SportVenue mengacu pada 8 butir standar industri:

| No | Butir Pemeriksaan Standar Desain | Status | Catatan Evaluasi & Hasil Review |
|:--:|:---|:---:|:---|
| 1 | Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja | **[ X ] Ya** | Seluruh endpoint konsisten jamak (`/venues`, `/courts`, `/prices`, `/blocks`, `/bookings`, `/payments`). Tidak ditemukan kata kerja seperti `/getVenues` atau `/createBooking`. |
| 2 | Prefix `/api/v1` dipakai konsisten | **[ X ] Ya** | Seluruh endpoint RESTful berada di bawah prefix `/api/v1` untuk menjamin strategi versioning tanpa memutus client lama. |
| 3 | Method sesuai operasi; GET tidak mengubah data | **[ X ] Ya** | Method GET bersifat *safe & idempotent*. Perubahan data selalu menggunakan method POST, PUT, PATCH, atau DELETE. |
| 4 | POST → 201, DELETE → 204, dan status gagal relevan dicantumkan | **[ X ] Ya** | Operasi create mengembalikan `201 Created` disertai header Location; delete mengembalikan `204 No Content`. Status gagal (400, 401, 403, 404, 409, 422) dirancang lengkap. |
| 5 | Nesting maksimal 2 tingkat | **[ X ] Ya** | Hierarki URI dibatasi maksimal 2 tingkat (misal `/venues/{id}/courts`, `/courts/{id}/prices`). Setelah item memiliki ID unik global, client mengakses secara flat (`/courts/{id}`, `/bookings/{id}`). |
| 6 | Setiap collection memiliki pagination dengan batas maksimum | **[ X ] Ya** | Seluruh endpoint koleksi dibatasi `per_page` default 10–20 dengan batas maksimum 50–100 untuk menangkal celah OWASP *Unrestricted Resource Consumption*. |
| 7 | Filter/sort memakai query parameter dengan whitelist | **[ X ] Ya** | Parameter filtering diletakkan di query string GET, dilengkapi whitelist field untuk mencegah eksploitasi kolom sensitif (seperti password_hash atau token). |
| 8 | Format error seragam | **[ X ] Ya** | Seluruh response error distandarkan dengan format seragam `{ "status": "error", "message": "...", "errors": { ... } }`. |

### Tindak Lanjut Perbaikan Pasca Review:
1. Menambahkan validasi regex ISO 8601 `YYYY-MM-DD` pada query parameter ketersediaan slot jam (`/courts/{id}/availability?date=`).
2. Menghilangkan seluruh ekspos kolom internal database (`password`, `remember_token`, `updated_at`) pada representasi DTO JSON Response Resource demi kepatuhan terhadap OWASP API3 (*Broken Object Property Level Authorization*).
3. Memastikan mekanisme *Anti Double Booking* (§9.2 PRD) selalu mengembalikan status HTTP `409 Conflict` dengan kode error terstruktur saat terjadi bentrok jadwal slot.

---

## 📦 Representasi Data & Format Response Standar

### 1. Struktur Response Envelope Standar (Laravel Resource)
```json
// Sukses Item Tunggal (GET /api/v1/venues/1)
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Arena Garuda Futsal",
    "slug": "arena-garuda-futsal",
    "city": "Tegal",
    "dp_percent": 30,
    "cancel_free_hours": 24,
    "cancel_refund_percent": 50
  }
}

// Sukses Koleksi dengan Pagination (GET /api/v1/venues?page=1&per_page=2)
{
  "status": "success",
  "data": [
    { "id": 1, "name": "Arena Garuda Futsal", "city": "Tegal", "starting_price": 120000 },
    { "id": 2, "name": "GOR Satria Badminton", "city": "Tegal", "starting_price": 40000 }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/venues?page=1",
    "last": "http://localhost:8000/api/v1/venues?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/v1/venues?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 2,
    "total": 10,
    "last_page": 5
  }
}

// Format Error Terstandar (422 Validasi)
{
  "status": "error",
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "slots": ["Minimal satu slot jam harus dipilih."]
  }
}

// Format Error Aturan Bisnis (409 Conflict)
{
  "status": "error",
  "message": "Satu atau lebih slot sudah terisi atau sedang di-hold. Silakan pilih slot lain.",
  "code": "SLOT_ALREADY_LOCKED"
}
```

---

## 🎯 Keputusan Desain Non-CRUD & Pola Bisnis
1. **Pola Pembatalan Booking (`POST /bookings/{id}/cancellation`):**  
   Menggunakan pola *Sub-resource / Process as Resource*. Pembatalan sewa lapangan bukan sekadar `PATCH status = cancelled`, melainkan mengevaluasi audit trail kebijakan venue (perhitungan sisa waktu H-24/H-12, persentase refund DP yang berhak diterima penyewa, dan pelepasan kunci baris slot di `booking_slots`).
2. **Pola Pembayaran DP (`POST /bookings/{id}/payments`):**  
   Pembayaran dimodelkan sebagai sub-resource independen dari booking. Setiap upaya pembayaran memiliki riwayat `order_id`, token Midtrans Snap, status settlement, dan metode pembayaran digital terpisah.
3. **Pemberian Izin & Otorisasi:**  
   Menggunakan Laravel Sanctum dengan token-based authentication dan Gate/Policy berbasis kepemilikan data (`owner_id` venue harus cocok dengan `auth()->id()`).
4. **Dukungan PUT vs PATCH:**  
   Keduanya didukung pada endpoint `/manage/courts/{id}` dan `/manage/venues/{id}`. PUT menggantikan seluruh representasi data lapangan/venue, sedangkan PATCH dipergunakan untuk pembaruan atribut tunggal secara atomik (misalnya mengubah status `is_active`).
