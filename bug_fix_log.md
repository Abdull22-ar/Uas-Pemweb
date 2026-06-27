# 🐛 Bug Fix & AI Usage Log

## 🤖 AI Usage Log

Berikut adalah ringkasan bagaimana AI digunakan untuk menganalisis, menemukan, dan merapikan kode pada tahap ini:

| Waktu | Aktivitas AI | Alat yang Digunakan (Tools) |
|---|---|---|
| **Step 1** | Menganalisis file-file Controller (`LaporanSampahController`, `KategoriSampahController`, `DashboardController`) dan Model (`LaporanSampah`, `KategoriSampah`) untuk mengidentifikasi potensi bug. | `list_dir`, `view_file` |
| **Step 2** | Menemukan 3 bug logika dan potensi error terkait file management, pagination limit, dan dokumentasi yang tidak sesuai kode. | Analisis mandiri dari hasil *view file*. |
| **Step 3** | Menemukan 2 area kode yang tidak optimal dan memiliki duplikasi logika (N+1 Query di Dashboard, duplicate view render di shortcut filter). | Analisis mandiri dari hasil *view file*. |
| **Step 4** | Menyusun *Implementation Plan* terperinci dan meminta persetujuan *User* sebelum mengeksekusi perubahan. | `write_to_file` (Plan Mode) |
| **Step 5** | Mengeksekusi perbaikan kode secara efisien ke berbagai file yang menjadi target dalam satu langkah yang aman. | `multi_replace_file_content`, `replace_file_content` |

---

## 🛠️ Bug Fix Log

Berikut adalah dokumentasi sebelum dan sesudah perbaikan dilakukan pada 3 Bug dan 2 Refactoring:

### 🐞 1. Bug: Potensi *Dangling File* Saat Update Laporan
- **File:** `app/Http/Controllers/LaporanSampahController.php` (Method `update`)
- **Masalah:** Jika Admin mengunggah foto baru lalu secara bersamaan mencentang `hapus_foto`, foto baru tersebut akan tersimpan ke storage, tetapi referensinya di database (`$data['foto']`) akan di-set menjadi `null`. Ini menciptakan sampah file di server (*dangling file*).
- **Sebelum:**
  ```php
  // Hapus foto jika user memilih opsi "hapus foto"
  if ($request->boolean('hapus_foto') && $laporanSampah->foto) {
      Storage::disk('public')->delete($laporanSampah->foto);
      $data['foto'] = null;
  }
  ```
- **Sesudah:**
  ```php
  // Hapus foto jika user memilih opsi "hapus foto" dan tidak ada foto baru yang diunggah
  if ($request->boolean('hapus_foto') && $laporanSampah->foto && !$request->hasFile('foto')) {
      Storage::disk('public')->delete($laporanSampah->foto);
      $data['foto'] = null;
  }
  ```

### 🐞 2. Bug: Parameter Pagination Negatif menyebabkan Error 500
- **File:** `app/Http/Controllers/LaporanSampahController.php` (Method `index`)
- **Masalah:** Parameter `per_page` dari query string hanya dibatasi nilai maksimalnya `min(..., 100)`. Jika user menginput `per_page=0` atau angka negatif, Laravel Paginator akan melempar *exception*.
- **Sebelum:**
  ```php
  $perPage  = min((int) $request->input('per_page', 15), 100); // max 100
  ```
- **Sesudah:**
  ```php
  $perPage  = max(1, min((int) $request->input('per_page', 15), 100)); // min 1, max 100
  ```

### 🐞 3. Bug: Dokumentasi Format Kode Laporan Tidak Sinkron
- **File:** `app/Models/LaporanSampah.php` (Method `generateKodeLaporan`)
- **Masalah:** *Docblock* menyebutkan format urut `SPH-001, SPH-002, dst` namun *actual code* menggunakan format `date('ymd')` + `mt_rand(1000, 9999)` untuk mencegah *race condition*. Ini dapat membingungkan developer.
- **Sebelum:**
  ```php
  * Format: SPH-001, SPH-002, ..., SPH-999, SPH-1000, dst.
  * Nomor urut diambil dari ID tertinggi yang sudah ada agar tidak tabrakan
  ```
- **Sesudah:**
  ```php
  * Format: SPH-[YYMMDD][RANDOM-4-DIGIT], misal: SPH-2406261234
  * Menggunakan kombinasi tanggal dan angka acak agar terhindar dari race condition.
  ```

---

## 🧹 Refactoring Log

### ♻️ 1. Refactoring: Optimasi N+1 Queries di Dashboard
- **File:** `app/Http/Controllers/DashboardController.php`
- **Masalah:** Terdapat 4 query terpisah (`count()`) untuk menghitung tiap status laporan. Ini tidak efisien pada tabel dengan record yang banyak.
- **Sebelum:**
  ```php
  $totalBaru     = LaporanSampah::where('status', 'baru')->count();
  $totalDiproses = LaporanSampah::where('status', 'diproses')->count();
  $totalSelesai  = LaporanSampah::where('status', 'selesai')->count();
  $totalDitolak  = LaporanSampah::where('status', 'ditolak')->count();
  ```
- **Sesudah:**
  ```php
  $statusCounts = LaporanSampah::selectRaw('status, count(*) as count')
      ->groupBy('status')
      ->pluck('count', 'status')
      ->toArray();

  $totalBaru     = $statusCounts['baru'] ?? 0;
  $totalDiproses = $statusCounts['diproses'] ?? 0;
  $totalSelesai  = $statusCounts['selesai'] ?? 0;
  $totalDitolak  = $statusCounts['ditolak'] ?? 0;
  ```

### ♻️ 2. Refactoring: Menghilangkan Duplikasi Render View Filter
- **File:** `app/Http/Controllers/LaporanSampahController.php`
- **Masalah:** Fungsi-fungsi helper seperti `byStatus`, `byKategori`, `byRisiko` menduplikasi query logika pagination dan pemanggilan view dari `index()`.
- **Sebelum:**
  ```php
  public function byKategori(KategoriSampah $kategoriSampah): View
  {
      $laporan = LaporanSampah::with('kategori')->where('kategori_id', $kategoriSampah->id)->paginate(15);
      $kategoriList = KategoriSampah::aktif()->get();
      return view('admin.laporan.index', compact('laporan', 'kategoriList'))->with('filterKategori', $kategoriSampah);
  }
  ```
- **Sesudah:**
  ```php
  public function byKategori(KategoriSampah $kategoriSampah): RedirectResponse
  {
      return redirect()->route('admin.laporan.index', ['kategori_id' => $kategoriSampah->id]);
  }
  ```
  *(Hal ini diterapkan ke ketiga fungsi shortcut, membuat kode lebih ramping dan logic fetch/render terpusat pada satu tempat)*.
