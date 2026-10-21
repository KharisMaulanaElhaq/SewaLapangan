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
- **Bahasa Penamaan:** Bahasa Inggris (sesuai standar konvensi RESTful & Laravel `Route::apiResource`)
- **Format Pertukaran Data:** JSON (`Content-Type: application/json`, `Accept: application/json`)

---

## 20.1 Aktivitas 1 – Pemodelan Resource
Resource diturunkan dari kata benda (*nouns*) pada kebutuhan bisnis platform booking olahraga:

### A. Tabel Pemodelan Resource
| No | Resource (Jamak) | Atribut Utama | Relasi Antar Entitas | Jenis Resource |
|:--:|:---|:---|:---|:---|
| 1 | `venues` | `id, owner_id, name, slug, address, city, phone, dp_percent, cancel_free_hours, cancel_refund_percent, is_active` | `1 : N` ke `courts`, `N : 1` ke `users` (owner) | Collection |
| 2 | `courts` | `id, venue_id, name, sport_type, surface, capacity_players, is_active` | `N : 1` ke `venues`, `1 : N` ke `court_prices`, `1 : N` ke `bookings` | Sub-resource / Collection |
| 3 | `court_prices` | `id, court_id, day_type, start_time, end_time, price, label` | `N : 1` ke `courts` | Sub-resource |
| 4 | `operating_hours` | `id, court_id, day_of_week, open_time, close_time, is_closed` | `N : 1` ke `courts` | Sub-resource |
| 5 | `schedule_blocks` | `id, court_id, start_at, end_at, block_type, reason, created_by` | `N : 1` ke `courts`, `N : 1` ke `users` | Sub-resource |
| 6 | `bookings` | `id, code, user_id, court_id, play_date, total_price, dp_amount, status, expires_at` | `N : 1` ke `users`, `N : 1` ke `courts`, `1 : N` ke `payments`, `1 : N` ke `booking_slots` | Collection |
| 7 | `payments` | `id, booking_id, order_id, type, amount, status, snap_token, redirect_url` | `N : 1` ke `bookings` | Sub-resource / Collection |
| 8 | `me` | `id, name, email, phone, role, status` | N/A (merujuk ke pengguna yang sedang terautentikasi) | Singleton |
| 9 | `booking_slots` | `id, booking_id, court_id, slot_start, slot_end` | `N : 1` ke `bookings`, `unique(court_id, slot_start)` | Internal Engine |

### B. Diagram Relasi Ringkas (ERD)
```
[users] 1 ──────────── N [venues] 1 ──────────── N [courts] 1 ──── N [court_prices]
   │                                                 │      1 ──── N [operating_hours]
   │                                                 │      1 ──── N [schedule_blocks]
   │ 1                                               │ 1
   │                                                 │
   ▼ N                                               ▼ N
[bookings] 1 ───────────────────────────────────── N [booking_slots] (InnoDB Unique Index)
   │ 1
   ▼ N
[payments] (Midtrans Snap & Callback Webhook)
```

---

## 20.2 Aktivitas 2 – Endpoint Matrix (Base URL: `/api/v1`)
Setiap endpoint dirancang konsisten menggunakan kata benda jamak, huruf kecil (*kebab-case*), method semantik HTTP, serta dilengkapi status sukses dan gagal:

| No | Method | Endpoint (URI) | Fungsi & Deskripsi Operasi | Aktor Akses | Status Sukses | Status Gagal Umum |
|:--:|:---:|:---|:---|:---:|:---:|:---|
| 1 | `POST` | `/auth/register` | Mendaftarkan akun pengguna baru (penyewa langsung aktif, pengelola pending verifikasi) | Publik | `201 Created` | `400 Bad Request`, `422 Unprocessable` |
| 2 | `POST` | `/auth/login` | Autentikasi kredensial pengguna dan menerbitkan Sanctum Bearer Token | Publik | `200 OK` | `401 Unauthorized`, `422 Unprocessable` |
| 3 | `POST` | `/auth/logout` | Mencabut token autentikasi aktif pengguna | Login | `204 No Content`| `401 Unauthorized` |
| 4 | `GET` | `/me` | Mengambil profil data diri pengguna yang sedang login (*singleton*) | Login | `200 OK` | `401 Unauthorized` |
| 5 | `GET` | `/venues` | Menampilkan katalog venue dengan filter kota, cabang olahraga, search, dan pagination | Publik | `200 OK` | `400 Bad Request`, `422 Unprocessable` |
| 6 | `GET` | `/venues/{id}` | Mengambil informasi detail satu venue beserta fasilitas lapangannya | Publik | `200 OK` | `404 Not Found` |
| 7 | `GET` | `/venues/{id}/courts` | Menampilkan seluruh lapangan yang tersedia di dalam venue tertentu | Publik | `200 OK` | `404 Not Found` |
| 8 | `GET` | `/courts/{id}` | Mengambil detail spesifikasi satu lapangan (tipe lantai, ukuran, foto) | Publik | `200 OK` | `404 Not Found` |
| 9 | `GET` | `/courts/{id}/prices` | Menampilkan daftar struktur tarif sewa reguler, peak hour, dan weekend | Publik | `200 OK` | `404 Not Found` |
| 10 | `GET` | `/courts/{id}/availability` | Matriks status ketersediaan slot jam (08:00–23:00) dan tarif per tanggal | Publik | `200 OK` | `404 Not Found`, `422 Unprocessable` |
| 11 | `POST` | `/bookings` | Membuat reservasi booking baru, mengunci slot atomik, hold DP 15 menit | Penyewa | `201 Created` | `400 Bad Request`, `409 Conflict`, `422` |
| 12 | `GET` | `/bookings` | Menampilkan seluruh riwayat transaksi booking milik penyewa aktif | Penyewa | `200 OK` | `401 Unauthorized` |
| 13 | `GET` | `/bookings/{id}` | Mengambil detail faktur booking, slot yang diambil, dan rincian bayar | Penyewa, Pengelola | `200 OK` | `401 Unauthorized`, `403 Forbidden`, `404` |
| 14 | `POST` | `/bookings/{id}/payments` | Menginisialisasi token transaksi Midtrans Snap dan redirect payment URL | Penyewa | `201 Created` | `401 Unauthorized`, `403 Forbidden`, `409` |
| 15 | `POST` | `/bookings/{id}/cancellation` | Membatalkan reservasi aktif dan memproses refund otomatis kebijakan venue | Penyewa | `200 OK` | `401 Unauthorized`, `403 Forbidden`, `409` |
| 16 | `POST` | `/payments/webhook/midtrans` | Endpoint callback notifikasi pembayaran gateway (validasi HMAC SHA-512) | Midtrans | `200 OK` | `401 Unauthorized`, `403 Forbidden` |
| 17 | `POST` | `/manage/courts` | Menambahkan unit lapangan baru pada venue mitra | Pengelola | `201 Created` | `401 Unauthorized`, `403 Forbidden`, `422` |
| 18 | `PUT` | `/manage/courts/{id}` | Memperbarui spesifikasi lapangan secara menyeluruh | Pengelola | `200 OK` | `401 Unauthorized`, `403 Forbidden`, `404` |
| 19 | `PATCH` | `/manage/courts/{id}` | Mengubah sebagian atribut lapangan (misal status aktif/non-aktif) | Pengelola | `200 OK` | `401 Unauthorized`, `403 Forbidden`, `404` |
| 20 | `DELETE`| `/manage/courts/{id}` | Menghapus atau menonaktifkan lapangan dari sistem | Pengelola | `204 No Content`| `401 Unauthorized`, `403 Forbidden`, `409` |
| 21 | `POST` | `/manage/courts/{id}/blocks` | Membuat jadwal pemblokiran slot operasional (perawatan/event turnamen) | Pengelola | `201 Created` | `401 Unauthorized`, `403 Forbidden`, `422` |
| 22 | `DELETE`| `/manage/blocks/{id}` | Membatalkan jadwal pemblokiran lapangan | Pengelola | `204 No Content`| `401 Unauthorized`, `403 Forbidden`, `404` |
| 23 | `PATCH` | `/manage/bookings/{id}/status` | Mengubah status operasional pesanan (`COMPLETED` atau `NO_SHOW`) | Pengelola | `200 OK` | `401 Unauthorized`, `403 Forbidden`, `422` |
| 24 | `GET` | `/admin/users` | Menampilkan seluruh data pengguna platform dengan filter role dan status | Admin | `200 OK` | `401 Unauthorized`, `403 Forbidden` |
| 25 | `PATCH` | `/admin/users/{id}/verify` | Melakukan verifikasi dan persetujuan aktivasi akun pengelola venue baru | Admin | `200 OK` | `401 Unauthorized`, `403 Forbidden`, `404` |

---

## 20.3 Aktivitas 3 – Desain Query untuk Endpoint Collection
Dirancang menggunakan query parameter terstandar pada endpoint bertipe collection untuk menyaring (*filter*), mencari (*search*), mengurutkan (*sort*), dan memaginasi (*pagination*):

| Endpoint Collection | Filtering (Whitelist) | Searching | Sorting (Default & Whitelist) | Pagination (Default / Maksimum) |
|:---|:---|:---|:---|:---|
| **`GET /api/v1/venues`** | `city` (string)<br>`sport_type` (futsal, badminton, basket, mini_soccer)<br>`is_active` (boolean)<br>`min_price` (integer)<br>`max_price` (integer) | `q` (mencari substring pada `name`, `address`, dan `city`) | Default: `name`<br>Whitelist: `name`, `-name`, `created_at`, `-created_at`, `starting_price`, `-starting_price` | Model: Offset/page-based<br>Parameter: `page`, `per_page`<br>Default: `10`<br>Maksimum: `50` data/layar |
| **`GET /api/v1/bookings`** | `status` (`PENDING_PAYMENT`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `EXPIRED`)<br>`court_id` (integer)<br>`date_from` (YYYY-MM-DD)<br>`date_to` (YYYY-MM-DD) | `q` (mencari kode booking `code`, misal `BK-20261011-8899A`) | Default: `-created_at` (terbaru lebih dulu)<br>Whitelist: `created_at`, `-created_at`, `play_date`, `-play_date`, `total_price`, `-total_price` | Model: Offset/page-based<br>Parameter: `page`, `per_page`<br>Default: `15`<br>Maksimum: `100` data/layar |
| **`GET /api/v1/admin/users`** | `role` (`penyewa`, `pengelola`, `admin`)<br>`status` (`active`, `pending`, `suspended`) | `q` (mencari pada `name`, `email`, dan `phone`) | Default: `-created_at`<br>Whitelist: `name`, `-name`, `email`, `created_at`, `-created_at` | Model: Offset/page-based<br>Parameter: `page`, `per_page`<br>Default: `20`<br>Maksimum: `100` data/layar |

---

## 20.4 Aktivitas 4 – Peer Review & Quality Checklist
Hasil evaluasi dan penilaian rancangan API SportVenue mengacu pada 8 butir standar industri:

| No | Butir Pemeriksaan Standar Desain | Status | Catatan Evaluasi & Hasil Review |
|:--:|:---|:---:|:---|
| 1 | Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja | **[ X ] Ya** | Seluruh endpoint konsisten jamak (`/venues`, `/courts`, `/bookings`, `/payments`). Tidak ditemukan kata kerja seperti `/getVenues` atau `/createBooking`. |
| 2 | Prefix `/api/v1` dipakai konsisten | **[ X ] Ya** | Seluruh endpoint RESTful berada di bawah prefix `/api/v1` untuk menjamin strategi versioning tanpa memutus client lama. |
| 3 | Method sesuai operasi; GET tidak mengubah data | **[ X ] Ya** | Method GET bersifat *safe & idempotent*. Perubahan data selalu menggunakan method POST, PUT, PATCH, atau DELETE. |
| 4 | POST → 201, DELETE → 204, dan status gagal relevan dicantumkan | **[ X ] Ya** | Operasi create mengembalikan `201 Created` disertai Location URI; delete mengembalikan `204 No Content`. Status gagal (400, 401, 403, 404, 409, 422) dirancang lengkap. |
| 5 | Nesting maksimal 2 tingkat | **[ X ] Ya** | Hierarki URI dibatasi maksimal 2 tingkat (misal `/venues/{id}/courts`). Setelah item memiliki ID unik global, client mengakses secara flat (`/courts/{id}`, `/bookings/{id}`). |
| 6 | Setiap collection memiliki pagination dengan batas maksimum | **[ X ] Ya** | Seluruh endpoint koleksi dibatasi `per_page` default 10–20 dengan batas maksimum 50–100 untuk menangkal celah OWASP *Unrestricted Resource Consumption*. |
| 7 | Filter/sort memakai query parameter dengan whitelist | **[ X ] Ya** | Parameter filtering diletakkan di query string GET, dilengkapi whitelist field untuk mencegah eksploitasi kolom sensitif (seperti password atau token). |
| 8 | Format error seragam | **[ X ] Ya** | Seluruh response error distandarkan dengan format seragam `{ "status": "error", "message": "...", "errors": { ... } }`. |

### Tindak Lanjut Perbaikan Pasca Review:
1. Menambahkan validasi regex ISO 8601 `YYYY-MM-DD` pada query parameter ketersediaan slot jam (`/courts/{id}/availability?date=`).
2. Menghilangkan seluruh ekspos kolom internal database (`password`, `remember_token`, `updated_at`) pada representasi DTO JSON Response Resource.
3. Memastikan mekanisme *Anti Double Booking* (§9.2 PRD) selalu mengembalikan status HTTP `409 Conflict` dengan kode error terstruktur saat terjadi bentrok jadwal.

---

## 📦 Representasi Data & Format Response Standar

### 1. Struktur Response Envelope Standar (Laravel Resource)
```json
// Sukses Item Tunggal
{
  "status": "success",
  "data": { ... }
}

// Sukses Koleksi dengan Pagination
{
  "status": "success",
  "data": [ ... ],
  "links": {
    "first": "http://localhost:8000/api/v1/venues?page=1",
    "last": "http://localhost:8000/api/v1/venues?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/v1/venues?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 48,
    "last_page": 5
  }
}

// Format Error Terstandar
{
  "status": "error",
  "message": "Pesan deskripsi kesalahan yang mudah dipahami manusia.",
  "errors": {
    "field_name": ["Detail pesan validasi."]
  }
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
