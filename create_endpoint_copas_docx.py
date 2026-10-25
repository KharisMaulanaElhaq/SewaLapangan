import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import parse_xml
from docx.oxml.ns import nsdecls

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def add_code_box(doc, code_text):
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table.rows[0].cells[0]
    set_cell_background(cell, "F8FAFC")
    set_cell_margins(cell, top=120, bottom=120, left=150, right=150)
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after = Pt(0)
    p.paragraph_format.line_spacing = 1.05
    run = p.add_run(code_text)
    run.font.name = "Consolas"
    run.font.size = Pt(8)
    run.font.color.rgb = RGBColor(15, 23, 42)
    doc.add_paragraph() # spacer

def build_copas_docx(filename):
    doc = docx.Document()

    for section in doc.sections:
        section.top_margin = Inches(0.6)
        section.bottom_margin = Inches(0.6)
        section.left_margin = Inches(0.6)
        section.right_margin = Inches(0.6)
        
        # Header
        header = section.header
        hp = header.paragraphs[0]
        hp.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        hrun = hp.add_run("SPORTVENUE • SNIPPET ENDPOINT SWAGGER (SIAP COPAS)")
        hrun.font.name = "Arial"
        hrun.font.size = Pt(8)
        hrun.font.color.rgb = RGBColor(100, 116, 139)

        # Footer
        footer = section.footer
        fp = footer.paragraphs[0]
        fp.alignment = WD_ALIGN_PARAGRAPH.LEFT
        frun = fp.add_run("Kelompok 3 • Pemrograman Web Service (Minggu ke-5)")
        frun.font.name = "Arial"
        frun.font.size = Pt(8)
        frun.font.color.rgb = RGBColor(100, 116, 139)

    # Title
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_title = p_title.add_run("KOLEKSI SNIPPET OPENAPI v1 — SIAP COPAS KE SWAGGER\n")
    r_title.bold = True
    r_title.font.name = "Arial"
    r_title.font.size = Pt(13)
    r_title.font.color.rgb = RGBColor(30, 58, 138)

    r_sub = p_title.add_run("Panduan Potongan Kode YAML Per Endpoint untuk Uji Coba Mandiri di Swagger Editor")
    r_sub.font.name = "Arial"
    r_sub.font.size = Pt(9.5)
    r_sub.font.color.rgb = RGBColor(13, 148, 136)

    # Instruction Card
    intro_table = doc.add_table(rows=1, cols=1)
    intro_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    c_intro = intro_table.rows[0].cells[0]
    set_cell_background(c_intro, "EFF6FF")
    set_cell_margins(c_intro, top=100, bottom=100, left=150, right=150)
    p_in = c_intro.paragraphs[0]
    r_in = p_in.add_run(
        "💡 PETUNJUK PENGGUNAAN:\n"
        "1. Dokumen ini memisahkan potongan kode YAML per endpoint agar Anda tinggal menyalin (copy) dan menempelkannya (paste) ke Swagger Editor (https://editor.swagger.io).\n"
        "2. Jika ingin mencoba Point 22.1 (Kerangka Dokumen & Screenshot), langsung salin KODE LENGKAP PADA BAGIAN 1 di bawah. Kode tersebut langsung mandiri, valid 100%, dan memunculkan tombol Authorize serta 13 Tag!\n"
        "3. Jika ingin menambahkan endpoint satu per satu ke dokumen yang sudah ada, salin dari BAGIAN 2 dan pastikan indentasinya berada sejajar di bawah `paths:`."
    )
    r_in.font.name = "Arial"
    r_in.font.size = Pt(8.5)
    r_in.font.color.rgb = RGBColor(30, 58, 138)

    doc.add_paragraph()

    # =========================================================
    # BAGIAN 1: STARTER PACK POINT 22.1 (LENGKAP MANDIRI)
    # =========================================================
    h1 = doc.add_paragraph()
    r = h1.add_run("BAGIAN 1: STARTER PACK POINT 22.1 (LENGKAP & MANDIRI SIAP TEST)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    p_desc1 = doc.add_paragraph()
    r = p_desc1.add_run(
        "Salin seluruh blok kode di bawah ini, lalu buka https://editor.swagger.io (File -> Clear editor) "
        "dan tempelkan (Ctrl + V). Swagger akan langsung menampilkan antarmuka SportVenue lengkap dengan 13 Tag "
        "dan tombol Authorize. Anda dapat langsung mengambil screenshot untuk tugas Point 22.1!"
    )
    r.font.name = "Arial"
    r.font.size = Pt(8.5)

    code_starter_221 = """openapi: 3.1.0
info:
  title: SportVenue Core Booking API
  version: 1.0.0
  description: |
    # SportVenue RESTful API Specification (v1)
    Kontrak API resmi platform penyewaan lapangan olahraga (SportVenue).
    Disusun berdasarkan prinsip API-first sebagai single source of truth.
  contact:
    name: Kelompok 3 - Pemrograman Web Service
    email: kel3-webservice@kampus.id
  license:
    name: CC BY 4.0
    url: https://creativecommons.org/licenses/by/4.0/

servers:
  - url: http://localhost:8000/api/v1
    description: Server Pengembangan Lokal (Laravel API Server)
  - url: https://api.sportvenue.id/api/v1
    description: Server Produksi / Staging Gateway

tags:
  - name: Auth
    description: Autentikasi pengguna, registrasi akun, dan login Bearer token.
  - name: Users
    description: Profil akun pengguna mandiri (Singleton /me).
  - name: Venues
    description: Katalog venue publik dan pencarian fasilitas lapangan.
  - name: Courts
    description: Spesifikasi lapangan olahraga fisik.
  - name: Availability
    description: Matriks ketersediaan slot jam dan harga per tanggal.
  - name: Bookings
    description: Reservasi sewa lapangan olahraga dan pembatalan refund.
  - name: Payments
    description: Inisialisasi token pembayaran Midtrans Snap.
  - name: Manage - Venues
    description: Operasional mitra pengelola venue.
  - name: Manage - Courts
    description: Operasional mitra pengelola lapangan.
  - name: Manage - Prices
    description: Konfigurasi tarif sewa lapangan.
  - name: Manage - Schedule Blocks
    description: Pemblokiran jadwal turnamen dan perawatan.
  - name: Manage - Bookings
    description: Verifikasi kehadiran pelanggan di lokasi.
  - name: Admin - Users
    description: Hak akses administrator sistem.

security:
  - bearerAuth: []

paths:
  /venues:
    get:
      tags: [Venues]
      summary: Katalog venue olahraga publik (Endpoint Publik)
      description: Menampilkan daftar venue olahraga yang aktif. Bebas diakses tanpa login.
      operationId: listVenues
      security: []
      parameters:
        - name: city
          in: query
          description: Filter kota
          schema:
            type: string
            example: Tegal
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          schema:
            type: integer
            default: 15
      responses:
        "200":
          description: Daftar venue berhasil diambil
          content:
            application/json:
              schema:
                type: object
                properties:
                  status: { type: string, example: success }
                  data:
                    type: array
                    items:
                      $ref: "#/components/schemas/VenueSummary"

  /me:
    get:
      tags: [Users]
      summary: Ambil profil pengguna aktif (Endpoint Terproteksi)
      description: Membutuhkan Bearer Token. Uji coba dengan mengisi token di tombol Authorize di pojok kanan atas.
      operationId: getCurrentUserProfile
      responses:
        "200":
          description: Profil berhasil diambil
          content:
            application/json:
              schema:
                type: object
                properties:
                  status: { type: string, example: success }
                  name: { type: string, example: Moh. Kharis Maulana Elhaq }
                  email: { type: string, example: kharis@sportvenue.test }
                  role: { type: string, example: penyewa }
        "401":
          description: Sesi tidak valid / Token tidak disertakan

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
      description: Masukkan token otentikasi login dalam format `Bearer <token>`

  schemas:
    VenueSummary:
      type: object
      properties:
        id: { type: integer, example: 1 }
        name: { type: string, example: Arena Garuda Futsal }
        city: { type: string, example: Tegal }
        starting_price: { type: number, example: 120000 }"""

    add_code_box(doc, code_starter_221)

    # =========================================================
    # BAGIAN 2: SNIPPET ENDPOINT TERPISAH (UNTUK DITEMPEL DI PATHS)
    # =========================================================
    h2 = doc.add_paragraph()
    r = h2.add_run("BAGIAN 2: SNIPPET POTONGAN ENDPOINT TERPISAH (UNDER paths:)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    p_desc2 = doc.add_paragraph()
    r = p_desc2.add_run(
        "Di bawah ini adalah potongan kode YAML per endpoint yang dapat Anda salin secara terpisah. "
        "Pastikan saat menempelkan ke file openapi.yaml Anda, posisinya berada di bawah blok `paths:` "
        "dengan indentasi 2 spasi dari tepi kiri."
    )
    r.font.name = "Arial"
    r.font.size = Pt(8.5)

    # 1. POST /auth/register
    p_ep1 = doc.add_paragraph()
    r = p_ep1.add_run("1. Endpoint Registrasi Pengguna Baru (POST /auth/register)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_reg = """  /auth/register:
    post:
      tags: [Auth]
      summary: Registrasi pengguna baru
      operationId: registerUser
      security: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [name, email, phone, password, role]
              properties:
                name: { type: string, example: Moh. Kharis Maulana Elhaq }
                email: { type: string, format: email, example: kharis@sportvenue.test }
                phone: { type: string, example: "081234567890" }
                password: { type: string, format: password, minLength: 8, writeOnly: true, example: SecretPass123! }
                role: { type: string, enum: [penyewa, pengelola], example: penyewa }
      responses:
        "201":
          description: Akun pengguna berhasil didaftarkan
        "422":
          description: Validasi input form gagal"""
    add_code_box(doc, code_reg)

    # 2. POST /auth/login
    p_ep2 = doc.add_paragraph()
    r = p_ep2.add_run("2. Endpoint Login & Penerbitan Token (POST /auth/login)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_login = """  /auth/login:
    post:
      tags: [Auth]
      summary: Autentikasi pengguna dan penerbitan token
      operationId: loginUser
      security: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [email, password]
              properties:
                email: { type: string, format: email, example: kharis@sportvenue.test }
                password: { type: string, format: password, writeOnly: true, example: SecretPass123! }
      responses:
        "200":
          description: Login berhasil, token dikembalikan
          content:
            application/json:
              schema:
                type: object
                properties:
                  status: { type: string, example: success }
                  token: { type: string, example: 1|qX8z9P0L2jR4vK6wM8nB7vC5xZ3aS1dF }
        "401":
          description: Email atau kata sandi tidak cocok"""
    add_code_box(doc, code_login)

    # 3. GET /venues/{id}
    p_ep3 = doc.add_paragraph()
    r = p_ep3.add_run("3. Endpoint Detail Profil Venue (GET /venues/{id})")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_venue_detail = """  /venues/{id}:
    get:
      tags: [Venues]
      summary: Detail lengkap profil venue
      operationId: getVenueDetail
      security: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
            example: 1
      responses:
        "200":
          description: Detail venue berhasil ditemukan
        "404":
          description: Venue tidak ditemukan"""
    add_code_box(doc, code_venue_detail)

    # 4. GET /courts/{id}/availability
    p_ep4 = doc.add_paragraph()
    r = p_ep4.add_run("4. Endpoint Matriks Ketersediaan Slot Sewa (GET /courts/{id}/availability)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_avail = """  /courts/{id}/availability:
    get:
      tags: [Availability]
      summary: Matriks ketersediaan slot sewa per tanggal
      operationId: checkCourtAvailability
      security: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
            example: 1
        - name: play_date
          in: query
          required: true
          description: Tanggal bermain (YYYY-MM-DD)
          schema:
            type: string
            format: date
            example: "2026-10-25"
      responses:
        "200":
          description: Matriks ketersediaan slot berhasil dikalkulasi
          content:
            application/json:
              schema:
                type: object
                properties:
                  court_id: { type: integer, example: 1 }
                  play_date: { type: string, example: "2026-10-25" }
                  slots:
                    type: array
                    items:
                      type: object
                      properties:
                        slot_start: { type: string, example: "09:00" }
                        slot_end: { type: string, example: "10:00" }
                        status: { type: string, enum: [AVAILABLE, HOLD, BOOKED, BLOCKED], example: AVAILABLE }
                        price: { type: number, example: 120000 }"""
    add_code_box(doc, code_avail)

    # 5. POST /bookings
    p_ep5 = doc.add_paragraph()
    r = p_ep5.add_run("5. Endpoint Pembuatan Booking Baru / Hold Slot (POST /bookings)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_booking_post = """  /bookings:
    post:
      tags: [Bookings]
      summary: Buat transaksi booking baru (Hold Slot Atomik 15 Menit)
      operationId: createBooking
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [court_id, slots]
              properties:
                court_id: { type: integer, example: 1 }
                slots:
                  type: array
                  items:
                    type: string
                    example: "2026-10-25 09:00:00"
                notes: { type: string, example: Latihan tanding komunitas futsal }
      responses:
        "201":
          description: Booking berhasil dibuat, slot terkunci sementara (HOLD)
          headers:
            Location:
              schema: { type: string, example: /api/v1/bookings/45 }
        "401":
          description: Belum terotentikasi (wajib login penyewa)
        "409":
          description: Slot waktu bentrok atau sudah di-hold pemesan lain
        "422":
          description: Validasi input data tidak valid"""
    add_code_box(doc, code_booking_post)

    # 6. POST /bookings/{id}/payments
    p_ep6 = doc.add_paragraph()
    r = p_ep6.add_run("6. Endpoint Inisialisasi Pembayaran Midtrans Snap (POST /bookings/{id}/payments)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_payment = """  /bookings/{id}/payments:
    post:
      tags: [Payments]
      summary: Inisialisasi token pembayaran Midtrans Snap
      operationId: initiateBookingPayment
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
            example: 45
      responses:
        "201":
          description: Snap token Midtrans berhasil dibuat
          content:
            application/json:
              schema:
                type: object
                properties:
                  status: { type: string, example: success }
                  snap_token: { type: string, example: "083c27be-6831-4ce8-8b96-b6f13e7102e1" }
                  redirect_url: { type: string, example: "https://app.sandbox.midtrans.com/snap/v2/vtweb/083c27be..." }
        "401":
          description: Unauthorized
        "409":
          description: Booking sudah kadaluarsa atau sudah lunas"""
    add_code_box(doc, code_payment)

    # 7. POST /payments/webhook/midtrans
    p_ep7 = doc.add_paragraph()
    r = p_ep7.add_run("7. Endpoint Webhook Midtrans Idempotent (POST /payments/webhook/midtrans)")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_webhook = """  /payments/webhook/midtrans:
    post:
      tags: [Payments]
      summary: Callback notifikasi pembayaran dari Midtrans (Idempotent)
      operationId: handleMidtransWebhook
      security: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [order_id, transaction_status, gross_amount, signature_key]
              properties:
                order_id: { type: string, example: ORDER-BK-20261021-9988A-DP }
                transaction_status: { type: string, enum: [settlement, pending, expire, cancel], example: settlement }
                gross_amount: { type: string, example: "72000.00" }
                signature_key: { type: string, example: e15e8b3e8c9735d1db977824... }
      responses:
        "200":
          description: Notifikasi webhook berhasil diproses secara idempoten
        "401":
          description: Checksum signature key SHA-512 tidak valid"""
    add_code_box(doc, code_webhook)

    # 8. PATCH /manage/courts/{id}
    p_ep8 = doc.add_paragraph()
    r = p_ep8.add_run("8. Endpoint Toggle Status Keaktifan Lapangan (PATCH /manage/courts/{id})")
    r.bold = True
    r.font.name = "Arial"
    r.font.size = Pt(9.5)
    r.font.color.rgb = RGBColor(15, 118, 110)

    code_patch = """  /manage/courts/{id}:
    patch:
      tags: [Manage - Courts]
      summary: Toggle status keaktifan lapangan fisik (Parsial)
      operationId: patchCourtStatus
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
            example: 1
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [is_active]
              properties:
                is_active: { type: boolean, example: false }
      responses:
        "200":
          description: Status lapangan berhasil diubah
        "401":
          description: Unauthenticated
        "403":
          description: Forbidden (khusus mitra pengelola pemilik venue)"""
    add_code_box(doc, code_patch)

    doc.save(filename)
    print(f"File DOCX successfully generated: {filename}")

if __name__ == "__main__":
    out_docx = os.path.join(os.path.dirname(__file__), "Endpoint_Swagger_Siap_Copas.docx")
    build_copas_docx(out_docx)
