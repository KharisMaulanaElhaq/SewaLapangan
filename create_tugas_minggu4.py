import os
import sys
import re
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import parse_xml
from docx.oxml.ns import nsdecls

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_header_footer(num_pages)
            super().showPage()
        super().save()

    def draw_header_footer(self, page_count):
        self.saveState()
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(colors.HexColor("#1E3A8A"))
        
        # Header (Pages > 1)
        if self._pageNumber > 1:
            self.drawString(36, 810, "UNIVERSITAS HARKAT NEGERI • PEMROGRAMAN WEB SERVICE (MINGGU KE-4)")
            self.setFont("Helvetica", 8)
            self.setFillColor(colors.HexColor("#64748B"))
            self.drawRightString(559, 810, "SportVenue — REST API Design Document v1 (Milestone M2)")
            self.setStrokeColor(colors.HexColor("#CBD5E1"))
            self.setLineWidth(0.6)
            self.line(36, 804, 559, 804)
        
        # Footer (All pages)
        self.setStrokeColor(colors.HexColor("#CBD5E1"))
        self.setLineWidth(0.6)
        self.line(36, 40, 559, 40)
        
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748B"))
        self.drawString(36, 28, "SportVenue Kelompok 3 — Dokumen Desain REST API, Koreksi ERD & Panduan Status Code")
        page_str = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(559, 28, page_str)
        self.restoreState()

def build_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=A4,
        leftMargin=36,
        rightMargin=36,
        topMargin=46,
        bottomMargin=46
    )

    styles = getSampleStyleSheet()
    
    NAVY = colors.HexColor("#1E3A8A")
    DARK_SLATE = colors.HexColor("#0F172A")
    TEXT_COLOR = colors.HexColor("#1E293B")
    BG_LIGHT = colors.HexColor("#F8FAFC")
    BG_HEADER = colors.HexColor("#1E3A8A")
    BORDER_COLOR = colors.HexColor("#CBD5E1")

    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=16.5,
        textColor=NAVY,
        spaceAfter=3
    )
    
    subtitle_style = ParagraphStyle(
        'DocSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor("#475569"),
        spaceAfter=6
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Heading1'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=13,
        textColor=NAVY,
        spaceBefore=4,
        spaceAfter=3
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.5,
        leading=10.5,
        textColor=TEXT_COLOR,
        spaceAfter=3
    )

    table_header = ParagraphStyle(
        'TH',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7,
        leading=8.8,
        textColor=colors.white,
        alignment=1
    )

    table_cell = ParagraphStyle(
        'TD',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=6.8,
        leading=8.8,
        textColor=TEXT_COLOR
    )

    table_cell_center = ParagraphStyle(
        'TDC',
        parent=table_cell,
        alignment=1
    )

    table_cell_bold = ParagraphStyle(
        'TDB',
        parent=table_cell,
        fontName='Helvetica-Bold'
    )

    code_style = ParagraphStyle(
        'CodeStyle',
        parent=styles['Normal'],
        fontName='Courier',
        fontSize=6.5,
        leading=8.2,
        textColor=colors.HexColor("#0F172A")
    )

    story = []

    # ==========================================
    # HALAMAN 1: IDENTITAS & 20.1 AKTIVITAS 1
    # ==========================================
    story.append(Paragraph("LAPORAN WORKSHOP & DOKUMEN RANCANGAN REST API (v1)", title_style))
    story.append(Paragraph("<b>Mata Kuliah:</b> Pemrograman Web Service (Minggu ke-4) &nbsp;|&nbsp; <b>Dosen Pengampu:</b> Zaenul Arif, S.Kom., M.Kom.<br/>"
                           "<b>Topik:</b> REST API Design: Resource, Endpoint, CRUD, Filtering, Pagination &nbsp;|&nbsp; <b>Milestone:</b> M2 – API Blueprint<br/>"
                           "<b>Platform:</b> SportVenue — Platform Web Service Booking Lapangan Olahraga Online", subtitle_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=NAVY, spaceAfter=5))

    meta_data = [
        [
            Paragraph("<b>Identitas Kelompok:</b>", table_cell_bold),
            Paragraph("<b>Kelompok 3 (Kelas TI - Universitas Harkat Negeri 2026/2027)</b>", table_cell),
            Paragraph("<b>Base URL API:</b>", table_cell_bold),
            Paragraph("<code>http://localhost:8000/api/v1</code>", table_cell)
        ],
        [
            Paragraph("<b>Anggota & Peran:</b>", table_cell_bold),
            Paragraph("1. Moh. Arif Prasetyo (23215043) — <i>Availability Engine & Discovery</i><br/>"
                      "2. Muhamad Djafar Ilyasa (23215028) — <i>Auth & User Security</i><br/>"
                      "3. Lutfi Laeli Nur Afiyah (23215046) — <i>Core Booking & Concurrency Lock</i><br/>"
                      "4. Muh. Kharis Maulana Elhaq (23215059) — <i>Payment Gateway & Webhook</i>", table_cell),
            Paragraph("<b>Spesifikasi Desain:</b>", table_cell_bold),
            Paragraph("• Standar: RESTful JSON API<br/>"
                      "• Autentikasi: Sanctum Bearer Token<br/>"
                      "• Versioning: Path-based (<code>/api/v1</code>)<br/>"
                      "• Konvensi: English (Plural, Kebab-case)", table_cell)
        ]
    ]
    t_meta = Table(meta_data, colWidths=[88, 202, 80, 153])
    t_meta.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), BG_LIGHT),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(t_meta)
    story.append(Spacer(1, 5))

    # 20.1 Aktivitas 1
    story.append(Paragraph("20.1 Aktivitas 1 – Pemodelan Resource (Tersinkronisasi Penuh dengan ERD)", h1_style))
    story.append(Paragraph("Resource diturunkan secara presisi dari entitas bisnis SportVenue (Modul Ajar Point 9.3: <i>Resource tidak harus sama dengan tabel</i>). Kolom fisik database dan atribut virtual turunan telah diselaraskan 100% dengan ERD aplikasi:", body_style))

    res_headers = [
        Paragraph("No", table_header),
        Paragraph("Resource URI (Jamak)", table_header),
        Paragraph("Tabel DB", table_header),
        Paragraph("Atribut Fisik Database & Field Turunan", table_header),
        Paragraph("Relasi Antar Resource", table_header),
        Paragraph("Jenis Resource", table_header)
    ]
    res_rows = [
        res_headers,
        [Paragraph("1", table_cell_center), Paragraph("<b>users</b>", table_cell), Paragraph("<code>users</code>", table_cell), Paragraph("id, name, email, phone, password, role, status, email_verified_at, created_at", table_cell), Paragraph("1 : N ke venues (pengelola)<br/>1 : N ke bookings (penyewa)", table_cell), Paragraph("Collection", table_cell_center)],
        [Paragraph("2", table_cell_center), Paragraph("<b>venues</b>", table_cell), Paragraph("<code>venues</code>", table_cell), Paragraph("id, owner_id, name, slug, description, address, city, phone, dp_percent, cancel_free_hours, cancel_refund_percent, image_url", table_cell), Paragraph("N : 1 ke users (owner)<br/>1 : N ke courts", table_cell), Paragraph("Collection", table_cell_center)],
        [Paragraph("3", table_cell_center), Paragraph("<b>courts</b>", table_cell), Paragraph("<code>courts</code>", table_cell), Paragraph("id, venue_id, name, sport_type, is_active, created_at", table_cell), Paragraph("N : 1 ke venues<br/>1 : N ke prices, operating-hours, blocks, bookings", table_cell), Paragraph("Sub-resource /<br/>Collection", table_cell_center)],
        [Paragraph("4", table_cell_center), Paragraph("<b>prices</b>", table_cell), Paragraph("<code>court_prices</code>", table_cell), Paragraph("id, court_id, day_type, start_time, end_time, price, created_at", table_cell), Paragraph("N : 1 ke courts", table_cell), Paragraph("Sub-resource", table_cell_center)],
        [Paragraph("5", table_cell_center), Paragraph("<b>operating-hours</b>", table_cell), Paragraph("<code>operating_hours</code>", table_cell), Paragraph("id, court_id, day_of_week, open_time, close_time, slot_duration_minutes", table_cell), Paragraph("N : 1 ke courts", table_cell), Paragraph("Sub-resource", table_cell_center)],
        [Paragraph("6", table_cell_center), Paragraph("<b>blocks</b>", table_cell), Paragraph("<code>schedule_blocks</code>", table_cell), Paragraph("id, court_id, start_at, end_at, reason, created_at", table_cell), Paragraph("N : 1 ke courts", table_cell), Paragraph("Sub-resource", table_cell_center)],
        [Paragraph("7", table_cell_center), Paragraph("<b>availability</b>", table_cell), Paragraph("<i>(Kalkulasi)</i>", table_cell), Paragraph("court_id, play_date, slots [slot_time, status, price]<br/><i>(Dihasilkan dari jam operasional, blokir, dan slot terisi)</i>", table_cell), Paragraph("Turunan dari courts (kalkulasi slot terisi & blokir)", table_cell), Paragraph("Sub-resource<br/>(Virtual / Read-Only)", table_cell_center)],
        [Paragraph("8", table_cell_center), Paragraph("<b>bookings</b>", table_cell), Paragraph("<code>bookings</code>", table_cell), Paragraph("id, code, user_id, court_id, total_price, dp_amount, status, expires_at, cancelled_at, refund_amount<br/><i>(Tgl main diturunkan dari booking_slots.slot_start)</i>", table_cell), Paragraph("N : 1 ke users, N : 1 ke courts<br/>1 : N ke booking_slots, 1 : N ke payments", table_cell), Paragraph("Collection", table_cell_center)],
        [Paragraph("9", table_cell_center), Paragraph("<b>payments</b>", table_cell), Paragraph("<code>payments</code>", table_cell), Paragraph("id, booking_id, order_id, type, amount, status, payment_type, snap_token, snap_redirect_url, paid_at", table_cell), Paragraph("N : 1 ke bookings", table_cell), Paragraph("Sub-resource /<br/>Collection", table_cell_center)],
        [Paragraph("10", table_cell_center), Paragraph("<b>booking_slots</b>", table_cell), Paragraph("<code>booking_slots</code>", table_cell), Paragraph("id, booking_id, court_id, slot_start, slot_end, price, created_at", table_cell), Paragraph("N : 1 ke bookings<br/>unique(court_id, slot_start)", table_cell), Paragraph("Internal Engine<br/>(Anti Double Booking)", table_cell_center)],
        [Paragraph("11", table_cell_center), Paragraph("<b>me</b>", table_cell), Paragraph("<code>users</code>", table_cell), Paragraph("id, name, email, phone, role, status", table_cell), Paragraph("N/A (merujuk ke pengguna login aktif)", table_cell), Paragraph("Singleton", table_cell_center)],
    ]
    t_res = Table(res_rows, colWidths=[16, 75, 65, 165, 125, 77])
    t_res.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), BG_HEADER),
        ('ALIGN', (0,0), (-1,0), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 1.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1.5),
        ('LEFTPADDING', (0,0), (-1,-1), 3),
        ('RIGHTPADDING', (0,0), (-1,-1), 3),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, BG_LIGHT])
    ]))
    story.append(t_res)
    story.append(Spacer(1, 4))

    erd_box = """+-------------+ 1       N +-------------+ 1       N +-------------+ 1       N +-----------------+
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
+-------------+"""
    
    t_erd = Table([[Paragraph(f"<b>Diagram Relasi Antar-Resource (ERD Ringkas):</b><br/><pre>{erd_box}</pre>", code_style)]], colWidths=[523])
    t_erd.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#F1F5F9")),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 2),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(t_erd)

    note_erd = """<b>Catatan Penyelarasan Atribut Fisik Database vs Field Turunan:</b><br/>
• <b>venues.is_active:</b> Status aktif dikontrol di tingkat lapangan (<code>courts.is_active</code>). Venue tidak memiliki kolom <code>is_active</code>.<br/>
• <b>play_date:</b> Tidak ada kolom <code>play_date</code> di tabel <code>bookings</code>; tanggal main tersimpan di baris slot <code>booking_slots.slot_start</code>.<br/>
• <b>Detail courts:</b> Lapangan hanya memuat <code>name</code>, <code>sport_type</code>, <code>is_active</code>. Foto fasilitas berada di <code>venues.image_url</code>.<br/>
• <b>starting_price:</b> Field virtual dinamis hasil agregasi <code>MIN(court_prices.price)</code>, bukan kolom fisik tabel <code>venues</code>."""
    t_note_erd = Table([[Paragraph(note_erd, body_style)]], colWidths=[523])
    t_note_erd.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#FEF3C7")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#FCD34D")),
        ('TOPPADDING', (0,0), (-1,-1), 2.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2.5),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(Spacer(1, 3))
    story.append(t_note_erd)

    story.append(PageBreak())

    # ==========================================
    # HALAMAN 2: 20.2 AKTIVITAS 2 (ENDPOINT MATRIX)
    # ==========================================
    story.append(Paragraph("20.2 Aktivitas 2 – Endpoint Matrix (20 menit)", h1_style))
    story.append(Paragraph("Disusun 26 endpoint RESTful yang selaras 100% dengan resource dan model database SportVenue. Setiap baris memuat HTTP method semantik, path URI terstandar, fungsi operasi bisnis, aktor pengguna, serta status sukses dan status gagal yang relevan:", body_style))

    ep_headers = [
        Paragraph("No", table_header),
        Paragraph("Method", table_header),
        Paragraph("Endpoint (Base: /api/v1)", table_header),
        Paragraph("Fungsi & Operasi Bisnis", table_header),
        Paragraph("Aktor", table_header),
        Paragraph("Sukses", table_header),
        Paragraph("Status Gagal Relevan", table_header)
    ]
    
    ep_data_raw = [
        ("1", "POST", "/auth/register", "Registrasi pengguna baru (penyewa aktif, pengelola pending)", "Publik", "201 Created", "400, 422"),
        ("2", "POST", "/auth/login", "Autentikasi akun & penerbitan Sanctum Bearer Token", "Publik", "200 OK", "401, 422"),
        ("3", "POST", "/auth/logout", "Revokasi token autentikasi aktif pengguna", "Login", "204 No Content", "401"),
        ("4", "GET", "/me", "Mengambil profil pengguna yang sedang login (singleton)", "Login", "200 OK", "401"),
        ("5", "GET", "/venues", "Katalog venue mitra dengan filter city, sport, min/max price, search", "Publik", "200 OK", "400, 422"),
        ("6", "GET", "/venues/{id}", "Detail satu venue beserta daftar fasilitas lapangan fisiknya", "Publik", "200 OK", "404"),
        ("7", "GET", "/venues/{id}/courts", "Daftar lapangan pada venue tertentu (sub-resource)", "Publik", "200 OK", "404"),
        ("8", "GET", "/courts/{id}", "Detail lapangan fisik (nama, cabang olahraga, status is_active, relasi harga)", "Publik", "200 OK", "404"),
        ("9", "GET", "/courts/{id}/prices", "Daftar struktur harga sewa lapangan (reguler, peak, weekend)", "Publik", "200 OK", "404"),
        ("10", "GET", "/courts/{id}/operating-hours", "Daftar jam buka-tutup operasional lapangan seminggu penuh", "Publik", "200 OK", "404"),
        ("11", "GET", "/courts/{id}/availability", "Matriks ketersediaan slot jam & tarif per tanggal (YYYY-MM-DD)", "Publik", "200 OK", "404, 422"),
        ("12", "POST", "/bookings", "Reservasi booking baru, kunci slot atomik, hold DP 15 menit", "Penyewa", "201 Created", "400, 409, 422"),
        ("13", "GET", "/bookings", "Riwayat transaksi booking milik penyewa yang sedang login", "Penyewa", "200 OK", "401"),
        ("14", "GET", "/bookings/{id}", "Detail faktur booking, slot yang diambil, dan rincian bayar", "Penyewa, Pengelola", "200 OK", "401, 403, 404"),
        ("15", "POST", "/bookings/{id}/payments", "Membuat transaksi DP Midtrans Snap token & redirect URL", "Penyewa", "201 Created", "401, 403, 409"),
        ("16", "POST", "/bookings/{id}/cancellation", "Membatalkan booking & hitung refund otomatis kebijakan venue", "Penyewa", "200 OK", "401, 403, 409"),
        ("17", "POST", "/payments/webhook/midtrans", "Callback webhook notifikasi gateway (verifikasi HMAC SHA-512)", "Midtrans", "200 OK", "401, 403"),
        ("18", "POST", "/manage/venues", "Mendaftarkan venue baru milik mitra pengelola", "Pengelola", "201 Created", "401, 403, 422"),
        ("19", "PUT", "/manage/venues/{id}", "Memperbarui profil venue secara menyeluruh (alamat, kebijakan DP/refund)", "Pengelola", "200 OK", "401, 403, 404, 422"),
        ("20", "DELETE", "/manage/venues/{id}", "Menghapus/menonaktifkan venue dari platform", "Pengelola", "204 No Content", "401, 403, 404, 409"),
        ("21", "POST", "/manage/courts", "Menambah lapangan baru pada venue milik pengelola", "Pengelola", "201 Created", "401, 403, 422"),
        ("22", "PUT", "/manage/courts/{id}", "Memperbarui data lapangan fisik secara menyeluruh", "Pengelola", "200 OK", "401, 403, 404, 422"),
        ("23", "PATCH", "/manage/courts/{id}", "Mengubah status aktif/non-aktif lapangan parsial (is_active)", "Pengelola", "200 OK", "401, 403, 404"),
        ("24", "DELETE", "/manage/courts/{id}", "Menghapus/menonaktifkan lapangan fisik dari sistem", "Pengelola", "204 No Content", "401, 403, 404, 409"),
        ("25", "POST", "/manage/prices", "Menambahkan aturan konfigurasi tarif sewa lapangan", "Pengelola", "201 Created", "401, 403, 422"),
        ("26", "DELETE", "/manage/prices/{id}", "Menghapus aturan konfigurasi tarif sewa", "Pengelola", "204 No Content", "401, 403, 404"),
        ("27", "POST", "/manage/blocks", "Membuat jadwal pemblokiran slot perawatan/turnamen", "Pengelola", "201 Created", "401, 403, 422"),
        ("28", "DELETE", "/manage/blocks/{id}", "Membatalkan jadwal pemblokiran lapangan fisik", "Pengelola", "204 No Content", "401, 403, 404"),
        ("29", "PATCH", "/manage/bookings/{id}/status", "Mengubah status sewa menjadi COMPLETED / NO_SHOW", "Pengelola", "200 OK", "401, 403, 422"),
        ("30", "GET", "/admin/users", "Daftar seluruh akun pengguna platform (filter role & status)", "Admin", "200 OK", "401, 403"),
        ("31", "PATCH", "/admin/users/{id}/verify", "Verifikasi dan aktivasi kemitraan akun pengelola baru", "Admin", "200 OK", "401, 403, 404")
    ]

    ep_rows = [ep_headers]
    for r in ep_data_raw:
        m_color = "#2563EB" if r[1] == "GET" else ("#16A34A" if r[1] == "POST" else ("#D97706" if "P" in r[1] else "#DC2626"))
        m_para = Paragraph(f"<font color='{m_color}'><b>{r[1]}</b></font>", table_cell_center)
        ep_rows.append([
            Paragraph(r[0], table_cell_center),
            m_para,
            Paragraph(f"<code>{r[2]}</code>", table_cell),
            Paragraph(r[3], table_cell),
            Paragraph(r[4], table_cell_center),
            Paragraph(f"<b>{r[5]}</b>", table_cell_center),
            Paragraph(r[6], table_cell)
        ])

    t_ep = Table(ep_rows, colWidths=[16, 38, 134, 175, 48, 56, 56])
    t_ep.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), BG_HEADER),
        ('ALIGN', (0,0), (-1,0), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 1.6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1.6),
        ('LEFTPADDING', (0,0), (-1,-1), 2.5),
        ('RIGHTPADDING', (0,0), (-1,-1), 2.5),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, BG_LIGHT])
    ]))
    story.append(t_ep)

    story.append(PageBreak())

    # ==========================================
    # HALAMAN 3: 20.3 AKTIVITAS 3 & 20.4 AKTIVITAS 4
    # ==========================================
    story.append(Paragraph("20.3 Aktivitas 3 – Desain Query untuk Endpoint Collection (15 menit)", h1_style))
    story.append(Paragraph("Ditetapkan query parameter standar pada 3 endpoint collection utama untuk menyaring (<i>filter</i>), mencari (<i>search</i>), mengurutkan (<i>sort</i>), dan memaginasi (<i>pagination</i>) dengan whitelist yang selaras dengan skema database:", body_style))

    q_headers = [
        Paragraph("Endpoint Collection", table_header),
        Paragraph("Filter (Whitelist)", table_header),
        Paragraph("Search", table_header),
        Paragraph("Sort (Default & Whitelist)", table_header),
        Paragraph("Pagination (Default / Maks)", table_header)
    ]
    q_rows = [
        q_headers,
        [
            Paragraph("<b>GET /venues</b><br/><code>/api/v1/venues</code>", table_cell),
            Paragraph("• <code>city</code> (string)<br/>• <code>sport</code> / <code>sport_type</code> (futsal, badminton, basket, mini_soccer)<br/>• <code>min_price</code>, <code>max_price</code><br/><i>(Filter olahraga & harga dievaluasi via relasi courts.courtPrices)</i>", table_cell),
            Paragraph("<code>q</code><br/>(mencari pada <code>name</code>, <code>address</code>, <code>city</code>)", table_cell),
            Paragraph("• Default: <code>name</code><br/>• Whitelist: <code>name</code>, <code>-name</code>, <code>created_at</code>, <code>-created_at</code>, <code>starting_price*</code>, <code>-starting_price*</code><br/><i>(*Ket: starting_price diurutkan via subquery agregasi MIN(court_prices.price))</i>", table_cell),
            Paragraph("Model: Offset-based<br/>Param: <code>page</code>, <code>per_page</code><br/>• Default: <b>10</b><br/>• Maksimum: <b>50</b>", table_cell)
        ],
        [
            Paragraph("<b>GET /bookings</b><br/><code>/api/v1/bookings</code>", table_cell),
            Paragraph("• <code>status</code> (PENDING_PAYMENT, CONFIRMED, COMPLETED, CANCELLED, EXPIRED, NO_SHOW)<br/>• <code>court_id</code> (integer)<br/>• <code>date_from</code>, <code>date_to</code><br/><i>(Filter rentang tgl dievaluasi via booking_slots.slot_start)</i>", table_cell),
            Paragraph("<code>q</code><br/>(mencari kode booking: <code>code</code>, misal <code>BK-20261011</code>)", table_cell),
            Paragraph("• Default: <code>-created_at</code><br/>• Whitelist: <code>created_at</code>, <code>-created_at</code>, <code>total_price</code>, <code>-total_price</code>, <code>expires_at</code>, <code>-expires_at</code>", table_cell),
            Paragraph("Model: Offset-based<br/>Param: <code>page</code>, <code>per_page</code><br/>• Default: <b>15</b><br/>• Maksimum: <b>100</b>", table_cell)
        ],
        [
            Paragraph("<b>GET /admin/users</b><br/><code>/api/v1/admin/users</code>", table_cell),
            Paragraph("• <code>role</code> (penyewa, pengelola, admin)<br/>• <code>status</code> (active, pending, suspended)", table_cell),
            Paragraph("<code>q</code><br/>(mencari pada <code>name</code>, <code>email</code>, <code>phone</code>)", table_cell),
            Paragraph("• Default: <code>-created_at</code><br/>• Whitelist: <code>name</code>, <code>-name</code>, <code>email</code>, <code>created_at</code>, <code>-created_at</code>", table_cell),
            Paragraph("Model: Offset-based<br/>Param: <code>page</code>, <code>per_page</code><br/>• Default: <b>20</b><br/>• Maksimum: <b>100</b>", table_cell)
        ]
    ]
    t_q = Table(q_rows, colWidths=[90, 130, 80, 125, 98])
    t_q.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), BG_HEADER),
        ('ALIGN', (0,0), (-1,0), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, BG_LIGHT])
    ]))
    story.append(t_q)
    story.append(Spacer(1, 6))

    # 20.4 Aktivitas 4
    story.append(Paragraph("20.4 Aktivitas 4 – Peer Review & Quality Checklist (10 menit)", h1_style))
    story.append(Paragraph("Evaluasi kelayakan kontrak rancangan API SportVenue mengacu pada 8 butir standar industri perancangan RESTful API:", body_style))

    chk_headers = [
        Paragraph("No", table_header),
        Paragraph("Butir Pemeriksaan Kualitas Desain REST API", table_header),
        Paragraph("Status", table_header),
        Paragraph("Catatan Evaluasi Kelompok & Hasil Penilaian", table_header)
    ]
    chk_rows = [
        chk_headers,
        [Paragraph("1", table_cell_center), Paragraph("Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Seluruh endpoint memakai kata benda jamak (<code>/venues</code>, <code>/courts</code>, <code>/prices</code>, <code>/blocks</code>, <code>/bookings</code>, <code>/payments</code>). Tidak ada kata kerja seperti <code>/getVenues</code>.", table_cell)],
        [Paragraph("2", table_cell_center), Paragraph("Prefix <code>/api/v1</code> dipakai konsisten", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Seluruh URI diawali prefix <code>/api/v1</code>, memastikan evolusi API tanpa memutus (<i>breaking</i>) integrasi client lama.", table_cell)],
        [Paragraph("3", table_cell_center), Paragraph("Method sesuai operasi; GET tidak mengubah data", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Operasi GET bersifat <i>safe & idempotent</i> (hanya membaca data). Seluruh mutasi state wajib memakai POST (create), PUT/PATCH (update), atau DELETE.", table_cell)],
        [Paragraph("4", table_cell_center), Paragraph("POST → 201, DELETE → 204, dan status gagal relevan dicantumkan", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Create menghasilkan <code>201 Created</code> disertai header Location; Delete menghasilkan <code>204 No Content</code>. Status gagal (400, 401, 403, 404, 409, 422) didefinisikan lengkap.", table_cell)],
        [Paragraph("5", table_cell_center), Paragraph("Nesting maksimal 2 tingkat", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Nesting dibatasi: <code>/venues/{id}/courts</code>, <code>/courts/{id}/prices</code>. Setelah item memiliki ID unik global, akses dilakukan secara flat: <code>/courts/{id}</code>, <code>/bookings/{id}</code>.", table_cell)],
        [Paragraph("6", table_cell_center), Paragraph("Setiap collection memiliki pagination dengan batas maksimum", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Setiap collection dibatasi default 10–20 dan hard-cap max 50–100 untuk menangkal celah OWASP <i>Unrestricted Resource Consumption</i>.", table_cell)],
        [Paragraph("7", table_cell_center), Paragraph("Filter/sort memakai query parameter dengan whitelist", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Filter dan sort dioperasikan di query string dengan whitelist ketat yang selaras kolom fisik ERD untuk mencegah SQL error atau kebocoran kolom sensitif.", table_cell)],
        [Paragraph("8", table_cell_center), Paragraph("Format error seragam", table_cell), Paragraph("<b>[ X ] Ya</b>", table_cell_center), Paragraph("Seluruh skenario kegagalan memakai amplop format seragam: <code>{ status: 'error', message: '...', errors: { ... } }</code>.", table_cell)],
    ]
    t_chk = Table(chk_rows, colWidths=[20, 185, 65, 253])
    t_chk.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), BG_HEADER),
        ('ALIGN', (0,0), (-1,0), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 2.2),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2.2),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, BG_LIGHT])
    ]))
    story.append(t_chk)
    story.append(Spacer(1, 5))

    action_text = """<b>Tindak Lanjut Perbaikan Pasca Review & Masukan Penyelarasan ERD:</b><br/>
1. <b>Penyelarasan Kolom play_date:</b> Dihapus dari whitelist sort tabel bookings dan dicatat secara transparan sebagai nilai relasional dari <code>booking_slots.slot_start</code>.<br/>
2. <b>Penyelarasan venues.is_active:</b> Dihilangkan dari atribut tabel venues dan filter <code>GET /venues</code>, karena keaktifan diatur di level lapangan <code>courts.is_active</code>.<br/>
3. <b>Deskripsi Detail Lapangan:</b> Disesuaikan tepat dengan kolom fisik <code>courts</code> (nama, cabang olahraga, status aktif); foto berada di <code>venues.image_url</code>.<br/>
4. <b>Klarifikasi starting_price:</b> Dicatat secara eksplisit bahwa <code>starting_price</code> adalah nilai turunan / field virtual agregasi <code>MIN(court_prices.price)</code>.<br/>
5. <b>Validasi & Keamanan:</b> Validasi format <code>YYYY-MM-DD</code> pada availability serta pencegahan OWASP API3 (menghilangkan password/remember_token)."""
    t_action = Table([[Paragraph(action_text, body_style)]], colWidths=[523])
    t_action.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#EFF6FF")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#93C5FD")),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(t_action)

    story.append(PageBreak())

    # ==========================================
    # HALAMAN 4: KAMUS & PANDUAN STATUS CODE HANDLING
    # ==========================================
    story.append(Paragraph("Kamus & Panduan Lengkap HTTP Status Code Handling (RFC 9110)", h1_style))
    story.append(Paragraph("Setiap kode status HTTP yang dipakai dalam perancangan REST API SportVenue memiliki semantik baku, kondisi penggunaan spesifik, dan format body response terstandar:", body_style))

    sc_headers = [
        Paragraph("Kode HTTP", table_header),
        Paragraph("Kategori", table_header),
        Paragraph("Arti & Semantik Standar (RFC 9110)", table_header),
        Paragraph("Konteks Penggunaan pada SportVenue", table_header),
        Paragraph("Karakteristik Body Response", table_header)
    ]
    sc_rows = [
        sc_headers,
        [Paragraph("<b>200 OK</b>", table_cell_center), Paragraph("2xx Success", table_cell_center), Paragraph("Request berhasil diproses dan mengembalikan data.", table_cell), Paragraph("GET katalog venue/court/booking, PUT/PATCH update, POST kalkulasi refund batal.", table_cell), Paragraph("Amplop: <code>{ status: 'success', data: { ... } }</code>", table_cell)],
        [Paragraph("<b>201 Created</b>", table_cell_center), Paragraph("2xx Success", table_cell_center), Paragraph("Resource baru berhasil dibuat di server.", table_cell), Paragraph("POST register user, POST booking baru, POST tambah lapangan, POST token DP Snap.", table_cell), Paragraph("Memuat data baru & header <code>Location: /api/v1/...</code>", table_cell)],
        [Paragraph("<b>204 No Content</b>", table_cell_center), Paragraph("2xx Success", table_cell_center), Paragraph("Operasi tuntas, sengaja tanpa konten response.", table_cell), Paragraph("DELETE lapangan, DELETE jadwal blokir, POST logout revokasi token.", table_cell), Paragraph("<b>Tanpa Body</b> (0 byte payload jaringan)", table_cell)],
        [Paragraph("<b>400 Bad Request</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Sintaks request rusak / gagal diparse.", table_cell), Paragraph("Payload JSON malformed (kurung kurawal tidak tertutup, koma menggantung).", table_cell), Paragraph("Amplop: <code>{ status: 'error', message: 'Malformed JSON' }</code>", table_cell)],
        [Paragraph("<b>401 Unauthorized</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Kredensial autentikasi tidak ada / tidak valid.", table_cell), Paragraph("Akses tanpa token Bearer, token Sanctum expired, kombinasi login salah.", table_cell), Paragraph("Pesan: <code>Unauthenticated / Invalid credentials</code>", table_cell)],
        [Paragraph("<b>403 Forbidden</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Identitas dikenali, tetapi tidak punya izin akses.", table_cell), Paragraph("Penyewa akses <code>/manage/*</code>, edit venue milik orang lain, HMAC webhook palsu.", table_cell), Paragraph("Pesan: <code>Akses ditolak. Tidak berhak atas aksi ini.</code>", table_cell)],
        [Paragraph("<b>404 Not Found</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Resource yang diminta tidak ada di sistem.", table_cell), Paragraph("Akses <code>/venues/999</code> atau <code>/courts/888</code> dengan ID yang tidak terdaftar di DB.", table_cell), Paragraph("Amplop: <code>{ status: 'error', message: 'Not Found' }</code>", table_cell)],
        [Paragraph("<b>409 Conflict</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Bentrok dengan state database saat ini.", table_cell), Paragraph("<b>Anti Double Booking Guard (§9.2):</b> Slot di-hold user lain; booking expired dibatalkan.", table_cell), Paragraph("Amplop: <code>{ code: 'SLOT_ALREADY_LOCKED', ... }</code>", table_cell)],
        [Paragraph("<b>422 Unprocessable</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Sintaks benar, isi melanggar validasi bisnis.", table_cell), Paragraph("Form booking tanpa slot, format tgl bukan YYYY-MM-DD, email duplikat, DP < 10%.", table_cell), Paragraph("Amplop: <code>{ errors: { field: ['detail pesan'] } }</code>", table_cell)],
        [Paragraph("<b>429 Too Many Req</b>", table_cell_center), Paragraph("4xx Client", table_cell_center), Paragraph("Frekuensi request melebihi ambang batas.", table_cell), Paragraph("Spamming booking, brute-force login (> 60 request/menit). Menangkal bot.", table_cell), Paragraph("Header <code>Retry-After: 60</code> + pesan rate limit.", table_cell)],
        [Paragraph("<b>500 Server Error</b>", table_cell_center), Paragraph("5xx Server", table_cell_center), Paragraph("Kegagalan tak terduga pada server backend.", table_cell), Paragraph("Koneksi MySQL terputus, fatal exception runtime unhandled.", table_cell), Paragraph("Pesan umum, <b>tanpa stack trace</b> demi keamanan.", table_cell)],
        [Paragraph("<b>503 Unavailable</b>", table_cell_center), Paragraph("5xx Server", table_cell_center), Paragraph("Layanan upstream/gateway sedang down.", table_cell), Paragraph("API Midtrans payment gateway down/maintenance saat generate token Snap.", table_cell), Paragraph("Pesan petunjuk gateway sibuk, coba kembali.", table_cell)],
    ]
    t_sc = Table(sc_rows, colWidths=[55, 52, 125, 175, 116])
    t_sc.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), BG_HEADER),
        ('ALIGN', (0,0), (-1,0), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 1.6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1.6),
        ('LEFTPADDING', (0,0), (-1,-1), 3),
        ('RIGHTPADDING', (0,0), (-1,-1), 3),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, BG_LIGHT])
    ]))
    story.append(t_sc)
    story.append(Spacer(1, 5))

    sc_analysis = """<b>Prinsip & Analisis Penanganan Status Code Kritis:</b><br/>
1. <b>Mengapa Double Booking menggunakan <code>409 Conflict</code> (bukan 400 atau 422)?</b><br/>
   Request penyewa valid secara sintaks dan tipe data (lolos 422). Namun operasi ditolak karena terjadi tabrakan konkurensi pada indeks unik <code>(court_id, slot_start)</code> di InnoDB. Sesuai RFC 9110, 409 Conflict adalah status paling tepat untuk bentrok state data.<br/>
2. <b>Perbedaan Tegas <code>401 Unauthorized</code> vs <code>403 Forbidden</code>:</b><br/>
   • <b>401 (Autentikasi):</b> Masalah identitas (token tidak ada/kedaluwarsa). Client dapat mengulang request dengan menyertakan token yang valid.<br/>
   • <b>403 (Otorisasi):</b> Identitas sah, tetapi role/policy melarang aksi tersebut (misal penyewa menghapus court). Mengulang token tidak akan berhasil.<br/>
3. <b>Mengapa Validasi Form menghasilkan <code>422 Unprocessable Content</code> (bukan 400)?</b><br/>
   Membedakan kesalahan transport/JSON corrupt (400) dengan kegagalan aturan bisnis domain (422), memudahkan frontend memetakan error per field.<br/>
4. <b>Semantik <code>201 Created</code> vs <code>204 No Content</code>:</b><br/>
   201 wajib menyertakan header <code>Location</code> dan representasi data baru; 204 digunakan saat delete/logout tanpa mengembalikan body."""
    
    t_sc_analysis = Table([[Paragraph(sc_analysis, body_style)]], colWidths=[523])
    t_sc_analysis.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#F0FDF4")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#86EFAC")),
        ('TOPPADDING', (0,0), (-1,-1), 3.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3.5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t_sc_analysis)

    story.append(PageBreak())

    # ==========================================
    # HALAMAN 5: REPRESENTASI, ERROR, NON-CRUD & KEPUTUSAN
    # ==========================================
    story.append(Paragraph("Representasi Response JSON & Format Error Terstandar", h1_style))
    story.append(Paragraph("Format representasi data keluaran Laravel API Resource seragam untuk seluruh endpoint (envelope data, meta, dan links):", body_style))

    json_envelope_item = """// 1. Response Item Tunggal (GET /api/v1/venues/1)
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

// 2. Response Validasi Gagal (422 Unprocessable Content)
{
  "status": "error",
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "slots": ["Minimal satu slot jam harus dipilih."]
  }
}"""

    json_envelope_col = """// 3. Response Koleksi dengan Pagination (GET /venues)
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
    "last": "http://localhost:8000/api/v1/venues?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/v1/venues?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 2,
    "total": 6,
    "last_page": 3
  }
}

// 4. Response Aturan Bisnis Bentrok Slot (409 Conflict)
{
  "status": "error",
  "message": "Satu atau lebih slot sudah terisi atau sedang di-hold. Silakan pilih slot lain.",
  "code": "SLOT_ALREADY_LOCKED"
}"""

    code_table_data = [
        [Paragraph(f"<b>Response Item Tunggal & Error Validasi 422:</b><br/><pre>{json_envelope_item}</pre>", code_style),
         Paragraph(f"<b>Response Koleksi Pagination & Conflict 409:</b><br/><pre>{json_envelope_col}</pre>", code_style)]
    ]
    t_code = Table(code_table_data, colWidths=[260, 263])
    t_code.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#F8FAFC")),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('INNERGRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(t_code)
    story.append(Spacer(1, 8))

    story.append(Paragraph("Pola Aksi Non-CRUD & Catatan Keputusan Desain Arsitektur", h1_style))
    
    decisions_text = """• <b>Aksi Non-CRUD Pembatalan Booking (<code>POST /bookings/{id}/cancellation</code>):</b> Dimodelkan menggunakan pola <i>Sub-resource / Process as Resource</i>. Pembatalan sewa lapangan bukan sekadar <code>PATCH status = cancelled</code>, melainkan memerlukan evaluasi audit trail kebijakan pembatalan venue (perhitungan tenggat waktu H-24/H-12, persentase refund DP, pelepasan kunci baris slot operasional di database, dan notifikasi refund ke penyewa).<br/>
• <b>Aksi Non-CRUD Pembayaran DP (<code>POST /bookings/{id}/payments</code>):</b> Dipisahkan menjadi sub-resource pembayaran karena memiliki entitas data mandiri (order_id Midtrans, status token Snap, redirect URL, dan verifikasi callback webhook idempotent).<br/>
• <b>Dukungan PUT vs PATCH:</b> Keduanya didukung pada endpoint <code>/manage/courts/{id}</code> dan <code>/manage/venues/{id}</code>. PUT menggantikan seluruh representasi data lapangan, sedangkan PATCH dipergunakan untuk pembaruan atribut tunggal secara atomik (misalnya mengubah status aktif/non-aktif lapangan <code>is_active</code>).<br/>
• <b>Strategi Versioning:</b> Menggunakan path-based versioning (<code>/api/v1</code>) yang mudah dipahami, mudah diuji via Postman, dan secara transparan memisahkan kontrak versi lama jika di kemudian hari dilakukan breaking change.<br/>
• <b>Struktur Envelope Data & Pagination:</b> Menggunakan format <code>{ status, data, links, meta }</code> yang selaras dengan keluaran standar Laravel Eloquent API Resource, mempermudah tim frontend/mobile dalam rendering navigasi halaman."""
    
    t_dec = Table([[Paragraph(decisions_text, body_style)]], colWidths=[523])
    t_dec.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), BG_LIGHT),
        ('BOX', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t_dec)

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"Successfully generated 5-page PDF: {filename}")

def build_docx(filename):
    doc = docx.Document()
    
    for section in doc.sections:
        section.top_margin = Inches(0.6)
        section.bottom_margin = Inches(0.6)
        section.left_margin = Inches(0.6)
        section.right_margin = Inches(0.6)

    def set_cell_background(cell, fill_hex):
        tcPr = cell._tc.get_or_add_tcPr()
        shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
        tcPr.append(shd)

    # Title
    p_title = doc.add_paragraph()
    r_title = p_title.add_run("LAPORAN WORKSHOP & DOKUMEN RANCANGAN REST API (v1)")
    r_title.bold = True
    r_title.font.size = Pt(14)
    r_title.font.color.rgb = RGBColor(30, 58, 138)
    p_title.paragraph_format.space_after = Pt(2)

    p_sub = doc.add_paragraph()
    p_sub.add_run("Mata Kuliah: Pemrograman Web Service (Minggu ke-4) | Dosen: Zaenul Arif, S.Kom., M.Kom.\nProyek: SportVenue — Platform Web Service Booking Lapangan Olahraga Online (Milestone M2)").font.size = Pt(9)
    p_sub.paragraph_format.space_after = Pt(8)

    # Identitas Tim
    table_id = doc.add_table(rows=2, cols=4)
    table_id.alignment = WD_TABLE_ALIGNMENT.CENTER
    id_data = [
        [("Identitas Kelompok:", True), ("Kelompok 3 (Kelas TI - Universitas Harkat Negeri 2026/2027)", False),
         ("Base URL API:", True), ("http://localhost:8000/api/v1", False)],
        [("Anggota Kelompok:", True),
         ("1. Moh. Arif Prasetyo (23215043)\n2. Muhamad Djafar Ilyasa (23215028)\n3. Lutfi Laeli Nur Afiyah (23215046)\n4. Muh. Kharis Maulana Elhaq (23215059)", False),
         ("Spesifikasi:", True),
         ("RESTful JSON API\nLaravel 12 · Sanctum Bearer Token\nPath-based Versioning (/api/v1)\nKonvensi: English (Plural)", False)]
    ]
    for row_idx, row in enumerate(id_data):
        for col_idx, (text, is_bold) in enumerate(row):
            cell = table_id.cell(row_idx, col_idx)
            set_cell_background(cell, "F8FAFC")
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(1)
            p.paragraph_format.space_before = Pt(1)
            run = p.add_run(text)
            run.bold = is_bold
            run.font.size = Pt(8)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    # 20.1 Aktivitas 1
    h1 = doc.add_paragraph()
    r = h1.add_run("20.1 Aktivitas 1 – Pemodelan Resource (Koreksi & Sinkronisasi ERD)")
    r.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)
    
    t_res = doc.add_table(rows=12, cols=6)
    t_res.alignment = WD_TABLE_ALIGNMENT.CENTER
    res_data = [
        ["No", "Resource URI", "Tabel DB", "Atribut Utama", "Relasi Antar Entitas", "Jenis Resource"],
        ["1", "users", "users", "id, name, email, phone, role, status, email_verified_at, created_at", "1 : N ke venues (pengelola)\n1 : N ke bookings (penyewa)", "Collection"],
        ["2", "venues", "venues", "id, owner_id, name, slug, description, address, city, phone, dp_percent, cancel_free_hours, cancel_refund_percent, image_url", "N : 1 ke users (owner)\n1 : N ke courts", "Collection"],
        ["3", "courts", "courts", "id, venue_id, name, sport_type, is_active, created_at", "N : 1 ke venues\n1 : N ke prices, operating-hours, blocks, bookings", "Sub-resource / Collection"],
        ["4", "prices", "court_prices", "id, court_id, day_type, start_time, end_time, price, created_at", "N : 1 ke courts", "Sub-resource"],
        ["5", "operating-hours", "operating_hours", "id, court_id, day_of_week, open_time, close_time, slot_duration_minutes", "N : 1 ke courts", "Sub-resource"],
        ["6", "blocks", "schedule_blocks", "id, court_id, start_at, end_at, reason, created_at", "N : 1 ke courts", "Sub-resource"],
        ["7", "availability", "(Kalkulasi)", "court_id, play_date, slots [slot_time, status, price]", "Turunan dari courts (kalkulasi slot & blokir)", "Sub-resource (Virtual / Read-Only)"],
        ["8", "bookings", "bookings", "id, code, user_id, court_id, total_price, dp_amount, status, expires_at, cancelled_at, refund_amount (tgl main via booking_slots)", "N : 1 ke users, N : 1 ke courts\n1 : N ke booking_slots, 1 : N ke payments", "Collection"],
        ["9", "payments", "payments", "id, booking_id, order_id, type, amount, status, payment_type, snap_token, snap_redirect_url, paid_at", "N : 1 ke bookings", "Sub-resource / Collection"],
        ["10", "booking_slots", "booking_slots", "id, booking_id, court_id, slot_start, slot_end, price", "N : 1 ke bookings\nunique(court_id, slot_start)", "Internal Engine (Anti Double Booking)"],
        ["11", "me", "users", "id, name, email, phone, role, status", "N/A (merujuk ke pengguna login)", "Singleton"],
    ]
    for r_idx, row in enumerate(res_data):
        for c_idx, val in enumerate(row):
            cell = t_res.cell(r_idx, c_idx)
            if r_idx == 0:
                set_cell_background(cell, "1E3A8A")
            elif r_idx % 2 == 1:
                set_cell_background(cell, "FFFFFF")
            else:
                set_cell_background(cell, "F8FAFC")
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(1)
            p.paragraph_format.space_before = Pt(1)
            run = p.add_run(val)
            run.font.size = Pt(7)
            if r_idx == 0:
                run.bold = True
                run.font.color.rgb = RGBColor(255, 255, 255)

    doc.add_page_break()

    # 20.2 Aktivitas 2
    h2 = doc.add_paragraph()
    r = h2.add_run("20.2 Aktivitas 2 – Endpoint Matrix (20 menit)")
    r.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    ep_matrix_data = [
        ["No", "Method", "Endpoint (/api/v1)", "Fungsi & Deskripsi Operasi", "Aktor", "Sukses", "Status Gagal Relevan"],
        ["1", "POST", "/auth/register", "Registrasi pengguna baru (penyewa aktif, pengelola pending)", "Publik", "201 Created", "400, 422"],
        ["2", "POST", "/auth/login", "Autentikasi akun & penerbitan token Sanctum", "Publik", "200 OK", "401, 422"],
        ["3", "POST", "/auth/logout", "Revokasi token autentikasi aktif pengguna", "Login", "204 No Content", "401"],
        ["4", "GET", "/me", "Mengambil profil pengguna yang login (singleton)", "Login", "200 OK", "401"],
        ["5", "GET", "/venues", "Katalog venue mitra dengan filter city, sport, min/max price", "Publik", "200 OK", "400, 422"],
        ["6", "GET", "/venues/{id}", "Detail satu venue beserta fasilitas lapangannya", "Publik", "200 OK", "404"],
        ["7", "GET", "/venues/{id}/courts", "Daftar lapangan pada venue tertentu", "Publik", "200 OK", "404"],
        ["8", "GET", "/courts/{id}", "Detail lapangan fisik (nama, cabang olahraga, status is_active)", "Publik", "200 OK", "404"],
        ["9", "GET", "/courts/{id}/prices", "Daftar struktur harga lapangan (reguler, peak, weekend)", "Publik", "200 OK", "404"],
        ["10", "GET", "/courts/{id}/operating-hours", "Daftar jam operasional lapangan seminggu penuh", "Publik", "200 OK", "404"],
        ["11", "GET", "/courts/{id}/availability", "Matriks ketersediaan slot jam & tarif per tanggal", "Publik", "200 OK", "404, 422"],
        ["12", "POST", "/bookings", "Reservasi booking baru, kunci slot atomik, hold 15 menit", "Penyewa", "201 Created", "400, 409, 422"],
        ["13", "GET", "/bookings", "Riwayat transaksi booking milik penyewa aktif", "Penyewa", "200 OK", "401"],
        ["14", "GET", "/bookings/{id}", "Detail faktur booking, slot yang diambil, rincian bayar", "Penyewa, Pengelola", "200 OK", "401, 403, 404"],
        ["15", "POST", "/bookings/{id}/payments", "Membuat token transaksi Midtrans Snap & redirect URL", "Penyewa", "201 Created", "401, 403, 409"],
        ["16", "POST", "/bookings/{id}/cancellation", "Batalkan booking & hitung refund otomatis kebijakan venue", "Penyewa", "200 OK", "401, 403, 409"],
        ["17", "POST", "/payments/webhook/midtrans", "Callback notifikasi gateway (verifikasi HMAC SHA-512)", "Midtrans", "200 OK", "401, 403"],
        ["18", "POST", "/manage/venues", "Mendaftarkan venue baru milik pengelola", "Pengelola", "201 Created", "401, 403, 422"],
        ["19", "PUT", "/manage/venues/{id}", "Memperbarui profil venue secara menyeluruh (alamat, DP/refund)", "Pengelola", "200 OK", "401, 403, 404, 422"],
        ["20", "DELETE", "/manage/venues/{id}", "Menghapus/menonaktifkan venue dari platform", "Pengelola", "204 No Content", "401, 403, 404, 409"],
        ["21", "POST", "/manage/courts", "Menambah lapangan baru pada venue milik pengelola", "Pengelola", "201 Created", "401, 403, 422"],
        ["22", "PUT", "/manage/courts/{id}", "Memperbarui spesifikasi lapangan secara menyeluruh", "Pengelola", "200 OK", "401, 403, 404, 422"],
        ["23", "PATCH", "/manage/courts/{id}", "Mengubah status aktif/non-aktif lapangan parsial (is_active)", "Pengelola", "200 OK", "401, 403, 404"],
        ["24", "DELETE", "/manage/courts/{id}", "Menghapus/menonaktifkan lapangan fisik dari sistem", "Pengelola", "204 No Content", "401, 403, 404, 409"],
        ["25", "POST", "/manage/prices", "Menambahkan aturan konfigurasi tarif sewa", "Pengelola", "201 Created", "401, 403, 422"],
        ["26", "DELETE", "/manage/prices/{id}", "Menghapus aturan konfigurasi tarif sewa", "Pengelola", "204 No Content", "401, 403, 404"],
        ["27", "POST", "/manage/blocks", "Membuat jadwal pemblokiran slot perawatan/turnamen", "Pengelola", "201 Created", "401, 403, 422"],
        ["28", "DELETE", "/manage/blocks/{id}", "Membatalkan jadwal pemblokiran lapangan fisik", "Pengelola", "204 No Content", "401, 403, 404"],
        ["29", "PATCH", "/manage/bookings/{id}/status", "Mengubah status sewa menjadi COMPLETED / NO_SHOW", "Pengelola", "200 OK", "401, 403, 422"],
        ["30", "GET", "/admin/users", "Daftar seluruh akun pengguna platform (filter role & status)", "Admin", "200 OK", "401, 403"],
        ["31", "PATCH", "/admin/users/{id}/verify", "Verifikasi dan aktivasi kemitraan akun pengelola baru", "Admin", "200 OK", "401, 403, 404"]
    ]
    t_ep = doc.add_table(rows=len(ep_matrix_data), cols=7)
    t_ep.alignment = WD_TABLE_ALIGNMENT.CENTER
    for r_idx, row in enumerate(ep_matrix_data):
        for c_idx, val in enumerate(row):
            cell = t_ep.cell(r_idx, c_idx)
            if r_idx == 0:
                set_cell_background(cell, "1E3A8A")
            elif r_idx % 2 == 1:
                set_cell_background(cell, "FFFFFF")
            else:
                set_cell_background(cell, "F8FAFC")
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(1)
            p.paragraph_format.space_before = Pt(1)
            run = p.add_run(val)
            run.font.size = Pt(6.8)
            if r_idx == 0:
                run.bold = True
                run.font.color.rgb = RGBColor(255, 255, 255)

    doc.add_page_break()

    # 20.3 Aktivitas 3
    h3 = doc.add_paragraph()
    r = h3.add_run("20.3 Aktivitas 3 – Desain Query untuk Endpoint Collection (15 menit)")
    r.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    q_data = [
        ["Endpoint Collection", "Filter (Whitelist)", "Search", "Sort (Default & Whitelist)", "Pagination (Default / Maks)"],
        ["GET /api/v1/venues", "city, sport, min_price, max_price (via courts.courtPrices)", "q (name, address, city)", "Default: name\nWhitelist: name, -name, created_at, -created_at, starting_price*, -starting_price* (*agregasi MIN(price))", "Model: Offset-based\nParam: page, per_page\nDefault: 10, Maks: 50"],
        ["GET /api/v1/bookings", "status, court_id, date_from*, date_to* (*via booking_slots.slot_start)", "q (code)", "Default: -created_at\nWhitelist: created_at, -created_at, total_price, -total_price, expires_at, -expires_at", "Model: Offset-based\nParam: page, per_page\nDefault: 15, Maks: 100"],
        ["GET /api/v1/admin/users", "role, status", "q (name, email, phone)", "Default: -created_at\nWhitelist: name, -name, email, created_at, -created_at", "Model: Offset-based\nParam: page, per_page\nDefault: 20, Maks: 100"]
    ]
    t_q = doc.add_table(rows=len(q_data), cols=5)
    t_q.alignment = WD_TABLE_ALIGNMENT.CENTER
    for r_idx, row in enumerate(q_data):
        for c_idx, val in enumerate(row):
            cell = t_q.cell(r_idx, c_idx)
            if r_idx == 0:
                set_cell_background(cell, "1E3A8A")
            elif r_idx % 2 == 1:
                set_cell_background(cell, "FFFFFF")
            else:
                set_cell_background(cell, "F8FAFC")
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(1)
            p.paragraph_format.space_before = Pt(1)
            run = p.add_run(val)
            run.font.size = Pt(7.5)
            if r_idx == 0:
                run.bold = True
                run.font.color.rgb = RGBColor(255, 255, 255)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    # 20.4 Aktivitas 4
    h4 = doc.add_paragraph()
    r = h4.add_run("20.4 Aktivitas 4 – Peer Review & Quality Checklist (10 menit)")
    r.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    chk_data = [
        ["No", "Butir Pemeriksaan Kualitas Desain REST API", "Status", "Catatan Evaluasi Kelompok & Hasil Penilaian"],
        ["1", "Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja", "[ X ] Ya", "Seluruh endpoint memakai kata benda jamak (/venues, /courts, /prices, /blocks, /bookings, /payments). Tidak ada kata kerja seperti /getVenues atau /createBooking."],
        ["2", "Prefix /api/v1 dipakai konsisten", "[ X ] Ya", "Seluruh URI dimulai dengan prefix /api/v1, menjamin evolusi API tanpa memutus (breaking) integrasi client lama."],
        ["3", "Method sesuai operasi; GET tidak mengubah data", "[ X ] Ya", "Operasi GET bersifat safe & idempotent (hanya membaca data). Seluruh mutasi data wajib memakai POST, PUT/PATCH, atau DELETE."],
        ["4", "POST → 201, DELETE → 204, dan status gagal relevan dicantumkan", "[ X ] Ya", "Create menghasilkan 201 Created; Delete menghasilkan 204 No Content. Status gagal (400, 401, 403, 404, 409, 422) didefinisikan eksplisit."],
        ["5", "Nesting maksimal 2 tingkat", "[ X ] Ya", "Nesting dibatasi: /venues/{id}/courts. Setelah item memiliki ID unik global, akses dilakukan secara flat: /courts/{id}, /bookings/{id}."],
        ["6", "Setiap collection memiliki pagination dengan batas maksimum", "[ X ] Ya", "Setiap collection dibatasi default 10–20 dan hard-cap max 50–100 untuk menangkal celah OWASP Unrestricted Resource Consumption."],
        ["7", "Filter/sort memakai query parameter dengan whitelist", "[ X ] Ya", "Filter dan sort dioperasikan di query string dengan whitelist ketat yang selaras kolom fisik ERD untuk mencegah SQL error atau pembocoran kolom sensitif."],
        ["8", "Format error seragam", "[ X ] Ya", "Seluruh skenario kegagalan memakai amplop format seragam: { status: 'error', message: '...', errors: { ... } }."],
    ]
    t_chk = doc.add_table(rows=len(chk_data), cols=4)
    t_chk.alignment = WD_TABLE_ALIGNMENT.CENTER
    for r_idx, row in enumerate(chk_data):
        for c_idx, val in enumerate(row):
            cell = t_chk.cell(r_idx, c_idx)
            if r_idx == 0:
                set_cell_background(cell, "1E3A8A")
            elif r_idx % 2 == 1:
                set_cell_background(cell, "FFFFFF")
            else:
                set_cell_background(cell, "F8FAFC")
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(1)
            p.paragraph_format.space_before = Pt(1)
            run = p.add_run(val)
            run.font.size = Pt(7.5)
            if r_idx == 0:
                run.bold = True
                run.font.color.rgb = RGBColor(255, 255, 255)

    doc.add_page_break()

    # Halaman 4 Docx: STATUS CODE HANDLING
    h_sc = doc.add_paragraph()
    r = h_sc.add_run("Kamus & Panduan Lengkap HTTP Status Code Handling (RFC 9110)")
    r.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    sc_table_data = [
        ["Kode", "Kategori", "Arti Standar (RFC 9110)", "Konteks Penggunaan pada SportVenue", "Karakteristik Response"],
        ["200 OK", "2xx Success", "Request berhasil diproses", "GET katalog venue/court, PUT/PATCH update, refund sewa", "{ status: 'success', data: { ... } }"],
        ["201 Created", "2xx Success", "Resource baru berhasil dibuat", "POST register, POST booking baru, POST court, POST payment", "Header Location + representasi data baru"],
        ["204 No Content", "2xx Success", "Operasi tuntas tanpa body", "DELETE lapangan, DELETE blokir, POST logout revokasi token", "Tanpa body (0 byte payload jaringan)"],
        ["400 Bad Request", "4xx Client", "Sintaks request rusak", "Payload JSON rusak (kurung kurawal tidak tutup, sintaks error)", "{ status: 'error', message: 'Malformed JSON' }"],
        ["401 Unauthorized", "4xx Client", "Autentikasi tidak valid", "Tanpa Bearer token, token Sanctum expired, salah login", "{ status: 'error', message: 'Unauthenticated' }"],
        ["403 Forbidden", "4xx Client", "Otorisasi ditolak (tidak berhak)", "Penyewa akses /manage/*, edit venue orang lain, HMAC palsu", "{ status: 'error', message: 'Akses ditolak' }"],
        ["404 Not Found", "4xx Client", "Resource tidak ada di sistem", "ID venue/court/booking tidak terdaftar di database MySQL", "{ status: 'error', message: 'Not Found' }"],
        ["409 Conflict", "4xx Client", "Bentrok dengan state database", "Anti double booking (slot terkunci), booking expired dibatalkan", "{ code: 'SLOT_ALREADY_LOCKED', message: '...' }"],
        ["422 Unprocessable", "4xx Client", "Isi melanggar validasi bisnis", "Slot kosong, tanggal bukan YYYY-MM-DD, email duplikat", "{ errors: { field: ['detail pesan error'] } }"],
        ["429 Too Many Req", "4xx Client", "Melebihi kuota rate limit", "Spam booking, brute-force login (> 60 request/menit)", "Header Retry-After: 60 + pesan penolakan"],
        ["500 Server Error", "5xx Server", "Kegagalan internal tak terduga", "Koneksi database MySQL terputus, fatal exception runtime", "Pesan umum, tanpa stack trace"],
        ["503 Unavailable", "5xx Server", "Layanan upstream/gateway down", "API Midtrans payment gateway down/maintenance saat token", "Pesan gateway sibuk, coba kembali"]
    ]
    t_sc_docx = doc.add_table(rows=len(sc_table_data), cols=5)
    t_sc_docx.alignment = WD_TABLE_ALIGNMENT.CENTER
    for r_idx, row in enumerate(sc_table_data):
        for c_idx, val in enumerate(row):
            cell = t_sc_docx.cell(r_idx, c_idx)
            if r_idx == 0:
                set_cell_background(cell, "1E3A8A")
            elif r_idx % 2 == 1:
                set_cell_background(cell, "FFFFFF")
            else:
                set_cell_background(cell, "F8FAFC")
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(1)
            p.paragraph_format.space_before = Pt(1)
            run = p.add_run(val)
            run.font.size = Pt(6.8)
            if r_idx == 0:
                run.bold = True
                run.font.color.rgb = RGBColor(255, 255, 255)

    doc.add_page_break()

    # Halaman 5 Docx
    h5 = doc.add_paragraph()
    r = h5.add_run("Representasi Response JSON & Format Error Terstandar")
    r.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(30, 58, 138)

    p = doc.add_paragraph()
    p.add_run("Format Response Envelope Item Tunggal & Koleksi (Pagination):\n").bold = True
    p.add_run("""// GET /api/v1/venues?page=1&per_page=2
{
  "status": "success",
  "data": [
    { "id": 1, "name": "Arena Garuda Futsal", "city": "Tegal", "starting_price": 120000 },
    { "id": 2, "name": "GOR Satria Badminton", "city": "Tegal", "starting_price": 40000 }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/venues?page=1",
    "last": "http://localhost:8000/api/v1/venues?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/v1/venues?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 2,
    "total": 6,
    "last_page": 3
  }
}""").font.size = Pt(8)

    p2 = doc.add_paragraph()
    p2.add_run("Pola Aksi Non-CRUD & Catatan Keputusan Desain:\n").bold = True
    p2.add_run("""• Aksi Pembatalan (POST /bookings/{id}/cancellation): Pola sub-resource proses untuk mencatat audit refund & pelepasan lock slot.
• Aksi Pembayaran DP (POST /bookings/{id}/payments): Sub-resource pembayaran dengan Midtrans Snap & callback webhook idempotent.
• PUT vs PATCH: Keduanya didukung pada /manage/courts/{id} dan /manage/venues/{id}. PUT untuk replace utuh, PATCH untuk mutasi parsial.
• Versioning: Prefix /api/v1 untuk proteksi kontrak jangka panjang tanpa merusak client lawas.""").font.size = Pt(8.5)

    doc.save(filename)
    print(f"Successfully generated synchronized 5-page DOCX: {filename}")

if __name__ == "__main__":
    pdf_path = r"c:\xampp\htdocs\booking_lapangan\Tugas_Minggu_4_Workshop_Desain_REST_API_Kelompok_3.pdf"
    docx_path = r"c:\xampp\htdocs\booking_lapangan\Tugas_Minggu_4_Workshop_Desain_REST_API_Kelompok_3.docx"
    build_pdf(pdf_path)
    build_docx(docx_path)
