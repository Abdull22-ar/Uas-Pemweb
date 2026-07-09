# Panduan Deployment Silaris ke InfinityFree

## Prasyarat
- Akun InfinityFree (2 account terpisah untuk frontend dan backend)
- File project sudah siap (sampahsehat-frontend dan backendsampahsehat)
- Akses ke panel control InfinityFree

---

## Langkah 1: Deploy Backend (Laravel)

### 1.1 Upload File Backend
1. Login ke panel InfinityFree untuk account backend
2. Buka **File Manager** atau gunakan FTP client (FileZilla)
3. Upload semua file dari folder `backendsampahsehat` ke folder `htdocs`
4. Pastikan file `.env` **TIDAK** diupload (karena sudah ada di gitignore)

### 1.2 Konfigurasi Database
1. Di panel InfinityFree, buka **MySQL Databases**
2. Buat database baru atau gunakan yang sudah ada
3. Catat informasi berikut:
   - Database Host (misal: `sqlXXX.infinityfree.com`)
   - Database Name (misal: `if0_XXXXXXX_silaris`)
   - Username (misal: `if0_XXXXXXX`)
   - Password

### 1.3 Setup File .env
1. Copy file `.env.infinityfree` dari local ke server
2. Rename menjadi `.env`
3. Edit file `.env` dan isi dengan informasi database yang sudah dicatat:
   ```env
   DB_HOST=sqlXXX.infinityfree.com
   DB_DATABASE=if0_XXXXXXX_silaris
   DB_USERNAME=if0_XXXXXXX
   DB_PASSWORD=password_anda
   APP_URL=https://backend-name.infinityfreeapp.com
   ```

### 1.4 Generate APP_KEY
1. Buka terminal/SSH di server atau jalankan di local:
   ```bash
   php artisan key:generate
   ```
2. Copy hasil APP_KEY yang di-generate ke file `.env` di server

### 1.5 Run Migration
1. Buka **Online File Editor** di panel InfinityFree atau gunakan SSH
2. Jalankan perintah berikut di folder project:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

### 1.6 Set Permissions
Pastikan folder berikut memiliki permission yang benar:
- `storage` - 755 atau 777
- `bootstrap/cache` - 755 atau 777

### 1.7 Test Backend
1. Buka URL backend di browser: `https://backend-name.infinityfreeapp.com`
2. Seharusnya redirect ke halaman login
3. Coba akses API: `https://backend-name.infinityfreeapp.com/api/kategori`

---

## Langkah 2: Deploy Frontend (PHP Native)

### 2.1 Upload File Frontend
1. Login ke panel InfinityFree untuk account frontend
2. Upload semua file dari folder `sampahsehat-frontend` ke folder `htdocs`

### 2.2 Konfigurasi API URL
1. Edit file `config.php`
2. Uncomment baris untuk infinityfree dan ganti URL:
   ```php
   // Comment baris localhost:
   // $backendBaseUrl = 'http://127.0.0.1:8000';
   
   // Uncomment dan ganti dengan URL backend:
   $backendBaseUrl = 'https://backend-name.infinityfreeapp.com';
   ```

### 2.3 Test Frontend
1. Buka URL frontend di browser: `https://frontend-name.infinityfreeapp.com`
2. Coba akses halaman:
   - Beranda (`index.php`)
   - Daftar Laporan (`daftar-laporan.php`)
   - Cek Status (`cek-status.php`)

---

## Langkah 3: Verifikasi Koneksi API

### 3.1 Test Endpoint Kategori
Buka browser dan akses:
```
https://backend-name.infinityfreeapp.com/api/kategori
```
Seharusnya muncul JSON response dengan data kategori.

### 3.2 Test Endpoint Laporan Publik
Buka browser dan akses:
```
https://backend-name.infinityfreeapp.com/api/laporan-publik
```
Seharusnya muncul JSON response dengan data laporan.

### 3.3 Test dari Frontend
1. Buka halaman `daftar-laporan.php` di frontend
2. Pastikan data laporan muncul (bukan data dummy)
3. Jika masih data dummy, berarti koneksi API belum berhasil

---

## Troubleshooting

### Masalah: Gagal terhubung ke API
**Solusi:**
- Pastikan URL backend di `config.php` sudah benar
- Cek apakah backend sudah bisa diakses langsung via browser
- Pastikan CORS tidak memblokir request (Laravel default sudah allow)

### Masalah: Error 500 di Backend
**Solusi:**
- Cek file `.env` sudah terisi dengan benar
- Pastikan APP_KEY sudah di-generate
- Cek permission folder `storage` dan `bootstrap/cache`
- Aktifkan `APP_DEBUG=true` sementara untuk melihat error detail

### Masalah: Database Connection Failed
**Solusi:**
- Pastikan kredensial database di `.env` sudah benar
- Cek apakah database sudah dibuat di panel InfinityFree
- Pastikan user database sudah memiliki permission yang cukup

### Masalah: Migration Gagal
**Solusi:**
- Pastikan koneksi database sudah berhasil
- Cek apakah tabel sudah ada (jika ya, hapus dulu atau gunakan `migrate:fresh`)
- Pastikan PHP version di InfinityFree kompatibel dengan Laravel

---

## Catatan Penting

1. **URL Backend & Frontend**
   - Backend dan frontend harus di-deploy di account InfinityFree yang berbeda
   - Catat URL masing-masing untuk konfigurasi

2. **Security**
   - Pastikan `APP_DEBUG=false` di production
   - Jangan upload file `.env` dari local ke server jika sudah ada sensitive data
   - Gunakan HTTPS (InfinityFree sudah menyediakan SSL gratis)

3. **Performance**
   - InfinityFree adalah hosting gratis dengan resource terbatas
   - Untuk production sebaiknya gunakan hosting berbayar dengan resource lebih baik

4. **Backup**
   - Backup database secara berkala dari panel InfinityFree
   - Backup file project jika ada perubahan penting

---

## Struktur Final

```
InfinityFree Account 1 (Backend):
├── htdocs/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env (dari .env.infinityfree)
│   └── ... (file Laravel lainnya)

InfinityFree Account 2 (Frontend):
├── htdocs/
│   ├── assets/
│   ├── components/
│   ├── data/
│   ├── config.php (sudah diupdate dengan URL backend)
│   ├── index.php
│   ├── laporan.php
│   ├── daftar-laporan.php
│   ├── cek-status.php
│   └── ... (file frontend lainnya)
```
