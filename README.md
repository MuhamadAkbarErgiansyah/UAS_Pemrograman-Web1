# 🌄 Explore Bandung - Platform Wisata Digital

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

<p align="center">
  <b>Platform pemesanan paket wisata Bandung yang lengkap dengan fitur modern</b>
</p>

---

## 🎬 Demo Video & Dokumentasi

<p align="center">
  <a href="https://drive.google.com/drive/folders/1dOiDsNFhu8hP-HIRAN_Ut8zx0gIZpp0l?usp=sharing">
    <img src="https://img.shields.io/badge/📁%20Lihat%20Demo%20Video-Google%20Drive-4285F4?style=for-the-badge&logo=googledrive&logoColor=white" alt="Demo Video">
  </a>
</p>

<p align="center">
  🎥 <b><a href="https://drive.google.com/drive/folders/1dOiDsNFhu8hP-HIRAN_Ut8zx0gIZpp0l?usp=sharing">Klik di sini untuk melihat Demo Video Aplikasi Explore Bandung</a></b>
</p>

> 📌 **Catatan**: Folder berisi video demo lengkap fitur-fitur aplikasi termasuk:
> - Demo fitur user (login, booking, wishlist, review, chat)
> - Demo fitur admin (dashboard, approval, voucher, analytics)
> - Walkthrough aplikasi secara keseluruhan

---

## 📋 Daftar Isi

- [Tentang Project](#-tentang-project)
- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Struktur Folder](#-struktur-folder)
- [Database Schema](#-database-schema)
- [Keamanan](#-keamanan)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [API Endpoints](#-api-endpoints)
- [Screenshots](#-screenshots)
- [Kontributor](#-kontributor)
- [Lisensi](#-lisensi)

---

## 🏔️ Tentang Project

**Explore Bandung** adalah platform web profesional untuk pemesanan paket wisata di wilayah Bandung Raya. Platform ini menyediakan layanan lengkap mulai dari browse paket wisata, pemesanan online, sistem voucher diskon, hingga fitur chat dengan admin.

### Visi
> Menjadi penyedia layanan wisata Bandung terbaik yang mengedepankan kenyamanan, keamanan, edukasi, dan pengalaman tak terlupakan.

### Misi
- ✅ Mengutamakan pelayanan terbaik kepada wisatawan
- ✅ Mempromosikan potensi wisata di seluruh Bandung Raya
- ✅ Menyediakan paket wisata lengkap dan profesional

---

## ✨ Fitur Utama

### 👤 Fitur User (Pengunjung)

| Fitur | Deskripsi |
|-------|-----------|
| 🔐 **Autentikasi** | Registrasi, Login, Logout dengan session management |
| 📦 **Browse Paket** | Melihat paket wisata Lembang, Ciwidey, Pangalengan |
| 🔍 **Pencarian** | Cari paket berdasarkan nama, kategori, harga |
| 📅 **Pemesanan Online** | Form booking lengkap dengan validasi |
| 🎟️ **Voucher Diskon** | Sistem kode voucher untuk diskon |
| ❤️ **Wishlist** | Simpan paket favorit untuk nanti |
| ⭐ **Review & Rating** | Berikan ulasan dan rating paket |
| 💬 **Live Chat** | Chat real-time dengan admin |
| 📜 **Riwayat Pesanan** | Lihat histori pemesanan |
| 🔔 **Notifikasi** | Pemberitahuan status booking |
| 🖼️ **Galeri** | Galeri foto wisata |
| 👨‍🏫 **Guide Profesional** | Profil guide dengan itinerary |

### 👨‍💼 Fitur Admin

| Fitur | Deskripsi |
|-------|-----------|
| 📊 **Dashboard** | Statistik lengkap dan overview |
| 📦 **Manajemen Paket** | CRUD paket wisata |
| 📋 **Manajemen Booking** | Approve/Reject pemesanan |
| 🎫 **Manajemen Voucher** | Buat & kelola voucher diskon |
| ⭐ **Manajemen Review** | Moderasi review & balas ulasan |
| 💬 **Admin Chat** | Balas pesan dari user |
| 📈 **Analytics** | Analisis data & grafik |
| 📄 **Laporan** | Generate laporan pemesanan |
| 📤 **Export Data** | Export ke Excel & PDF |

---

## 🛠️ Teknologi

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling dengan custom properties
- **Bootstrap 5.3** - CSS Framework
- **JavaScript ES6** - Interaktivitas
- **Font Awesome 6** - Icons
- **AOS (Animate On Scroll)** - Animasi scroll

### Tools
- **XAMPP** - Local development server
- **Git** - Version control

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                         │
│                    HTML5 + CSS3 + JavaScript                    │
└─────────────────────┬───────────────────────────────────────────┘
                      │ HTTP Request
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                      WEB SERVER (Apache)                        │
│                          XAMPP Stack                            │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │   config    │  │   header    │  │       footer            │  │
│  │   .php      │  │   .php      │  │       .php              │  │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    PAGE CONTROLLERS                       │   │
│  │  index.php │ paket.php │ pemesanan.php │ detail.php      │   │
│  │  login.php │ register.php │ riwayat.php │ wishlist.php   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    ADMIN CONTROLLERS                      │   │
│  │  admin_dashboard.php │ admin_bookings.php                │   │
│  │  admin_vouchers.php  │ admin_reviews.php │ admin_chat    │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                     REST API                              │   │
│  │  /api/chat.php │ /api/notifications.php                  │   │
│  │  /api/voucher.php │ /api/reviews_wishlist.php            │   │
│  │  /api/get_items.php │ /api/create_item.php               │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────┬───────────────────────────────────────────┘
                      │ PDO Connection
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE (MySQL)                           │
│                         uts_web                                 │
│  ┌─────────┐ ┌───────────┐ ┌─────────┐ ┌──────────────────┐    │
│  │  users  │ │   items   │ │ reviews │ │   reservations   │    │
│  └─────────┘ └───────────┘ └─────────┘ └──────────────────┘    │
│  ┌──────────────┐ ┌──────────┐ ┌─────────────┐ ┌──────────┐    │
│  │  wishlists   │ │ vouchers │ │ chat_messages│ │notifications│ │
│  └──────────────┘ └──────────┘ └─────────────┘ └──────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

### Flow Diagram

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Browse  │ ──▶ │  Login   │ ──▶ │  Booking │ ──▶ │  Payment │
│  Paket   │     │  User    │     │  Form    │     │  Confirm │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
                                       │
                                       ▼
                              ┌──────────────────┐
                              │  Admin Approval  │
                              └────────┬─────────┘
                                       │
                      ┌────────────────┼────────────────┐
                      ▼                ▼                ▼
               ┌──────────┐     ┌──────────┐     ┌──────────┐
               │ Approved │     │ Rejected │     │  Pending │
               └──────────┘     └──────────┘     └──────────┘
```

---

## 📁 Struktur Folder

```
uts_pemrogramanweb1-main/
│
├── 📁 app/                          # Aplikasi utama
│   │
│   ├── 📁 api/                      # REST API endpoints
│   │   ├── chat.php                 # API chat real-time
│   │   ├── check_session.php        # Cek status session
│   │   ├── create_item.php          # Tambah paket baru
│   │   ├── delete_item.php          # Hapus paket
│   │   ├── get_item.php             # Get single item
│   │   ├── get_items.php            # Get all items
│   │   ├── get_package_detail.php   # Detail paket
│   │   ├── notifications.php        # API notifikasi
│   │   ├── reviews_wishlist.php     # API review & wishlist
│   │   ├── update_item.php          # Update paket
│   │   └── voucher.php              # API validasi voucher
│   │
│   ├── 📁 assets/                   # Static assets
│   │   ├── 📁 css/
│   │   │   └── style.css            # Custom stylesheet
│   │   └── 📁 js/
│   │       └── main.js              # JavaScript utama
│   │
│   ├── 📁 images/                   # Gambar paket wisata
│   │   ├── lembang.jpg
│   │   ├── ciwidey.jpeg
│   │   ├── pangalengan.jpg
│   │   └── ...
│   │
│   ├── 📁 includes/                 # Komponen reusable
│   │   ├── admin_header.php         # Header admin panel
│   │   ├── admin_footer.php         # Footer admin panel
│   │   ├── export_excel.php         # Export ke Excel
│   │   └── export_pdf.php           # Export ke PDF
│   │
│   ├── ── 🔧 Core Files ──
│   ├── config.php                   # Konfigurasi database & session
│   ├── header.php                   # Header template
│   ├── footer.php                   # Footer template
│   │
│   ├── ── 📄 User Pages ──
│   ├── index.php                    # Halaman utama
│   ├── login.php                    # Halaman login
│   ├── register.php                 # Halaman registrasi
│   ├── logout.php                   # Logout handler
│   ├── dashboard.php                # Dashboard user
│   ├── paket.php                    # Daftar paket wisata
│   ├── detail.php                   # Detail paket
│   ├── pemesanan.php                # Form pemesanan
│   ├── riwayat.php                  # Riwayat pesanan
│   ├── wishlist.php                 # Wishlist user
│   ├── galerry.php                  # Galeri foto
│   ├── guide.php                    # Profil guide
│   ├── search.php                   # Pencarian paket
│   ├── chat.php                     # Chat dengan admin
│   │
│   ├── ── 👨‍💼 Admin Pages ──
│   ├── admin_login.php              # Login admin
│   ├── admin_dashboard.php          # Dashboard admin
│   ├── admin_bookings.php           # Kelola booking
│   ├── admin_vouchers.php           # Kelola voucher
│   ├── admin_reviews.php            # Kelola review
│   ├── admin_chat.php               # Chat admin
│   ├── admin_analytics.php          # Analytics
│   ├── admin_reports.php            # Laporan
│   │
│   ├── ── 🛠️ Utilities ──
│   ├── add_item.php                 # Tambah item
│   ├── edit_item.php                # Edit item
│   ├── manage_items.php             # Kelola item
│   ├── export_booking.php           # Export booking
│   ├── generate_certificate.php     # Generate sertifikat
│   ├── create_tables.php            # Setup database tables
│   ├── admin_setup.php              # Setup admin
│   └── setup_new_features.php       # Setup fitur baru
│
├── 📁 images/                       # Screenshot dokumentasi
│   └── Screenshot *.png
│
└── README.md                        # Dokumentasi project
```

---

## 🗄️ Database Schema

### Entity Relationship Diagram (ERD)

```
┌─────────────┐       ┌──────────────────┐       ┌─────────────┐
│    users    │       │   reservations   │       │    items    │
├─────────────┤       ├──────────────────┤       ├─────────────┤
│ id (PK)     │──┐    │ id (PK)          │    ┌──│ id (PK)     │
│ name        │  │    │ user_id (FK)     │←───┘  │ title       │
│ email       │  └───▶│ package_id (FK)  │───────│ slug        │
│ password    │       │ package_title    │       │ summary     │
│ role        │       │ name             │       │ description │
│ created_at  │       │ email            │       │ price       │
└─────────────┘       │ contact          │       │ image       │
       │              │ date_event       │       │ category    │
       │              │ note             │       │ created_at  │
       │              │ created_at       │       └─────────────┘
       │              └──────────────────┘              │
       │                      │                        │
       │              ┌───────┴────────┐              │
       │              ▼                               │
       │    ┌──────────────────┐                      │
       │    │booking_approvals │                      │
       │    ├──────────────────┤                      │
       │    │ id (PK)          │                      │
       │    │ reservation_id   │                      │
       │    │ status           │                      │
       │    │ approval_date    │                      │
       │    │ approved_by      │                      │
       │    │ notes            │                      │
       │    └──────────────────┘                      │
       │                                              │
       ▼                                              ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────────┐
│  wishlists  │    │   reviews   │    │  chat_messages  │
├─────────────┤    ├─────────────┤    ├─────────────────┤
│ id (PK)     │    │ id (PK)     │    │ id (PK)         │
│ user_id(FK) │    │ item_id(FK) │    │ sender_id (FK)  │
│ item_id(FK) │    │ user_id(FK) │    │ receiver_id(FK) │
│ created_at  │    │ rating      │    │ message         │
└─────────────┘    │ comment     │    │ is_read         │
                   │ created_at  │    │ created_at      │
                   └─────────────┘    └─────────────────┘

┌─────────────────┐    ┌─────────────────┐
│    vouchers     │    │  notifications  │
├─────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)         │
│ code            │    │ user_id (FK)    │
│ discount_type   │    │ type            │
│ discount_value  │    │ message         │
│ min_order       │    │ is_read         │
│ max_uses        │    │ created_at      │
│ used_count      │    └─────────────────┘
│ valid_from      │
│ valid_until     │
│ is_active       │
└─────────────────┘
```

### Tables Description

| Table | Deskripsi |
|-------|-----------|
| `users` | Data pengguna (admin & user) |
| `items` | Paket wisata |
| `reservations` | Pemesanan/booking |
| `booking_approvals` | Status approval booking |
| `reviews` | Ulasan & rating |
| `wishlists` | Paket favorit user |
| `vouchers` | Kode voucher diskon |
| `chat_messages` | Pesan chat |
| `notifications` | Notifikasi user |

---

## 🔒 Keamanan

### 1. Authentication & Session

```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);    // Prevent XSS access to cookie
ini_set('session.cookie_samesite', 'Lax'); // CSRF protection
ini_set('session.use_strict_mode', 1);     // Prevent session fixation
```

### 2. Password Hashing

```php
// Menggunakan bcrypt untuk hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verifikasi password
password_verify($input, $hashedPassword);
```

### 3. SQL Injection Prevention

```php
// Menggunakan PDO Prepared Statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### 4. XSS Prevention

```php
// Output sanitization
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### 5. CSRF Protection
- Session-based authentication
- SameSite cookie policy

### 6. Access Control

```php
// Role-based access control
if($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
```

### 7. Input Validation

```php
// Server-side validation
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$name = trim(htmlspecialchars($_POST['name']));
```

### Security Features Summary

| Feature | Implementation |
|---------|----------------|
| 🔐 Password Hashing | bcrypt (PASSWORD_DEFAULT) |
| 🛡️ SQL Injection | PDO Prepared Statements |
| 🚫 XSS | htmlspecialchars() |
| 🔒 Session Security | HTTPOnly, SameSite, Strict Mode |
| 👤 Access Control | Role-based (admin/user) |
| ✅ Input Validation | Server-side + Client-side |

---

## 🚀 Instalasi

### Prasyarat
- XAMPP v7.4+ (Apache + MySQL + PHP)
- Web Browser modern
- Git (optional)

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/MuhamadAkbarErgiansyah/UAS_Pemrograman-Web1.git
   ```

2. **Start XAMPP**
   - Buka XAMPP Control Panel
   - Start **Apache** dan **MySQL**

3. **Buat Database**
   ```sql
   CREATE DATABASE uts_web;
   ```

4. **Setup Tables**
   - Buka browser: `http://localhost/UAS_Pemrograman-Web1/app/create_tables.php`
   - Atau import SQL file jika tersedia

5. **Setup Admin** (Opsional)
   - Buka: `http://localhost/UAS_Pemrograman-Web1/app/admin_setup.php`

6. **Akses Aplikasi**
   - User: `http://localhost/UAS_Pemrograman-Web1/app/`
   - Admin: `http://localhost/UAS_Pemrograman-Web1/app/admin_login.php`

---

## ⚙️ Konfigurasi

### Database Configuration

Edit file `app/config.php`:

```php
$host = 'localhost';      // Database host
$db   = 'uts_web';        // Database name
$user = 'root';           // Database username
$pass = '';               // Database password (kosongkan jika default XAMPP)
$charset = 'utf8mb4';     // Character set
```

### Session Configuration

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', 0);  // Set ke 1 jika HTTPS
```

---

## 📡 API Endpoints

### Public Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/get_items.php` | Get semua paket |
| GET | `/api/get_item.php?id={id}` | Get detail paket |
| GET | `/api/get_package_detail.php?slug={slug}` | Get detail by slug |

### Protected Endpoints (Requires Auth)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/check_session.php` | Cek status login |
| POST | `/api/chat.php` | Kirim/ambil pesan chat |
| GET | `/api/notifications.php` | Get notifikasi |
| POST | `/api/reviews_wishlist.php` | CRUD review & wishlist |
| POST | `/api/voucher.php` | Validasi voucher |

### Admin Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/create_item.php` | Tambah paket baru |
| POST | `/api/update_item.php` | Update paket |
| POST | `/api/delete_item.php` | Hapus paket |

---

## 📸 Screenshots

### Halaman Utama
![Homepage](images/Screenshot%202025-11-24%20223309.png)

### Daftar Paket
![Packages](images/Screenshot%202025-11-24%20224739.png)

### Form Pemesanan
![Booking](images/Screenshot%202025-11-24%20224808.png)

### Admin Dashboard
![Admin](images/Screenshot%202025-11-24%20224846.png)

---

## 👨‍💻 Kontributor

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/MuhamadAkbarErgiansyah">
        <img src="app/images/aku.jpg" width="100px;" alt="Akbar"/><br />
        <sub><b>Muhamad Akbar Ergiansyah</b></sub>
      </a><br />
      <sub>Lead Developer</sub>
    </td>
  </tr>
</table>

---

## 📄 Lisensi

Project ini dibuat untuk keperluan **UAS Pemrograman Web 1**.

```
© 2025-2026 Explore Bandung - All Rights Reserved
```

---

## 📞 Kontak

- **WhatsApp**: [+62 895-0889-1566](https://wa.me/6289508891566)
- **Email**: [akbarergiansyah@gmail.com](mailto:akbarergiansyah@gmail.com)
- **GitHub**: [@MuhamadAkbarErgiansyah](https://github.com/MuhamadAkbarErgiansyah)

---

<p align="center">
  Made with ❤️ in Bandung, Indonesia
</p>
