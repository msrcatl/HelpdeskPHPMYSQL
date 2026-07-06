# Helpdesk Ticketing System

Aplikasi Helpdesk Ticketing System menggunakan PHP, MySQL, dan Bootstrap 5 dengan fitur email notifikasi.

## Fitur Utama

✅ **Autentikasi & Role-Based Access Control**
- Login & Registrasi (Admin, Support Agent, User)
- Manajemen role berbeda untuk setiap user

✅ **Manajemen Tiket**
- Buat tiket baru dengan judul, deskripsi, kategori, prioritas
- Status: Open, In Progress, Resolved, Closed
- Upload lampiran (gambar/PDF)
- Tambah komentar/solusi

✅ **Email Notifikasi (PHPMailer)**
- Notifikasi saat tiket dibuat
- Notifikasi saat tiket ditugaskan ke Support Agent
- Notifikasi saat status tiket berubah

✅ **Dashboard & Statistik**
- Dashboard Admin dengan filter status/kategori
- Statistik visual menggunakan Chart.js
- Dashboard User untuk melihat tiket mereka

✅ **Best Practices**
- Prepared statements untuk keamanan MySQL
- Struktur MVC sederhana
- Dokumentasi lengkap

## Instalasi di XAMPP

### Langkah 1: Download & Setup

1. Clone repository ke folder htdocs XAMPP:
```bash
cd C:\xampp\htdocs  # atau /Applications/XAMPP/xamppfiles/htdocs di Mac
git clone https://github.com/msrcatl/HelpdeskPHPMYSQL.git helpdesk
cd helpdesk
```

### Langkah 2: Buat Database

1. Buka `http://localhost/phpmyadmin`
2. Buat database baru dengan nama `helpdesk_db`
3. Import file `database/helpdesk.sql`:
   - Di phpMyAdmin, klik database `helpdesk_db`
   - Tab "Import"
   - Pilih file `database/helpdesk.sql`
   - Klik "Go"

### Langkah 3: Konfigurasi Email

Edit file `config/email.php`:
```php
// Gunakan email Gmail atau SMTP server lain
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'Helpdesk Ticketing System');
```

**Untuk Gmail:**
- Aktifkan 2-Step Verification
- Generate App Password: https://myaccount.google.com/apppasswords
- Gunakan App Password di `SMTP_PASSWORD`

### Langkah 4: Jalankan Aplikasi

1. Start Apache & MySQL di XAMPP Control Panel
2. Buka browser: `http://localhost/helpdesk`
3. Login dengan akun default:
   - **Admin**: admin@example.com / password123
   - **Support Agent**: support@example.com / password123
   - **User**: user@example.com / password123

## Struktur Folder

```
helpdeskPHPMYSQL/
├── config/
│   ├── database.php       # Koneksi MySQL
│   ├── email.php          # Konfigurasi PHPMailer
│   └── constants.php      # Konstanta global
├── controllers/
│   ├── AuthController.php
│   ├── TicketController.php
│   ├── CommentController.php
│   └── AdminController.php
├── models/
│   ├── User.php
│   ├── Ticket.php
│   ├── Comment.php
│   └── Email.php
├── views/
│   ├── layout/
│   │   ├── header.php
│   │   ├── navbar.php
│   │   └── footer.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── dashboard/
│   │   ├── admin.php
│   │   ├── agent.php
│   │   └── user.php
│   ├── tickets/
│   │   ├── list.php
│   │   ├── create.php
│   │   ├── detail.php
│   │   └── edit.php
│   └── errors/
│       ├── 404.php
│       └── 500.php
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── main.js
│   │   └── chart.js
│   └── uploads/
│       └── attachments/
├── database/
│   └── helpdesk.sql
├── index.php              # Entry point aplikasi
├── router.php             # Routing sederhana
└── .htaccess              # URL rewriting
```

## User Default

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password123 | Admin |
| support@example.com | password123 | Support Agent |
| user@example.com | password123 | User |

## Fitur Per Role

### Admin
- Dashboard dengan statistik lengkap
- Lihat semua tiket
- Assign tiket ke Support Agent
- Kelola user
- Export laporan

### Support Agent
- Dashboard dengan tiket yang ditugaskan
- Update status tiket
- Tambah komentar/solusi
- Upload lampiran

### User
- Dashboard dengan tiket mereka
- Buat tiket baru
- Lihat detail tiket
- Tambah komentar
- Upload lampiran

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5, Chart.js, jQuery
- **Email**: PHPMailer 6.5+
- **Server**: Apache (XAMPP)

## Security

- Password hashing menggunakan `password_hash()`
- Prepared statements untuk semua query
- Session management
- CSRF protection (token)
- Input validation & sanitization

## Troubleshooting

### Email tidak terkirim
- Cek konfigurasi di `config/email.php`
- Pastikan SMTP host dan port benar
- Untuk Gmail, gunakan App Password bukan password biasa
- Cek file log di `logs/email.log`

### Database connection error
- Pastikan MySQL running di XAMPP
- Cek konfigurasi di `config/database.php`
- Pastikan user MySQL dan password sesuai

### 404 Not Found
- Pastikan `.htaccess` ada di root folder
- Enable mod_rewrite di Apache
- Cek folder `public/uploads/` memiliki permission 755

## License

MIT License

## Support

Untuk pertanyaan atau issue, silakan buat issue di repository ini.
