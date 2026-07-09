# 🗑️ Silaris – Sistem Laporan Sampah Sehat

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/REST_API-enabled-00C897?style=for-the-badge" />
</p>

---

## 📋 Deskripsi Singkat

**Silaris** (Sistem Laporan Sampah Sehat) adalah aplikasi web berbasis **Laravel + PHP Native** yang memungkinkan masyarakat melaporkan permasalahan sampah secara online. Masyarakat dapat mengirimkan laporan lengkap dengan foto, lokasi/koordinat GPS, dan kategori sampah, kemudian memantau status penanganannya secara real-time menggunakan kode laporan unik (`SPH-XXXXXX`).

Di sisi admin/petugas, tersedia dashboard berbasis Laravel Blade untuk mengelola laporan masuk, menugaskan petugas lapangan sesuai spesialisasi risiko, serta memperbarui status penanganan. Sistem ini dirancang untuk mendukung pengelolaan sampah berbasis data di lingkungan kesehatan masyarakat.

---

## 👥 Anggota Kelompok

| No | Nama | NIM | Peran |
|----|------|-----|-------|
| 1  | Annisa Kholifatul | 241020005 | Frontend Developer (PHP Native) |
| 2  | Hikmah Nutriana | 241020011 | UI/UX Designer |
| 3  | M. Adzhahabi P.D | 241020013 | Database Designer / Backend Developer |
| 4  | Maylany Hellena | 241020014 | Project Manager |

---

## 🧩 Pembagian Tugas

### 1. Annisa Kholifatul — Frontend Developer (PHP Native)
- Membangun seluruh halaman frontend publik menggunakan PHP Native
- Mengimplementasikan halaman **Beranda** (`index.php`) sebagai landing page aplikasi
- Membangun halaman **Form Kirim Laporan** (`laporan.php`) dengan integrasi GPS picker dan upload foto
- Membangun halaman **Daftar Laporan Publik** (`daftar-laporan.php`) yang mengonsumsi REST API
- Membangun halaman **Cek Status Laporan** (`cek-status.php`) berdasarkan kode unik laporan
- Membangun halaman **Rekap Laporan** (`rekap-laporan.php`) dengan fitur filter
- Mengintegrasikan frontend PHP dengan endpoint REST API backend Laravel
- Menangani error handling dan feedback pengguna di sisi frontend

### 2. Hikmah Nutriana — UI/UX Designer
- Merancang wireframe dan alur pengguna (user flow) aplikasi Silaris
- Mendesain tampilan antarmuka (UI) halaman publik dan panel admin
- Memilih palet warna, tipografi, dan komponen Bootstrap yang konsisten
- Menyiapkan aset visual (ikon, ilustrasi, dan elemen grafis) untuk frontend
- Memastikan tampilan responsif di berbagai ukuran layar (mobile, tablet, desktop)
- Memberikan panduan desain kepada tim developer untuk implementasi UI
- Melakukan review tampilan akhir dan penyesuaian detail visual

### 3. M. Adzhahabi P.D — Database Designer / Backend Developer
- Merancang skema database (ERD) untuk tabel `laporan_sampah`, `kategori_sampah`, dan `users`
- Membuat seluruh **migration** Laravel untuk struktur tabel dan penambahan kolom
- Membuat **seeder** untuk data awal (admin, kategori sampah, petugas lapangan)
- Membangun **Eloquent Model** (`LaporanSampah`, `KategoriSampah`) beserta relasi, scope query, dan accessor
- Mengembangkan **REST API Controller** (`Api\LaporanSampahController`, `Api\KategoriSampahController`)
- Mengimplementasikan validasi input server-side dan response JSON yang konsisten
- Mengelola konfigurasi database dan optimasi query (penambahan index)

### 4. Maylany Hellena — Project Manager
- Memimpin perencanaan dan pembagian tugas antar anggota tim
- Membuat dan memelihara timeline pengerjaan project UAS
- Mengkoordinasikan pengembangan backend Laravel (admin panel, routing, middleware auth)
- Membangun halaman admin panel berbasis Blade (dashboard, daftar laporan, edit status)
- Melakukan testing fungsionalitas keseluruhan sistem (API, frontend, admin)
- Mengelola deployment aplikasi ke hosting (InfinityFree)
- Mendokumentasikan bug dan solusi perbaikan di `bug_fix_log.md`
- Menyusun laporan dan dokumentasi akhir project

---

## ✨ Fitur Aplikasi

### 🔐 Autentikasi
- Login admin dan petugas dengan role-based access (`admin` / `petugas`)
- Proteksi halaman admin menggunakan Laravel Auth Middleware

### 📋 CRUD Entitas Utama
- **Laporan Sampah** – Kelola laporan masuk (lihat detail, ubah status, tambah catatan petugas)
- **Kategori Sampah** – Kelola kategori berdasarkan level risiko (rendah / sedang / tinggi)
- **Manajemen Petugas** – Penugasan petugas lapangan sesuai spesialisasi risiko

### 🌐 REST API
- Endpoint publik untuk pengiriman laporan dari frontend
- Pelacakan status laporan menggunakan kode unik (`SPH-XXXXXX`)
- Endpoint daftar laporan publik dengan paginasi
- Rate limiting pada endpoint pengiriman (3 request/menit)

### 📱 Frontend Responsif (PHP Native)
- Halaman beranda — pengenalan layanan
- Form kirim laporan dengan upload foto & GPS picker
- Halaman daftar laporan publik
- Halaman cek status laporan berdasarkan kode
- Rekap laporan dengan filter

### ✅ Validasi Input
- Validasi server-side di backend Laravel (nama pelapor, kontak, kategori, lokasi, koordinat, foto)
- Validasi client-side di frontend sebelum pengiriman form
- Upload foto dibatasi format `jpg`, `jpeg`, `png`, `webp`, maksimal **2 MB**

---

## 🛠️ Teknologi

| Teknologi | Keterangan |
|-----------|------------|
| **Laravel 11** | Backend framework – Admin panel, REST API, Eloquent ORM |
| **PHP Native 8.2** | Frontend – Halaman publik yang mengonsumsi API |
| **HTML / CSS / Bootstrap 5** | Tampilan antarmuka responsif |
| **MySQL / MariaDB** | Penyimpanan data laporan, kategori, dan pengguna |
| **REST API** | Komunikasi antara frontend dan backend |
| **Blade Template Engine** | Template view untuk halaman admin |

---

## 🚀 Cara Instalasi

### Prasyarat
- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Web Server (Apache / Nginx) atau `php artisan serve`

### Backend (Laravel)

```bash
# 1. Clone repository
git clone https://github.com/<username>/<repo-name>.git
cd <repo-name>

# 2. Masuk ke folder backend
cd backendsampahsehat

# 3. Install dependensi PHP
composer install

# 4. Copy file konfigurasi environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

**6. Atur database di file `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=silaris_db
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 7. Jalankan migration dan seeder
php artisan migrate --seed

# 8. Jalankan server backend
php artisan serve
# Backend berjalan di: http://127.0.0.1:8000
```

### Frontend (PHP Native)

```bash
# Masuk ke folder frontend
cd sampahsehat-frontend
```

Edit file `config.php`, ubah baris berikut untuk development lokal:
```php
// Untuk development lokal:
$backendBaseUrl = 'http://127.0.0.1:8000';
```

Kemudian buka folder `sampahsehat-frontend` dengan web server lokal (XAMPP, Laragon, dll.) atau:
```bash
php -S localhost:8080
# Frontend berjalan di: http://localhost:8080
```

---

## 🔑 Akun Demo

### Admin
| Field | Value |
|-------|-------|
| **Email** | `admin@Silaris.id` |
| **Password** | `admin123` |

### Petugas (Login Panel)
| Email | Password | Spesialisasi |
|-------|----------|-------------|
| `petugas_rendah@Silaris.id` | `petugas123` | Risiko Rendah |
| `petugas_sedang@Silaris.id` | `petugas123` | Risiko Sedang |
| `petugas_tinggi@Silaris.id` | `petugas123` | Risiko Tinggi |

---

## 🌍 Link Deploy

| Layanan | URL |
|---------|-----|
| **Frontend** | _(isi URL deploy frontend)_ |
| **Backend / Admin Panel** | _(isi URL deploy backend)_ |

> ⚠️ **Catatan:** Update link di atas sesuai hasil deployment aktual kelompok.

---

## 📡 Endpoint API

Base URL: `http://127.0.0.1:8000` (lokal) atau URL backend produksi

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/api/kategori` | Mengambil daftar semua kategori sampah aktif (untuk dropdown form) |
| `GET` | `/api/laporan-publik` | Mengambil daftar laporan publik dengan paginasi |
| `GET` | `/api/laporan/{kode}` | Mengambil detail & status laporan berdasarkan kode unik (`SPH-XXXXXX`) |
| `POST` | `/api/laporan` | Mengirimkan laporan sampah baru (multipart/form-data) |

### Contoh Request – `POST /api/laporan`

```
POST /api/laporan
Content-Type: multipart/form-data

nama_pelapor   = Budi Santoso
kontak_pelapor = 08123456789
kategori_id    = 1
lokasi         = Jl. Kenangan No. 5, Surabaya
latitude       = -7.2575
longitude      = 112.7521
deskripsi      = Tumpukan sampah plastik di pinggir jalan
foto           = (file gambar, opsional, maks 2 MB)
```

### Contoh Response – `GET /api/laporan/{kode}`

```json
{
  "success": true,
  "message": "Data laporan ditemukan.",
  "data": {
    "kode_laporan": "SPH-260611234",
    "status": "diproses",
    "label_status": "⚙️ Sedang Diproses",
    "lokasi": "Jl. Kenangan No. 5, Surabaya",
    "kategori": {
      "nama_kategori": "Sampah Plastik",
      "level_risiko": "rendah"
    },
    "petugas": {
      "nama": "Bayu",
      "kontak": "081234567001"
    }
  }
}
```

---

## 🤖 AI Usage Log

Berikut ringkasan penggunaan AI selama pengembangan proyek ini:

| No | Fitur / Modul | Tools AI | Kontribusi AI | Verifikasi Tim |
|----|---------------|----------|---------------|----------------|
| 1  | Eloquent Model & Relasi | Antigravity IDE | Generate relasi `belongsTo`, scope query, accessor | ✅ Direview & disesuaikan |
| 2  | REST API Controller | Antigravity IDE | Kerangka `successResponse` / `errorResponse` helper | ✅ Divalidasi dengan Postman |
| 3  | Validasi Input API | Antigravity IDE | Aturan validasi + pesan error Bahasa Indonesia | ✅ Ditest semua edge case |
| 4  | Migration Database | Antigravity IDE | Struktur tabel `laporan_sampah` dan `kategori_sampah` | ✅ Dijalankan & diverifikasi |
| 5  | Frontend PHP Native | Antigravity IDE | Kerangka halaman `laporan.php`, `cek-status.php`, `rekap-laporan.php` | ✅ Disesuaikan desain kelompok |
| 6  | Bug Fixing | Antigravity IDE | Diagnosa dan perbaikan bug deployment (CORS, path storage, DB) | ✅ Didokumentasikan di `bug_fix_log.md` |
| 7  | README | Antigravity IDE | Draft awal README berdasarkan kode di repository | ✅ Diedit dan dilengkapi tim |

> **Kebijakan kelompok:** Semua kode yang dihasilkan AI telah direview, dipahami, dan diverifikasi oleh minimal satu anggota tim sebelum di-commit ke repository.

---

## 📁 Struktur Repository

```
UAS/
├── backendsampahsehat/          # Laravel Backend & Admin Panel
│   ├── app/
│   │   ├── Http/Controllers/    # Controller Web & API
│   │   └── Models/              # Eloquent Models
│   ├── database/
│   │   ├── migrations/          # Skema database
│   │   └── seeders/             # Data awal (admin, kategori, laporan)
│   ├── resources/views/admin/   # Blade template halaman admin
│   └── routes/
│       ├── api.php              # Definisi endpoint API
│       └── web.php              # Definisi route admin
│
├── sampahsehat-frontend/        # Frontend PHP Native (Publik)
│   ├── index.php                # Halaman beranda
│   ├── laporan.php              # Form kirim laporan
│   ├── daftar-laporan.php       # Daftar laporan publik
│   ├── cek-status.php           # Cek status laporan
│   ├── rekap-laporan.php        # Rekap & filter laporan
│   └── config.php               # Konfigurasi URL backend API
│
├── DEPLOYMENT_INFINITYFREE.md   # Panduan deployment ke InfinityFree
├── bug_fix_log.md               # Log perbaikan bug
└── README.md                    # Dokumentasi ini
```

---

## 📄 Lisensi

Project ini dibuat untuk keperluan **UAS Pemrograman Web – Program Studi Informatika Kesehatan**.

---

<p align="center">Made with ❤️ by Kelompok <b>Silaris</b> — Informatika Kesehatan</p>
