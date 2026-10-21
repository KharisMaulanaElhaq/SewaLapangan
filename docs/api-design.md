# API Design Document v1 — SportVenue
**Milestone M2: API Blueprint**  
**Mata Kuliah:** Pemrograman Web Service — Pertemuan Minggu ke-4  
**Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.  
**Institusi:** Teknik Informatika, Universitas Harkat Negeri (2026/2027)

---

## 👥 Bagian A: Identitas Proyek & Tim Pengembang (Kelompok 3)
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

## 20.1 Aktivitas 1 – Pemodelan Resource (Bagian B) (15 menit)
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

## 20.2 Aktivitas 2 – Endpoint Matrix (Bagian C) (20 menit)
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

## 20.3 Aktivitas 3 – Desain Query untuk Endpoint Collection (Bagian D) (15 menit)
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

## 21. Praktikum Mini: Mengamati Filtering dan Pagination pada API Publik (DummyJSON)

Untuk membandingkan rancangan REST API SportVenue dengan standar publik di industri, dilakukan pengujian 6 request ke API publik **DummyJSON** (`https://dummyjson.com/products`) menggunakan Postman / HTTP client:

### A. Tabel Observasi Praktikum Mini
| No | Request URL DummyJSON | Status Code | Parameter yang Dipakai | Metadata pada Response | Interpretasi / Perbandingan dengan Konvensi Modul |
|:--:|:---|:---:|:---|:---|:---|
| 1 | `GET /products?limit=5&skip=0` | `200 OK` | `limit=5`, `skip=0` | `total: 194`, `skip: 0`, `limit: 5` | Mengambil 5 produk pertama (halaman 1). Menggunakan model *offset-based pagination* dengan nama parameter `limit` (setara `per_page`) dan `skip` (setara offset `(page-1)*per_page`). |
| 2 | `GET /products?limit=5&skip=5&select=title,price` | `200 OK` | `limit=5`, `skip=5`, `select=title,price` | `total: 194`, `skip: 5`, `limit: 5` | Mengambil 5 data halaman ke-2 dengan *sparse fieldsets* (`select=title,price`). Membatasi payload jaringan hanya pada field yang diminta client, sejalan dengan pencegahan *Unrestricted Resource Consumption*. |
| 3 | `GET /products?sortBy=price&order=desc&limit=5` | `200 OK` | `sortBy=price`, `order=desc`, `limit=5` | `total: 194`, `skip: 0`, `limit: 5` | Mengurutkan harga termahal ke termurah. DummyJSON memisahkan parameter kolom (`sortBy`) dan arah urutan (`order`), sedangkan modul merancang notasi lebih ringkas `sort=-price`. |
| 4 | `GET /products/search?q=phone` | `200 OK` | `q=phone` | `total: 23`, `skip: 0`, `limit: 23` | Mencari kata kunci "phone". DummyJSON membuat rute khusus `/search`, sedangkan modul menyatukan pencarian di endpoint collection utama `GET /products?q=phone` agar lebih konsisten dan dapat digabung filter lain. |
| 5 | `GET /products/category/smartphones` | `200 OK` | Path parameter `{name}` | `total: 16`, `skip: 0`, `limit: 16` | Filter kategori smartphone. DummyJSON memakai rute hierarkis, sedangkan modul menggunakan query string `GET /products?category=smartphones` yang memungkinkan filter multi-kategori dan kombinasi kriteria. |
| 6 | `GET /products/999999` | `404 Not Found` | Path parameter `{id}` | `{"message": "Product with id '999999' not found"}` | Menguji penanganan kasus saat ID produk tidak terdaftar. Server mengembalikan status semantik `404 Not Found` dengan pesan deskriptif, selaras dengan kaidah RFC 9110. |

### B. Pertanyaan Analisis & Pembahasan

#### 1. Model pagination apa yang digunakan DummyJSON? Petakan limit/skip ke per_page/page!
- **Model yang digunakan:** DummyJSON menggunakan **Offset/Page-based Pagination** dengan parameter penanda posisi `skip` (offset) dan `limit` (ukuran halaman).
- **Rumus Pemetaan ke Konvensi Modul (`page` dan `per_page`):**
  $$\text{limit} = \text{per\_page}$$
  $$\text{skip} = (\text{page} - 1) \times \text{per\_page}$$
  $$\text{page} = \left\lfloor \frac{\text{skip}}{\text{limit}} \right\rfloor + 1$$

#### 2. DummyJSON memakai `/products/search?q=` dan `/products/category/{name}`. Bandingkan dengan konvensi `?q=` dan `?category=` pada modul ini. Mana yang lebih konsisten dan mengapa?
- **Pilihan yang Lebih Konsisten:** Konvensi pada modul ini (`GET /products?q=...&category=...`) **jauh lebih konsisten, terstandar, dan berorientasi REST**.
- **Alasan Teknis & Arsitektur:**
  1. **Prinsip Endpoint Tunggal (Single Collection Endpoint):** Modul mempertahankan URI `/products` sebagai representasi tunggal koleksi produk. DummyJSON melanggar prinsip kata benda dengan menyisipkan kata kerja `/search` pada URI path.
  2. **Dukungan Kombinasi Parameter (Kombinabilitas Tinggi):** Pada rancangan modul, client dapat menyaring kategori sekaligus mencari teks dan menyaring harga dalam satu panggilan: `GET /products?category=smartphones&q=apple&min_price=500`. Pada DummyJSON, `/search` dan `/category` adalah rute terpisah yang saling meniadakan (*mutually exclusive*), sehingga client tidak dapat melakukan pencarian teks di dalam kategori tertentu.
  3. **Kemudahan Routing Backend:** Satu controller `index(Request $request)` di Laravel dapat menangani seluruh variasi filter secara dinamis tanpa perlu mendaftarkan banyak baris rute terpisah.

#### 3. Metadata apa saja yang tersedia untuk membangun navigasi halaman di client?
- **Metadata yang Disediakan DummyJSON:**
  - `total`: Total keseluruhan record yang cocok di database (misal `194`).
  - `skip`: Posisi baris awal data saat ini (misal `0`, `5`, `10`).
  - `limit`: Jumlah data maksimum yang dikembalikan per halaman (misal `5`).
- **Kalkulasi Navigasi di Sisi Client:**
  - Halaman Saat Ini (*Current Page*): `currentPage = Math.floor(skip / limit) + 1`
  - Total Halaman (*Last Page*): `totalPages = Math.ceil(total / limit)`
  - Tombol Next Aktif: `hasNext = (skip + limit) < total`
  - Tombol Prev Aktif: `hasPrev = skip > 0`
- **Keunggulan Konvensi Modul (Laravel Resource):** Rancangan SportVenue jauh lebih unggul karena menyajikan objek navigasi instan `links` (`first`, `last`, `prev`, `next`) dan objek `meta` (`current_page`, `last_page`, `from`, `to`, `total`), sehingga tim frontend tidak perlu melakukan perhitungan aritmatika offset secara manual.

---

## 22. Kelengkapan API Design Document v1 (Milestone M2 – API Blueprint)

Dokumen ini melengkapi seluruh checklist formal Milestone M2 sesuai arahan rubrik asesmen:

### Bagian E: Desain Representasi Request & Response JSON (Minimal 3 Endpoint)

#### 1. Response Item Tunggal (`GET /api/v1/venues/1`)
```json
HTTP/1.1 200 OK
Content-Type: application/json

{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Arena Garuda Futsal",
    "slug": "arena-garuda-futsal",
    "description": "Gelanggang futsal berstandar nasional lantai vinyl pro.",
    "address": "Jl. Martoloyo No. 12",
    "city": "Tegal",
    "phone": "081234567890",
    "dp_percent": 30,
    "cancel_free_hours": 24,
    "cancel_refund_percent": 50,
    "image_url": "https://sportvenue.test/storage/venues/garuda.jpg",
    "created_at": "2026-10-01T08:00:00+07:00"
  }
}
```

#### 2. Response Item Lapangan Fisik (`GET /api/v1/courts/1`)
```json
HTTP/1.1 200 OK
Content-Type: application/json

{
  "status": "success",
  "data": {
    "id": 1,
    "venue_id": 1,
    "name": "Lapangan Futsal A (Vinyl Pro)",
    "sport_type": "futsal",
    "is_active": true,
    "venue": {
      "id": 1,
      "name": "Arena Garuda Futsal",
      "city": "Tegal"
    },
    "created_at": "2026-10-01T08:30:00+07:00"
  }
}
```

#### 3. Response Koleksi dengan Pagination Links & Meta (`GET /api/v1/venues?page=1&per_page=2`)
```json
HTTP/1.1 200 OK
Content-Type: application/json

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
    "from": 1,
    "to": 2,
    "total": 10,
    "last_page": 5
  }
}
```

#### 4. Request & Response Pembuatan Booking (`POST /api/v1/bookings`)
```json
// Request Payload Client
POST /api/v1/bookings
Authorization: Bearer 1|qX8z9P...
Content-Type: application/json

{
  "court_id": 1,
  "slots": ["2026-10-25 09:00:00", "2026-10-25 10:00:00"],
  "notes": "Latihan tanding komunitas futsal"
}

// Response Sukses
HTTP/1.1 201 Created
Location: /api/v1/bookings/45
Content-Type: application/json

{
  "status": "success",
  "message": "Booking berhasil dibuat. Slot Anda terkunci (HOLD) selama 15 menit.",
  "data": {
    "id": 45,
    "code": "BK-20261021-9988A",
    "court_id": 1,
    "total_price": 240000.00,
    "dp_amount": 72000.00,
    "status": "PENDING_PAYMENT",
    "expires_at": "2026-10-21T19:25:00+07:00",
    "slots": [
      { "slot_start": "2026-10-25T09:00:00+07:00", "slot_end": "2026-10-25T10:00:00+07:00", "price": 120000.00 },
      { "slot_start": "2026-10-25T10:00:00+07:00", "slot_end": "2026-10-25T11:00:00+07:00", "price": 120000.00 }
    ]
  }
}
```

---

### Bagian F: Error & Status Response Terstandar

Setiap response error menggunakan amplop seragam `{ status: "error", message: "...", errors?: { ... } }`:

```json
// 1. Error Validasi Input (422 Unprocessable Content)
HTTP/1.1 422 Unprocessable Content
Content-Type: application/json

{
  "status": "error",
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "slots": ["Minimal satu slot jam operasional harus dipilih."],
    "court_id": ["Lapangan yang dipilih tidak aktif atau tidak ditemukan."]
  }
}

// 2. Error Aturan Bisnis Bentrok Slot (409 Conflict)
HTTP/1.1 409 Conflict
Content-Type: application/json

{
  "status": "error",
  "message": "Satu atau lebih slot jam yang Anda pilih sudah terisi atau sedang di-hold pengguna lain. Silakan pilih jadwal lain.",
  "code": "SLOT_ALREADY_LOCKED"
}
```

#### Kamus Kode Status HTTP Utama:
1. `200 OK`: Pengambilan, pembaruan data, atau aksi kalkulasi berhasil.
2. `201 Created`: Pembuatan resource baru berhasil (disertai header `Location`).
3. `204 No Content`: Penghapusan resource atau logout berhasil tanpa body.
4. `400 Bad Request`: Payload JSON rusak atau sintaks tidak valid (*malformed*).
5. `401 Unauthorized`: Kredensial tidak ada, token expired, atau login salah.
6. `403 Forbidden`: Pengguna login sah, tetapi role/kebijakan melarang aksi tersebut.
7. `404 Not Found`: ID resource tidak ditemukan di sistem database.
8. `409 Conflict`: Bentrok kondisi konkurensi (jadwal slot bertabrakan pada detik bersamaan).
9. `422 Unprocessable Content`: Sintaks JSON benar, tetapi isi melanggar validasi form.
10. `429 Too Many Requests`: Melebihi kuota rate limit (> 60 req/menit).
11. `500 Internal Server Error`: Kesalahan server tak terduga (tanpa ekspos stack trace).
12. `503 Service Unavailable`: Ketergantungan pihak ketiga (Midtrans) sedang gangguan.

---

### Bagian G: Desain Aksi Non-CRUD & Proses Bisnis
1. **Pola Pembatalan Booking (`POST /bookings/{id}/cancellation`):**  
   Menggunakan pola *Sub-resource / Process as Resource*. Pembatalan sewa lapangan bukan sekadar `PATCH status = cancelled`, melainkan mengevaluasi audit trail kebijakan venue (perhitungan sisa waktu H-24/H-12, persentase refund DP yang berhak diterima penyewa, dan pelepasan kunci baris slot di `booking_slots`).
2. **Pola Pembayaran DP Midtrans Snap (`POST /bookings/{id}/payments`):**  
   Pembayaran dimodelkan sebagai sub-resource independen dari booking. Setiap upaya pembayaran memiliki riwayat `order_id`, token Midtrans Snap, status settlement, dan metode pembayaran digital terpisah.
3. **Pola Callback Webhook Idempotent (`POST /payments/webhook/midtrans`):**  
   Callback dari payment gateway divalidasi menggunakan signature key SHA-512. Notifikasi berulang untuk order_id yang sama tidak akan mengubah status pesanan dua kali (*idempotent processing*).

---

### Bagian H: Catatan Keputusan Desain Arsitektur
1. **Dukungan PUT vs PATCH:**  
   Keduanya didukung pada endpoint `/manage/courts/{id}` dan `/manage/venues/{id}`. PUT menggantikan seluruh representasi data lapangan/venue, sedangkan PATCH dipergunakan untuk pembaruan atribut tunggal secara atomik (misalnya mengubah status `is_active`).
2. **Strategi Versioning:**  
   Menggunakan path-based versioning (`/api/v1`) yang transparan, mudah diuji via Postman, dan aman untuk evolusi jangka panjang tanpa merusak client aplikasi mobile maupun web yang sudah berjalan.
3. **Struktur Envelope Data, Meta, dan Links:**  
   Mengadopsi format envelope bawaan Laravel Eloquent API Resource yang memisahkan payload utama (`data`) dari metadata paginasi (`meta`) dan navigasi tautan (`links`).
4. **Batas Limit Pagination (Proteksi OWASP API4):**  
   Setiap endpoint bertipe collection menerapkan nilai default `per_page` antara 10–20 dengan batas maksimum keras (*hard cap*) 50–100 untuk melindungi server dari serangan *Unrestricted Resource Consumption*.
5. **Arsitektur Concurrency Anti Double Booking (§9.2 PRD):**  
   Pencegahan pemesanan ganda dijamin pada lapisan terdalam database melalui MySQL InnoDB Unique Constraint `unique(court_id, slot_start)`. Jika dua transaksi paralel mencoba memesan slot yang sama, transaksi kedua otomatis ditolak oleh database dan sistem merespons dengan HTTP status `409 Conflict`.
