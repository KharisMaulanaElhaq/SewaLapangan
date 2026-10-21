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
Resource diturunkan secara presisi dari kebutuhan fungsional dan kata benda (*nouns*) pada alur bisnis SportVenue: cari venue/lapangan → cek ketersediaan slot → booking → bayar DP → konfirmasi / batalkan sewa.

Sesuai kaidah arsitektur REST (*Modul Ajar Point 9.3: "Resource Tidak Harus Sama dengan Tabel"*), resource adalah model konseptual yang dilihat dan diakses oleh client melalui URI. Kolom dan relasi di bawah ini telah diselaraskan 100% dengan skema fisik database / ERD:

### A. Tabel Pemodelan Resource Lengkap (Tersinkronisasi dengan ERD)
| No | Resource URI (Jamak) | Tabel Database | Atribut Fisik Database & Field Turunan | Relasi Antar Resource | Jenis Resource | Diekspos API |
|:--:|:---|:---|:---|:---|:---|:---:|
| 1 | `users` | `users` | `id, name, email, phone, password, role, status, email_verified_at, created_at` | `1 : N` ke `venues` (owner pengelola)<br/>`1 : N` ke `bookings` (penyewa) | Collection | **Ya** |
| 2 | `venues` | `venues` | `id, owner_id, name, slug, description, address, city, phone, dp_percent, cancel_free_hours, cancel_refund_percent, image_url, created_at` | `N : 1` ke `users` (owner)<br/>`1 : N` ke `courts` | Collection | **Ya** |
| 3 | `courts` | `courts` | `id, venue_id, name, sport_type, is_active, created_at` | `N : 1` ke `venues`<br/>`1 : N` ke `prices`, `operating-hours`, `blocks`, `bookings` | Sub-resource / Collection | **Ya** |
| 4 | `prices` | `court_prices` | `id, court_id, day_type, start_time, end_time, price, created_at` | `N : 1` ke `courts` | Sub-resource | **Ya** |
| 5 | `operating-hours` | `operating_hours` | `id, court_id, day_of_week, open_time, close_time, slot_duration_minutes` | `N : 1` ke `courts` | Sub-resource | **Ya** |
| 6 | `blocks` | `schedule_blocks` | `id, court_id, start_at, end_at, reason, created_at` | `N : 1` ke `courts` | Sub-resource | **Ya** |
| 7 | `availability` | *(Kalkulasi Dinamis)* | `court_id, play_date, slots [slot_time, status, price]` *(Dihasilkan dari kalkulasi jam operasional, blokir jadwal, dan slot terisi)* | Turunan dari `courts` (kalkulasi slot terisi & blokir) | Sub-resource (Virtual / Read-Only) | **Ya (GET)** |
| 8 | `bookings` | `bookings` | `id, code, user_id, court_id, total_price, dp_amount, status, expires_at, cancel_free_hours_snapshot, cancel_refund_percent_snapshot, cancelled_at, refund_amount, cancellation_reason, notes, created_at` | `N : 1` ke `users`, `N : 1` ke `courts`<br/>`1 : N` ke `booking_slots`, `1 : N` ke `payments` | Collection | **Ya** |
| 9 | `payments` | `payments` | `id, booking_id, order_id, type, amount, status, payment_type, snap_token, snap_redirect_url, paid_at, raw_payload` | `N : 1` ke `bookings` | Sub-resource / Collection | **Ya** |
| 10 | `booking_slots` | `booking_slots` | `id, booking_id, court_id, slot_start, slot_end, price, created_at` | `N : 1` ke `bookings`<br/>`unique(court_id, slot_start)` | Internal Engine (Anti Double Booking) | **Tidak (Internal)** |
| 11 | `me` | `users` | `id, name, email, phone, role, status` | N/A (merujuk ke subjek token login aktif) | Singleton | **Ya (GET)** |
| 12 | `webhook-logs` | `webhook_logs` | `id, provider, order_id, payload, signature_valid, status, error_message, processed_at` | N/A (audit trail callback Midtrans) | Collection (Admin) | **Ya (GET)** |

> [!NOTE]
> **Catatan Penyelarasan Atribut Fisik vs Nilai Turunan (Berdasarkan Verifikasi ERD):**
> 1. **`venues.is_active`**: Tabel `venues` tidak memiliki kolom `is_active`. Status keaktifan dikontrol langsung di tingkat lapangan fisik melalui kolom `courts.is_active`. Venue yang tampil di katalog publik adalah venue yang memiliki minimal 1 lapangan aktif.
> 2. **`play_date` pada `bookings`**: Tabel `bookings` tidak menyimpan kolom `play_date` statis. Tanggal dan jam sewa disimpan secara atomik pada baris `booking_slots.slot_start` dan `slot_end` agar mendukung pemesanan multi-slot dalam satu transaksi.
> 3. **Spesifikasi Lapangan `courts`**: Tabel `courts` hanya menyimpan `name`, `sport_type`, dan `is_active`. Atribut gambar berada pada `venues.image_url`.
> 4. **`starting_price` pada katalog `venues`**: Bukan kolom tabel fisik `venues`, melainkan nilai agregasi virtual dinamis hasil kueri `MIN(court_prices.price)` dari seluruh lapangan milik venue terkait.

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
Disusun 26 endpoint RESTful yang selaras dengan seluruh modul bisnis dan model database SportVenue. Setiap baris memuat HTTP method semantik, URI terstandar, fungsi operasi bisnis, aktor akses, status sukses, dan status gagal relevan:

| No | Method | Endpoint (URI) | Resource Terkait | Fungsi & Deskripsi Operasi Bisnis | Aktor Akses | Status Sukses | Status Gagal Umum |
|:--:|:---:|:---|:---|:---|:---:|:---:|:---|
| 1 | `POST` | `/auth/register` | `users` | Registrasi akun pengguna baru (penyewa langsung aktif, pengelola pending verifikasi) | Publik | `201 Created` | `400, 422` |
| 2 | `POST` | `/auth/login` | `users` | Autentikasi kredensial pengguna dan menerbitkan Sanctum Bearer Token | Publik | `200 OK` | `401, 422` |
| 3 | `POST` | `/auth/logout` | `users` | Mencabut token autentikasi aktif pengguna | Login | `204 No Content` | `401` |
| 4 | `GET` | `/me` | `me` (Singleton) | Mengambil profil data diri pengguna yang sedang login | Login | `200 OK` | `401` |
| 5 | `GET` | `/venues` | `venues` | Menampilkan katalog venue dengan filter kota, cabang olahraga, search, pagination | Publik | `200 OK` | `400, 422` |
| 6 | `GET` | `/venues/{id}` | `venues` | Mengambil informasi detail satu venue beserta fasilitas lapangannya | Publik | `200 OK` | `404` |
| 7 | `GET` | `/venues/{id}/courts` | `courts` | Menampilkan seluruh lapangan yang tersedia di dalam venue tertentu | Publik | `200 OK` | `404` |
| 8 | `GET` | `/courts/{id}` | `courts` | Mengambil detail satu lapangan fisik (nama, cabang olahraga, status aktif, relasi harga) | Publik | `200 OK` | `404` |
| 9 | `GET` | `/courts/{id}/prices` | `prices` | Menampilkan daftar struktur tarif sewa reguler, peak hour, dan weekend | Publik | `200 OK` | `404` |
| 10 | `GET` | `/courts/{id}/operating-hours` | `operating-hours` | Menampilkan jam buka-tutup operasional lapangan dalam seminggu penuh | Publik | `200 OK` | `404` |
| 11 | `GET` | `/courts/{id}/availability` | `availability` | Matriks status ketersediaan slot jam (08:00–23:00) dan tarif per tanggal | Publik | `200 OK` | `404, 422` |
| 12 | `POST` | `/bookings` | `bookings` | Membuat reservasi booking baru, mengunci slot atomik, hold DP 15 menit | Penyewa | `201 Created` | `400, 409, 422` |
| 13 | `GET` | `/bookings` | `bookings` | Menampilkan seluruh riwayat transaksi booking milik penyewa aktif | Penyewa | `200 OK` | `401` |
| 14 | `GET` | `/bookings/{id}` | `bookings` | Mengambil detail faktur booking, slot yang diambil, dan rincian bayar | Penyewa, Pengelola | `200 OK` | `401, 403, 404` |
| 15 | `POST` | `/bookings/{id}/payments` | `payments` | Menginisialisasi token transaksi Midtrans Snap dan redirect payment URL | Penyewa | `201 Created` | `401, 403, 409` |
| 16 | `POST` | `/bookings/{id}/cancellation` | `bookings` | Membatalkan reservasi aktif dan memproses refund otomatis kebijakan venue | Penyewa | `200 OK` | `401, 403, 409` |
| 17 | `POST` | `/payments/webhook/midtrans` | `payments` | Endpoint callback notifikasi pembayaran gateway (validasi HMAC SHA-512) | Midtrans | `200 OK` | `401, 403` |
| 18 | `POST` | `/manage/venues` | `venues` | Mendaftarkan lokasi venue baru milik mitra pengelola | Pengelola | `201 Created` | `401, 403, 422` |
| 19 | `PUT` | `/manage/venues/{id}` | `venues` | Memperbarui data profil venue secara menyeluruh (alamat, persentase DP, kebijakan refund) | Pengelola | `200 OK` | `401, 403, 404, 422` |
| 20 | `DELETE` | `/manage/venues/{id}` | `venues` | Menonaktifkan/menghapus lokasi venue dari sistem | Pengelola | `204 No Content` | `401, 403, 404, 409` |
| 21 | `POST` | `/manage/courts` | `courts` | Menambahkan unit lapangan baru pada venue mitra | Pengelola | `201 Created` | `401, 403, 422` |
| 22 | `PUT` | `/manage/courts/{id}` | `courts` | Memperbarui spesifikasi lapangan secara menyeluruh | Pengelola | `200 OK` | `401, 403, 404, 422` |
| 23 | `PATCH` | `/manage/courts/{id}` | `courts` | Mengubah status aktif/non-aktif lapangan secara parsial (`is_active`) | Pengelola | `200 OK` | `401, 403, 404` |
| 24 | `DELETE` | `/manage/courts/{id}` | `courts` | Menghapus atau menonaktifkan lapangan dari sistem | Pengelola | `204 No Content` | `401, 403, 404, 409` |
| 25 | `POST` | `/manage/prices` | `prices` | Menambahkan konfigurasi aturan tarif sewa lapangan | Pengelola | `201 Created` | `401, 403, 422` |
| 26 | `DELETE` | `/manage/prices/{id}` | `prices` | Menghapus aturan konfigurasi tarif sewa | Pengelola | `204 No Content` | `401, 403, 404` |
| 27 | `POST` | `/manage/blocks` | `blocks` | Membuat jadwal pemblokiran slot operasional (perawatan/event turnamen) | Pengelola | `201 Created` | `401, 403, 422` |
| 28 | `DELETE` | `/manage/blocks/{id}` | `blocks` | Membatalkan jadwal pemblokiran lapangan fisik | Pengelola | `204 No Content` | `401, 403, 404` |
| 29 | `PATCH` | `/manage/bookings/{id}/status` | `bookings` | Mengubah status operasional pesanan (`COMPLETED` atau `NO_SHOW`) | Pengelola | `200 OK` | `401, 403, 422` |
| 30 | `GET` | `/admin/users` | `users` | Menampilkan seluruh data pengguna platform dengan filter role dan status | Admin | `200 OK` | `401, 403` |
| 31 | `PATCH` | `/admin/users/{id}/verify` | `users` | Melakukan verifikasi dan persetujuan aktivasi akun pengelola venue baru | Admin | `200 OK` | `401, 403, 404` |

---

## 20.3 Aktivitas 3 – Desain Query untuk Endpoint Collection (15 menit)
Dirancang query parameter terstandar pada endpoint bertipe collection untuk menyaring (*filter*), mencari (*search*), mengurutkan (*sort*), dan memaginasi (*pagination*) dengan whitelist yang selaras dengan skema database:

| Endpoint Collection | Filtering (Whitelist) | Searching | Sorting (Default & Whitelist) | Pagination (Default / Maksimum) |
|:---|:---|:---|:---|:---|
| **`GET /api/v1/venues`** | `city` (string)<br>`sport` / `sport_type` (futsal, badminton, basketball, mini_soccer)<br>`min_price` (integer)<br>`max_price` (integer)<br>*(Filter harga & olahraga dievaluasi melalui relasi child `courts.courtPrices`)* | `q`<br>*(mencari substring pada `name`, `address`, dan `city`)* | • Default: `name`<br>• Whitelist: `name`, `-name`, `created_at`, `-created_at`, `starting_price*`, `-starting_price*`<br>*(Ket: starting_price diurutkan via subquery agregasi `MIN(court_prices.price)`)* | Model: Offset-based<br>Parameter: `page`, `per_page`<br>• Default: **10**<br>• Maksimum: **50** data/layar |
| **`GET /api/v1/bookings`** | `status` (`PENDING_PAYMENT`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `EXPIRED`, `NO_SHOW`)<br>`court_id` (integer)<br>`date_from` (YYYY-MM-DD)<br>`date_to` (YYYY-MM-DD)<br>*(Filter rentang tanggal dievaluasi melalui child `booking_slots.slot_start`)* | `q`<br>*(mencari kode booking `code`, misal `BK-20261011-8899A`)* | • Default: `-created_at` (terbaru lebih dulu)<br>• Whitelist: `created_at`, `-created_at`, `total_price`, `-total_price`, `expires_at`, `-expires_at` | Model: Offset-based<br>Parameter: `page`, `per_page`<br>• Default: **15**<br>• Maksimum: **100** data/layar |
| **`GET /api/v1/admin/users`** | `role` (`penyewa`, `pengelola`, `admin`)<br>`status` (`active`, `pending`, `suspended`) | `q`<br>*(mencari pada `name`, `email`, dan `phone`)* | • Default: `-created_at`<br>• Whitelist: `name`, `-name`, `email`, `created_at`, `-created_at` | Model: Offset-based<br>Parameter: `page`, `per_page`<br>• Default: **20**<br>• Maksimum: **100** data/layar |

---

## 20.4 Aktivitas 4 – Peer Review & Quality Checklist (10 menit)
Hasil evaluasi dan penilaian rancangan API SportVenue mengacu pada 8 butir standar industri:

| No | Butir Pemeriksaan Standar Desain | Status | Catatan Evaluasi & Hasil Review |
|:--:|:---|:---:|:---|
| 1 | Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja | **[ X ] Ya** | Seluruh endpoint konsisten jamak (`/venues`, `/courts`, `/prices`, `/blocks`, `/bookings`, `/payments`). Tidak ditemukan kata kerja seperti `/getVenues` atau `/createBooking`. |
| 2 | Prefix `/api/v1` dipakai konsisten | **[ X ] Ya** | Seluruh endpoint RESTful berada di bawah prefix `/api/v1` untuk menjamin strategi versioning tanpa memutus client lama. |
| 3 | Method sesuai operasi; GET tidak mengubah data | **[ X ] Ya** | Method GET bersifat *safe & idempotent*. Perubahan data selalu menggunakan method POST, PUT, PATCH, atau DELETE. |
| 4 | POST → 201, DELETE → 204, dan status gagal relevan dicantumkan | **[ X ] Ya** | Operasi create mengembalikan `201 Created` disertai header Location; delete mengembalikan `204 No Content`. Status gagal (400, 401, 403, 404, 409, 422) dirancang lengkap. |
| 5 | Nesting maksimal 2 tingkat | **[ X ] Ya** | Hierarki URI dibatasi maksimal 2 tingkat (misal `/venues/{id}/courts`, `/courts/{id}/prices`). Setelah item memiliki ID unik global, akses dilakukan secara flat (`/courts/{id}`, `/bookings/{id}`). |
| 6 | Setiap collection memiliki pagination dengan batas maksimum | **[ X ] Ya** | Seluruh endpoint koleksi dibatasi `per_page` default 10–20 dengan batas maksimum 50–100 untuk menangkal celah OWASP *Unrestricted Resource Consumption*. |
| 7 | Filter/sort memakai query parameter dengan whitelist | **[ X ] Ya** | Parameter filtering diletakkan di query string GET, dilengkapi whitelist field yang selaras dengan kolom fisik ERD untuk mencegah SQL error dan ekspos kolom sensitif. |
| 8 | Format error seragam | **[ X ] Ya** | Seluruh response error distandarkan dengan format seragam `{ "status": "error", "message": "...", "errors": { ... } }`. |

### Tindak Lanjut Perbaikan Pasca Review & Feedback Selaras ERD:
1. **Penyelarasan Kolom `play_date`**: Menghapus `play_date` dari whitelist kolom langsung `bookings` pada `GET /bookings`, dan mencatatnya secara transparan bahwa tanggal main diturunkan secara relasional melalui child table `booking_slots.slot_start`.
2. **Penyelarasan Kolom `venues.is_active`**: Menghapus atribut `is_active` dari entitas `venues` dan filter `GET /venues`, karena status aktif secara fisik dikelola di level lapangan (`courts.is_active`).
3. **Penyelarasan Detail Lapangan `GET /courts/{id}`**: Memperbaiki deskripsi operasi agar tepat merepresentasikan skema fisik tabel `courts` (`name`, `sport_type`, `is_active`), serta menegaskan bahwa foto fasilitas berada di `venues.image_url`.
4. **Klarifikasi Atribut `starting_price`**: Mendokumentasikan secara eksplisit bahwa `starting_price` pada katalog venue adalah nilai turunan (field virtual) dari harga terendah `MIN(court_prices.price)`, bukan kolom fisik tabel `venues`.
5. **Keamanan Data OWASP API3**: Menghilangkan seluruh ekspos kolom internal database (`password`, `remember_token`, `updated_at`) pada representasi DTO JSON Response Resource.
6. **Mekanisme Anti Double Booking (§9.2 PRD)**: Memastikan selalu mengembalikan status HTTP `409 Conflict` dengan kode error terstruktur saat terjadi bentrok reservasi slot.

---

## 🚦 Kamus & Panduan HTTP Status Code Handling (Point 5 & 16 Modul Ajar)

Untuk memastikan konsistensi kontrak API dan pemahaman menyeluruh antara tim backend dan pengembang client (frontend/mobile), setiap kode status HTTP yang dipakai dalam perancangan SportVenue didefinisikan secara baku berdasarkan semantik **RFC 9110** dan praktik terbaik industri:

### A. Tabel Referensi & Pengertian Kode Status HTTP
| Kode HTTP | Kategori | Arti & Semantik Standar (RFC 9110) | Konteks Penggunaan pada SportVenue | Karakteristik Body Response |
|:---:|:---:|:---|:---|:---|
| **`200 OK`** | 2xx Success | Permintaan berhasil diproses dan hasil operasi dikembalikan. | Digunakan pada `GET` data resource (venues, bookings, profile), `PUT`/`PATCH` pembaruan yang mengembalikan data terbaru, dan `POST` pembatalan sewa yang menghitung refund. | Memuat envelope `{ status: "success", data: { ... } }`. |
| **`201 Created`** | 2xx Success | Permintaan berhasil dan menghasilkan resource baru di server. | Digunakan pada pembuatan akun (`POST /auth/register`), pembuatan booking (`POST /bookings`), penambahan lapangan (`POST /manage/courts`), dan inisialisasi pembayaran Snap (`POST /bookings/{id}/payments`). | Wajib menyertakan data resource baru dan header `Location: /api/v1/{resource}/{id}`. |
| **`204 No Content`** | 2xx Success | Permintaan berhasil diproses secara tuntas, namun tidak ada konten data yang perlu dikirim kembali. | Digunakan saat penghapusan resource (`DELETE /manage/courts/{id}`, `DELETE /manage/blocks/{id}`) dan saat mencabut token sesi (`POST /auth/logout`). | **Tanpa Body** (panjang payload 0 byte) untuk menghemat bandwidth jaringan. |
| **`400 Bad Request`** | 4xx Client Error | Sintaks permintaan rusak atau tidak dapat dipahami oleh parser server (*malformed request*). | Terjadi jika payload JSON yang dikirim client rusak sintaksnya (misal kurung kurawal tidak tertutup, koma menggantung, atau tipe data payload bertentangan). | Memuat amplop `{ status: "error", message: "Malformed JSON payload." }`. |
| **`401 Unauthorized`** | 4xx Client Error | Permintaan membutuhkan autentikasi pengguna, tetapi kredensial tidak ada atau tidak valid. | Terjadi jika client mengakses endpoint terlindungi tanpa header `Authorization: Bearer <token>`, token Sanctum telah kedaluwarsa/dicabut, atau login email/password salah. | Memuat amplop `{ status: "error", message: "Unauthenticated / Invalid credentials." }`. |
| **`403 Forbidden`** | 4xx Client Error | Identitas pengguna dikenali, tetapi pengguna tidak memiliki hak izin (*otorisasi*) atas resource tersebut. | Terjadi jika penyewa mencoba mengakses endpoint `/manage/*`, pengelola mencoba mengubah venue milik orang lain (*policy ownership violation*), atau signature webhook Midtrans tidak sah. | Memuat amplop `{ status: "error", message: "Akses ditolak. Anda tidak berhak melakukan aksi ini." }`. |
| **`404 Not Found`** | 4xx Client Error | Resource yang diminta tidak ditemukan pada alamat URI yang diberikan. | Terjadi ketika mencari venue, lapangan, booking, atau harga dengan `{id}` yang tidak terdaftar di database MySQL. | Memuat amplop `{ status: "error", message: "Resource tidak ditemukan." }`. |
| **`409 Conflict`** | 4xx Client Error | Permintaan valid secara sintaks, namun bertentangan dengan kondisi/state database saat ini. | **Kunci Arsitektur Concurrency (§9.2 PRD):** Dikembalikan ketika slot jam yang diminta sudah terkunci/di-hold oleh penyewa lain, atau saat booking yang sudah selesai/kedaluwarsa dibatalkan kembali. | Memuat amplop `{ status: "error", message: "Slot sudah terisi.", code: "SLOT_ALREADY_LOCKED" }`. |
| **`422 Unprocessable Content`** | 4xx Client Error | Permintaan terbaca sempurna (*well-formed*), namun isi field melanggar aturan validasi bisnis. | Dikembalikan ketika input form gagal validasi: format tanggal bukan `YYYY-MM-DD`, slot booking kosong, persentase DP di luar 10–100%, atau email sudah terdaftar. | Memuat `{ status: "error", message: "...", errors: { field: ["detail"] } }`. |
| **`429 Too Many Requests`** | 4xx Client Error | Client telah melampaui kuota frekuensi pengiriman request dalam batas waktu yang ditentukan (*rate limit*). | Menangkal serangan spam booking, login brute-force, atau bot scraping katalog (misal limit > 60 request/menit per IP/token). | Menyertakan header `Retry-After: 60` dan pesan penolakan kuota. |
| **`500 Internal Server Error`** | 5xx Server Error | Terjadi kegagalan sistem internal pada server yang tidak terduga saat memproses request. | Terjadi ketika koneksi server database MySQL terputus, fatal exception runtime, atau kegagalan internal kode tanpa penanganan khusus. | Memuat pesan umum tanpa mengekspos *stack trace* sensitif demi keamanan sistem. |
| **`503 Service Unavailable`** | 5xx Server Error | Server sedang tidak dapat melayani permintaan karena ketergantungan pihak ketiga (*upstream*) mengalami gangguan. | Dikembalikan ketika API Payment Gateway Midtrans sedang maintenance/down sehingga inisialisasi token Snap tidak dapat diselesaikan sementara waktu. | Memuat petunjuk bahwa gateway sedang sibuk dan client disarankan mencoba kembali. |

### B. Analisis & Prinsip Penanganan Status Code Kritis
1. **Mengapa Menggunakan `409 Conflict` untuk Kasus Double Booking (Bukan 400 atau 422)?**  
   Request reservasi yang dikirim oleh penyewa sudah memenuhi seluruh kaidah validasi tipe data dan format tanggal (`422` lolos). Format JSON yang dikirimkan pun utuh dan benar (`400` lolos). Namun, pada lapisan transaksi atomik database (InnoDB Unique Constraint `court_id, slot_start`), record slot tersebut sudah dikunci oleh penyewa lain sepersekian detik sebelumnya. Sesuai RFC 9110, `409 Conflict` adalah status yang paling akurat untuk mengabarkan bentrok kondisi konkurensi antar-pengguna.
2. **Pembedaan Tegas Antara `401 Unauthorized` vs `403 Forbidden`:**  
   - `401 Unauthorized` menyangkut **Autentikasi (Siapa Anda?)**: Client belum membuktikan identitasnya atau token kedaluwarsa. Client dapat memperbaiki request dengan menyertakan token yang valid.  
   - `403 Forbidden` menyangkut **Otorisasi (Apa Hak Anda?)**: Identitas pengguna sudah terverifikasi secara sah, namun otorisasi peran (*Role / Policy Gate*) menyatakan pengguna dilarang memanipulasi entitas tersebut. Mengirim ulang kredensial yang sama tidak akan mengubah hasil.
3. **Mengapa Validasi Form Menghasilkan `422 Unprocessable Content` (Bukan 400)?**  
   Standar REST modern membedakan kesalahan level transportasi/sintaks (`400 Bad Request` untuk JSON corrupt/malformed) dengan kesalahan level logika domain aplikasi (`422 Unprocessable Content` untuk validasi kolom input). Hal ini memudahkan *frontend library* seperti Formik atau Vue/React Hook Form memetakan *field error* secara otomatis ke elemen antarmuka pengguna.

---

## 📦 Representasi Data & Format Response Standar

### 1. Struktur Response Envelope Standar (Laravel Resource)
```json
// 1. Sukses Item Tunggal (GET /api/v1/venues/1)
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Arena Garuda Futsal",
    "slug": "arena-garuda-futsal",
    "description": "Gelanggang futsal berstandar nasional",
    "address": "Jl. Martoloyo No. 12",
    "city": "Tegal",
    "phone": "081234567890",
    "dp_percent": 30,
    "cancel_free_hours": 24,
    "cancel_refund_percent": 50,
    "image_url": "https://sportvenue.test/storage/venues/garuda.jpg"
  }
}

// 2. Sukses Koleksi dengan Pagination (GET /api/v1/venues?page=1&per_page=2)
{
  "status": "success",
  "data": [
    { 
      "id": 1, 
      "name": "Arena Garuda Futsal", 
      "city": "Tegal", 
      "starting_price": 120000 
    },
    { 
      "id": 2, 
      "name": "GOR Satria Badminton", 
      "city": "Tegal", 
      "starting_price": 40000 
    }
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

// 3. Format Error Terstandar (422 Validasi)
{
  "status": "error",
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "slots": ["Minimal satu slot jam harus dipilih."]
  }
}

// 4. Format Error Aturan Bisnis (409 Conflict)
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
5. **Strategi Versioning:**  
   Menggunakan path-based versioning (`/api/v1`) yang transparan, mudah diuji via Postman, dan aman untuk evolusi jangka panjang tanpa merusak client aplikasi mobile maupun web yang sudah berjalan.
