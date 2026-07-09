---
marp: true
theme: default
class: lead
paginate: true
backgroundColor: #ffffff
---

# 🗑️ Silaris
**Sistem Laporan Sampah Sehat**

Proyek UAS Pemrograman Web  
Program Studi Informatika Kesehatan

---

## 🎯 Apa itu Silaris?

Silaris adalah aplikasi web yang memungkinkan masyarakat melaporkan permasalahan sampah secara online.

- **Pelaporan Mudah:** Masyarakat dapat mengirimkan laporan lengkap dengan foto, lokasi/koordinat GPS, dan kategori sampah.
- **Tracking Real-time:** Status penanganan dapat dipantau menggunakan kode laporan unik (`SPH-XXXXXX`).
- **Dashboard Admin:** Admin/petugas memiliki dashboard untuk mengelola dan merespon laporan secara efisien.

---

## 👥 Tim Pengembang (Kelompok Silaris)

| Nama | NIM | Peran |
|------|-----|-------|
| **Annisa Kholifatul** | 241020005 | Frontend Developer |
| **Hikmah Nutriana** | 241020011 | UI/UX Designer |
| **M. Adzhahabi P.D** | 241020013 | Backend / Database |
| **Maylany Hellena** | 241020014 | Project Manager |

---

## 🧩 Pembagian Tugas

**Frontend & UI/UX**
- **Annisa (Frontend):** Membangun landing page, form laporan terintegrasi GPS, serta logika konsumsi REST API.
- **Hikmah (UI/UX):** Merancang wireframe, user flow, mendesain antarmuka yang responsif, dan menyiapkan aset visual.

**Backend & Manajemen**
- **Adzhahabi (Backend):** Membuat skema database (ERD), REST API dengan Laravel, validasi server-side, dan struktur relasi data.
- **Maylany (Project Manager):** Mengkoordinasikan timeline, membangun halaman Admin Panel (Blade), serta mengelola testing & deployment.

---

## 🛠️ Teknologi yang Digunakan

Proyek ini menggunakan arsitektur **Decoupled** (Frontend & Backend terpisah):

1. **Backend & Admin Panel:** Laravel 11 (PHP 8.2)
2. **Frontend Publik:** PHP Native, HTML, CSS, Bootstrap 5
3. **Database:** MySQL / MariaDB
4. **Komunikasi Data:** REST API (JSON)
5. **Hosting:** InfinityFree

---

## ✨ Fitur Unggulan

- 📍 **Pelaporan Berbasis Lokasi:** Integrasi *GPS Picker* pada form laporan.
- 🔍 **Live Tracking:** Cek status real-time dengan resi unik `SPH-XXXXXX`.
- ⚠️ **Manajemen Risiko:** Kategorisasi sampah berdasarkan level risiko (Rendah, Sedang, Tinggi).
- 🔐 **Role-Based Access:** Autentikasi untuk Admin dan Petugas lapangan sesuai dengan spesialisasi mereka.

---

## 🚀 Demo Aplikasi

Aplikasi Silaris telah di-deploy dan siap diakses.

**Akses Admin Panel:**
- **Email:** `admin@Silaris.id`
- **Password:** `admin123`

*(Catatan: Silakan merujuk pada dokumen README untuk link deployment aplikasi)*

---

# 🙏 Terima Kasih

Mari ciptakan lingkungan yang lebih bersih dan sehat bersama **Silaris**!
