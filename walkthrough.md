# Walkthrough: Sinkronisasi Tampilan Frontend dan Backend

Berikut adalah ringkasan perubahan dan penyelarasan tampilan antara Frontend dan Backend untuk memastikan aplikasi Silaris terlihat profesional, modern, responsif, dan konsisten.

## Apa Saja yang Berubah?

### 1. Perombakan Frontend (PHP Native)
Seluruh halaman frontend yang sebelumnya menggunakan Tailwind telah ditulis ulang menggunakan **Bootstrap 5**. Hal ini dilakukan untuk mencapai konsistensi visual dengan standar desain yang mudah dikembangkan.
- **Beranda (`index.php`)**: Menggunakan komponen hero section yang lebih interaktif dan *clean*, dengan micro-animations dan penyesuaian warna sesuai palet utama (*Sea Green* / `#2e8b57`).
- **Form Laporan (`laporan.php`)**: Tampilan form kini menggunakan grid Bootstrap yang lebih proporsional. Integrasi Leaflet JS tetap dipertahankan dengan *styling* yang disesuaikan.
- **Daftar Laporan Publik (`daftar-laporan.php`)**: Data ditampilkan menggunakan tabel Bootstrap 5 yang bersih dan *hover-able*, dengan badge status berwarna (Baru, Diproses, Selesai, Ditolak).
- **Cek Status (`cek-status.php`)**: Desain form pencarian kode laporan dan tampilan detail yang lebih informatif dan mudah dibaca pada perangkat mobile.
- **Autentikasi (`login.php`)**: Desain *card* login yang sentris dan *mobile-friendly*.

> [!IMPORTANT]
> **Integrasi Fetch API di Form Laporan**
> Sesuai instruksi, form pengiriman laporan di `laporan.php` tidak lagi *post* ke `kirim.php`, melainkan langsung mengirim data secara asinkronus ke **API Laravel (`POST /api/laporan`)** menggunakan `fetch()`. Pengguna akan menerima pesan sukses/gagal langsung di halaman yang sama tanpa perlu *reload*.

### 2. Perombakan Backend (Laravel)
Kami menulis ulang struktur dasar layout admin yang dulunya *custom grid* dan CSS mandiri menjadi struktur berbasis komponen **Bootstrap 5**.
- **Layout Utama (`admin.blade.php`)**: Sidebar interaktif dan *topbar* kini 100% menggunakan utilitas Bootstrap dan `flexbox`. Ini secara otomatis mengatasi masalah responsivitas di layar tablet maupun *smartphone*.
- **Dashboard Admin (`dashboard.blade.php`)**: Kartu statistik (Laporan Baru, Sedang Diproses, dll) dan *progress bar* per kategori menggunakan kartu bergaya *modern glassmorphism* dengan warna yang selaras dengan frontend.
- **Manajemen Laporan & Kategori**: 
  - **Tabel Daftar Laporan (`index.blade.php`)**: Dilengkapi *badge* dan form filter sejajar (*inline*) yang responsif.
  - **Detail & Update Status (`show.blade.php`, `edit-status.blade.php`)**: *Grid layout* dan panel informasi disajikan dengan pembagian ruang yang nyaman dibaca, serta *hint* interaktif (misalnya opsi catatan otomatis *wajib isi* jika laporan "Ditolak").

### 3. Penyesuaian API dan Validasi
- Sebelumnya terdapat *bug* (Error 422 Unprocessable Entity) karena validasi kontak yang terlalu ketat (memaksa regex nomor telepon padahal *dummy data* login menggunakan email). 
- Validasi untuk `kontak_pelapor` telah disesuaikan agar bisa menerima format yang lebih umum (seperti email pelapor) dan batas *min-length* deskripsi diturunkan.
- Menambahkan *timeout* dan *error handling* pada penggunaan `file_get_contents` di Frontend sehingga aplikasi tidak *freeze* ketika API Laravel *down* atau belum diaktifkan.

## Cara Melakukan Testing

Karena *development server* sudah berjalan (`localhost:8080` untuk Frontend dan `localhost:8000` untuk Backend Laravel):

1. Buka browser dan akses **Frontend** di [http://localhost:8080](http://localhost:8080).
2. Periksa desain *homepage* dan navigasinya, lalu coba *Login* (bisa menggunakan email dan password *default* `annisa@pelapor.com` / `password123` yang ter-generate di file JSON lokal).
3. Setelah login, masuk ke **Buat Laporan** ([http://localhost:8080/laporan.php](http://localhost:8080/laporan.php)).
4. Klik pada peta, isi data form, dan klik kirim. Perhatikan bagaimana *Fetch API* memunculkan status loading dan *alert* berhasil/gagal secara *real-time*.
5. Buka **Backend Admin** di [http://localhost:8000/login](http://localhost:8000/login) (Gunakan kredensial admin yang Anda miliki).
6. Lihat perubahan *Dashboard* admin, kelola laporan, ubah status, dan amati responsivitas UI-nya jika jendela browser dikecilkan.

## Perbaikan Bug & Refactoring Terakhir

Sebagai tambahan dari penyelarasan tampilan, beberapa optimasi *backend* (Laravel) juga dilakukan untuk meningkatkan performa dan keandalan sistem:

### 🐞 Bug yang Diperbaiki
1. **Dangling File Foto Laporan**: Memperbaiki masalah di mana foto baru yang diunggah dapat terhapus secara tak sengaja jika *checkbox* "hapus foto" ikut dicentang. Kini `hapus_foto` diabaikan jika ada file foto baru yang diunggah.
2. **Error Paginasi Parameter Negatif**: Menambahkan batasan (`max(1, ...)`) pada pembacaan query string `per_page` sehingga aplikasi tidak akan mengalami *Exception Error 500* jika *user/attacker* menginputkan angka nol atau negatif.
3. **Dokumentasi `generateKodeLaporan`**: Menyelaraskan komentar dokumentasi di Model `LaporanSampah` agar sesuai dengan logika terbaru yang menggunakan kombinasi tanggal dan angka acak.

### ♻️ Refactoring
1. **Optimasi Query Dashboard**: Menghitung statistik status laporan (Baru, Diproses, Selesai, Ditolak) menggunakan 1 query tergabung (`groupBy status`) ketimbang 4 query database terpisah, mengurangi beban pemanggilan *database* ketika dasbor diakses.
2. **Penghapusan Duplikasi Logika Render**: Menyederhanakan method-method *shortcut* di `LaporanSampahController` (`byStatus`, `byKategori`, `byRisiko`) menjadi *Redirect* sederhana ke method `index` (dengan menyertakan *query string*), alih-alih merender ulang data dan *view* secara redundan di tiap-tiap fungsi.

> [!TIP]
> Detail lengkap mengenai catatan penggunaan AI dan proses debugging dapat Anda lihat di dokumen terpisah: [Bug Fix & AI Usage Log](file:///C:/Users/User/.gemini/antigravity-ide/brain/35d5ad62-66c4-4977-95ec-f1ecbb0fb976/bug_fix_log.md).
