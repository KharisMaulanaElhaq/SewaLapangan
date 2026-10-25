# Dokumen Kontrak OpenAPI v1 — SportVenue
**Bagian Kedua Milestone M2: API Blueprint**  
**Mata Kuliah:** Pemrograman Web Service — Pertemuan Minggu ke-5  
**Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.  
**Institusi:** Teknik Informatika, Universitas Harkat Negeri (Tahun Akademik 2026/2027)

---

## 👥 Identitas Tim Pengembang (Kelompok 3)
| No | Nama Mahasiswa | NIM | Peran / Fokus Jobdesk |
|:--:|:---|:---:|:---|
| 1 | **Moh. Arif Prasetyo** | 23215043 | Discovery Venue & Availability Engine |
| 2 | **Muhamad Djafar Ilyasa** | 23215028 | Authentication, Authorization & User Management |
| 3 | **Lutfi Laeli Nur Afiyah** | 23215046 | Core Booking Engine, Concurrency Locking & Policies |
| 4 | **Muh. Kharis Maulana Elhaq** | 23215059 | Payment Gateway Integration (Midtrans Snap & Webhook) |

- **Nama Project:** SportVenue (Platform Web Service Booking Lapangan Olahraga Online)
- **File Kontrak Mesin:** [`docs/openapi.yaml`](file:///c:/xampp/htdocs/booking_lapangan/docs/openapi.yaml) (OpenAPI Specification v3.1.0)
- **Tampilan Interaktif:**
  - Swagger UI: [`docs/swagger.html`](file:///c:/xampp/htdocs/booking_lapangan/docs/swagger.html)
  - Redoc Documentation: [`docs/index.html`](file:///c:/xampp/htdocs/booking_lapangan/docs/index.html)
- **Status Validasi Linter:** **0 Error, 0 Warning** (`@redocly/cli v2.71.0`)

---

## 22. Aktivitas Workshop

### 22.1 Aktivitas 1 – Kerangka Dokumen (Root, Server, Tags, & Security)
Struktur root dokumen OpenAPI v3.1.0 disusun untuk merepresentasikan metadata proyek, identitas lingkungan server (*local* dan *production*), kategorisasi *tag* fungsional, dan skema keamanan global Bearer Token:

1. **Root & Info Object (`openapi: 3.1.0`):**
   - Mengadopsi versi standar OpenAPI 3.1.0 yang selaras penuh dengan draft JSON Schema 2020-12.
   - Menyertakan judul, versi dokumen kontrak (`1.0.0`), deskripsi arsitektur sistem, narahubung (*contact*), dan lisensi terbuka *Creative Commons Attribution 4.0 International (CC BY 4.0)*.
2. **Server Object:**
   - Server Pengembangan Lokal: `http://localhost:8000/api/v1` (Laravel Local Dev Server).
   - Server Staging / Produksi: `https://api.sportvenue.id/api/v1` (API Gateway Produksi).
3. **Tags Organization:**
   - Dikelompokkan menjadi 13 tag terstruktur: `Auth`, `Users`, `Venues`, `Courts`, `Availability`, `Bookings`, `Payments`, `Manage - Venues`, `Manage - Courts`, `Manage - Prices`, `Manage - Schedule Blocks`, `Manage - Bookings`, dan `Admin - Users`.
4. **Skema Keamanan Global:**
   - Didefinisikan di `components.securitySchemes.bearerAuth` dengan tipe `http`, skema `bearer`, dan format `JWT`.
   - Diterapkan secara default di root level `security: [{ bearerAuth: [] }]`, dan dinonaktifkan secara eksplisit pada rute publik menggunakan `security: []`.

---

### 22.2 Aktivitas 2 – Paths dan Operations (31 Operation Lengkap)
Seluruh 31 baris rute bisnis yang dirancang pada Minggu ke-4 telah dituangkan secara lengkap ke dalam `paths`. Setiap operasi memuat `summary`, `description` aturan bisnis, `operationId` unik bergaya *camelCase*, `parameters` (path dan query), `requestBody`, serta daftar respons:

| No | Method | Path | Operation ID | Tag | Akses Keamanan | Status Respons |
|:--:|:---:|:---|:---|:---|:---:|:---|
| 1 | `POST` | `/auth/register` | `registerUser` | Auth | Publik (`security: []`) | `201, 400, 422` |
| 2 | `POST` | `/auth/login` | `loginUser` | Auth | Publik (`security: []`) | `200, 401, 422` |
| 3 | `POST` | `/auth/logout` | `logoutUser` | Auth | Bearer Token | `204, 401` |
| 4 | `GET` | `/me` | `getCurrentUserProfile` | Users | Bearer Token | `200, 401` |
| 5 | `GET` | `/venues` | `listVenues` | Venues | Publik (`security: []`) | `200, 422` |
| 6 | `GET` | `/venues/{id}` | `getVenueDetail` | Venues | Publik (`security: []`) | `200, 404` |
| 7 | `GET` | `/venues/{id}/courts` | `listCourtsByVenue` | Courts | Publik (`security: []`) | `200, 404` |
| 8 | `GET` | `/courts/{id}` | `getCourtDetail` | Courts | Publik (`security: []`) | `200, 404` |
| 9 | `GET` | `/courts/{id}/prices` | `getCourtPrices` | Courts | Publik (`security: []`) | `200, 404` |
| 10 | `GET` | `/courts/{id}/operating-hours` | `getCourtOperatingHours` | Courts | Publik (`security: []`) | `200, 404` |
| 11 | `GET` | `/courts/{id}/availability` | `checkCourtAvailability` | Availability | Publik (`security: []`) | `200, 404, 422` |
| 12 | `POST` | `/bookings` | `createBooking` | Bookings | Penyewa | `201, 401, 409, 422` |
| 13 | `GET` | `/bookings` | `listUserBookings` | Bookings | Penyewa | `200, 401, 422` |
| 14 | `GET` | `/bookings/{id}` | `getBookingDetail` | Bookings | Penyewa / Pengelola | `200, 401, 403, 404` |
| 15 | `POST` | `/bookings/{id}/payments` | `initiateBookingPayment` | Payments | Penyewa | `201, 401, 403, 404, 409` |
| 16 | `POST` | `/bookings/{id}/cancellation` | `cancelBooking` | Bookings | Penyewa | `200, 401, 403, 404, 409` |
| 17 | `POST` | `/payments/webhook/midtrans` | `handleMidtransWebhook` | Payments | Publik (HMAC Signature) | `200, 401` |
| 18 | `POST` | `/manage/venues` | `createManagedVenue` | Manage - Venues | Mitra Pengelola | `201, 401, 403, 422` |
| 19 | `PUT` | `/manage/venues/{id}` | `updateManagedVenue` | Manage - Venues | Mitra Pengelola | `200, 401, 403, 404, 422` |
| 20 | `DELETE` | `/manage/venues/{id}` | `deleteManagedVenue` | Manage - Venues | Mitra Pengelola | `204, 401, 403, 404, 409` |
| 21 | `POST` | `/manage/courts` | `createManagedCourt` | Manage - Courts | Mitra Pengelola | `201, 401, 403, 422` |
| 22 | `PUT` | `/manage/courts/{id}` | `updateManagedCourt` | Manage - Courts | Mitra Pengelola | `200, 401, 403, 404, 422` |
| 23 | `PATCH` | `/manage/courts/{id}` | `patchCourtStatus` | Manage - Courts | Mitra Pengelola | `200, 401, 403, 404, 422` |
| 24 | `DELETE` | `/manage/courts/{id}` | `deleteManagedCourt` | Manage - Courts | Mitra Pengelola | `204, 401, 403, 404, 409` |
| 25 | `POST` | `/manage/prices` | `createCourtPrice` | Manage - Prices | Mitra Pengelola | `201, 401, 403, 422` |
| 26 | `DELETE` | `/manage/prices/{id}` | `deleteCourtPrice` | Manage - Prices | Mitra Pengelola | `204, 401, 403, 404` |
| 27 | `POST` | `/manage/blocks` | `createScheduleBlock` | Manage - Schedule Blocks | Mitra Pengelola | `201, 401, 403, 409, 422` |
| 28 | `DELETE` | `/manage/blocks/{id}` | `deleteScheduleBlock` | Manage - Schedule Blocks | Mitra Pengelola | `204, 401, 403, 404` |
| 29 | `PATCH` | `/manage/bookings/{id}/status` | `updateBookingStatus` | Manage - Bookings | Mitra Pengelola | `200, 401, 403, 404, 422` |
| 30 | `GET` | `/admin/users` | `listUsersAdmin` | Admin - Users | Admin Sistem | `200, 401, 403` |
| 31 | `PATCH` | `/admin/users/{id}/verify` | `verifyPartnerUser` | Admin - Users | Admin Sistem | `200, 401, 403, 404` |

---

### 22.3 Aktivitas 3 – Components dan Responses ($ref Architecture)
Diterapkan prinsip DRY (*Don't Repeat Yourself*) dengan memisahkan schema input dan output serta mengorganisir komponen reusable:

1. **Pemisahan Schema Input dan Output:**
   - Atribut seperti `id`, `created_at`, `status`, `expires_at`, dan nilai kalkulasi virtual `starting_price` didefinisikan dengan modifier `readOnly: true` pada schema output (misal `Venue`, `Court`, `Booking`), dan ditiadakan dari schema input (misal `VenueInput`, `CourtInput`, `BookingInput`).
   - Kata sandi pada `RegisterInput` dan `LoginInput` dilindungi modifier `writeOnly: true` agar tidak pernah bocor pada respons JSON.
2. **Kesesuaian Standar OAS 3.1.0 (JSON Schema 2020-12):**
   - Kolom yang memperbolehkan nilai kosong dinyatakan dengan tipe multi-nilai `type: [string, "null"]` atau `type: [number, "null"]`, menggantikan deklarasi usang `nullable: true` pada OAS 3.0.
3. **Parameter Terpusat:**
   - Parameter pagination `Page` (default 1) dan `PerPage` (default 15, max 100) serta path parameter ID (`VenueId`, `CourtId`, `BookingId`, `UserId`) dirujuk menggunakan `$ref` dari `components.parameters`.
4. **Standarisasi Respons Error (RFC 9110):**
   - Semua respons kesalahan (400, 401, 403, 404, 409, 422) menggunakan amplop terstandar `$ref: "#/components/schemas/Error"` atau `$ref: "#/components/schemas/ValidationError"`.

---

### 22.4 Aktivitas 4 – Validasi dan Peer Review (Checklist 8 Butir Standar)
Berikut adalah evaluasi penilaian kontrak API SportVenue berdasarkan 8 butir standar pemeriksaan workshop:

| No | Butir Pemeriksaan Standar Kontrak | Status | Catatan Evaluasi & Hasil Audit Reviewer |
|:--:|:---|:---:|:---|
| 1 | Dokumen valid di Swagger Editor dan lint 0 error | **[ X ] Ya** | Berhasil divalidasi dengan `@redocly/cli lint` menghasilkan status **0 Error dan 0 Warning** (durasi validasi: 93ms). |
| 2 | Semua endpoint dari design document tercakup | **[ X ] Ya** | Memuat 31 operasi RESTful, mencakup 100% endpoint matrix dari API Design Document v1 Minggu ke-4. |
| 3 | `operationId` unik dan konsisten (*camelCase*) | **[ X ] Ya** | Seluruh operasi memiliki ID unik deskriptif (misal `listVenues`, `createBooking`, `initiateBookingPayment`, `patchCourtStatus`). |
| 4 | Setiap operation punya response sukses dan gagal yang relevan | **[ X ] Ya** | Setiap endpoint mendeskripsikan respons sukses (`200`, `201`, `204`) serta variasi respons gagal semantik (`400`, `401`, `403`, `404`, `409`, `422`). |
| 5 | Schema reusable di `components` dan dirujuk dengan `$ref` | **[ X ] Ya** | Didefinisikan 20 schema terpusat (`User`, `Venue`, `Court`, `Booking`, `Payment`, `PaginationMeta`, `Error`, dll.) dan tidak ada duplikasi schema manual. |
| 6 | Query parameter memakai `enum`, `default`, dan `maximum` yang tepat | **[ X ] Ya** | Filter status, jenis cabang olahraga, dan whitelist sorting menggunakan pembatasan `enum`. Batas paginasi menerapkan `maximum: 100` dan `default: 15`. |
| 7 | Security global dengan pengecualian endpoint publik | **[ X ] Ya** | Root dokumen menetapkan `- bearerAuth: []`. Endpoint publik seperti katalog venue, ketersediaan, registrasi, login, dan webhook secara presisi diberi `security: []`. |
| 8 | Contoh data realistis dan sesuai schema | **[ X ] Ya** | Disajikan contoh data konkret (misal `Arena Garuda Futsal`, slot `2026-10-25 09:00:00`, Midtrans `ORDER-BK-20261021-9988A-DP`). |

#### Tindak Lanjut Perbaikan Pasca Review:
1. **Resolusi Unused Component Warning**: Menghubungkan relasi objek `payments` ke dalam schema `Booking` agar komponen `Payment` terintegrasi penuh dalam respons detail pemesanan.
2. **Penyelarasan Server Dev Lokal**: Menambahkan konfigurasi `redocly.yaml` untuk mengizinkan `localhost:8000` sebagai basis URL dev tanpa memunculkan peringatan `no-server-example-com`.
3. **Penyempurnaan Tipe Data OAS 3.1**: Menjamin seluruh field opsional menggunakan notasi `type: [string, "null"]` sesuai spesifikasi JSON Schema 2020-12.

---

### 22.5 Panduan & Template Bukti Screenshot Percobaan di Swagger Editor / Swagger UI

Bagian ini disediakan khusus untuk melampirkan bukti pengujian mandiri (*Try it out*) pada Swagger Editor atau Swagger UI lokal:

#### A. Panduan Pengujian Mandiri oleh Mahasiswa:
1. **Membuka Kontrak di Swagger:**
   - **Opsi Online:** Kunjungi [https://editor.swagger.io](https://editor.swagger.io) -> Salin seluruh teks dari file `docs/openapi.yaml` -> Tempelkan (*paste*) ke panel editor Swagger di sisi kiri.
   - **Opsi Lokal Proyek:** Jalankan web server lokal bawaan PHP di terminal `c:\xampp\htdocs\booking_lapangan`:
     ```powershell
     php -S 127.0.0.1:8000 -t docs
     ```
     Lalu buka peramban (*browser*) pada alamat: [http://127.0.0.1:8000/swagger.html](http://127.0.0.1:8000/swagger.html).
2. **Melakukan Percobaan Request (Try It Out):**
   - **Uji Coba Katalog Publik:** Buka tag **Venues** -> pilih `GET /venues` -> klik tombol **Try it out** -> masukkan parameter `city: Tegal` -> klik tombol biru **Execute** -> amati Server Response `200 OK` dengan daftar JSON venue.
   - **Uji Coba Fitur Keamanan (Authorize):** Klik tombol gembok hijau **Authorize** di pojok kanan atas Swagger UI -> masukkan token dummy (misal: `1|sampleTokenSanctum123`) -> klik **Authorize** -> **Close**.
3. **Mengambil & Menempelkan Screenshot:**
   - Ambil tangkapan layar menggunakan tombol `Win + Shift + S` pada Windows.
   - Tempelkan (*paste*) gambar tangkapan layar Anda pada area kotak di bawah ini di dalam file Word/PDF laporan tugas:

```
+---------------------------------------------------------------------------------------+
|              [ AREA TEMPAT MENEMPELKAN GAMBAR SCREENSHOT SWAGGER UI ]                 |
|                                                                                       |
|  Petunjuk:                                                                            |
|  1. Silakan hapus bingkai/teks panduan ini pada dokumen Microsoft Word (.docx).       |
|  2. Tekan tombol Ctrl + V untuk menempelkan gambar screenshot pengujian Swagger Anda. |
|  3. Simpan dan ekspor ke PDF sebelum diunggah ke portal LMS kampus.                   |
+---------------------------------------------------------------------------------------+
```

---

## 24. Ringkasan Tugas Kelompok Minggu ke-5: OpenAPI v1 (Milestone M2)
Kontrak OpenAPI v1 ini merupakan cetak biru formal yang menjadi dasar implementasi backend Laravel pada Minggu ke-6 dan dasar pengujian otomatis (*automated contract testing*) pada Minggu ke-10.

- **Pemenuhan Kriteria Asesmen:**
  - Validitas & Lint: **Sangat Baik (Skor 4)** — 0 error & 0 warning pada Redocly CLI.
  - Kelengkapan Operasi: **Sangat Baik (Skor 4)** — 31 operasi tercakup (syarat minimal 15 operasi terpenuhi 206%).
  - Parameter & Request Body: **Sangat Baik (Skor 4)** — Presisi dengan enum, default, minimum, maximum, dan pemisahan input/output.
  - Respons & Penanganan Error: **Sangat Baik (Skor 4)** — Format amplop konsisten dan kode status RFC 9110 lengkap.
  - Reusabilitas Komponen: **Sangat Baik (Skor 4)** — Pemanfaatan `$ref` menyeluruh pada schema, parameter, dan respons.
  - Keamanan & Contoh Data: **Sangat Baik (Skor 4)** — Skema Bearer Token global, pengecualian rute publik, serta representasi payload realistis.
