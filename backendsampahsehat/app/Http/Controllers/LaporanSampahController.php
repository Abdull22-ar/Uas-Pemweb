<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanSampahRequest;
use App\Http\Requests\UpdateLaporanSampahRequest;
use App\Http\Requests\UpdateStatusLaporanRequest;
use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LaporanSampahController extends Controller
{
    // =========================================================================
    // INDEX – Daftar Laporan dengan Filter
    // =========================================================================

    /**
     * Tampilkan daftar laporan dengan filter multi-kriteria.
     *
     * Query params yang didukung:
     *  - search      : cari kode_laporan, nama_pelapor, atau lokasi
     *  - kategori_id : filter berdasarkan kategori
     *  - status      : filter berdasarkan status laporan
     *  - level_risiko: filter berdasarkan level risiko kategori
     *  - tanggal_dari: filter laporan dari tanggal (YYYY-MM-DD)
     *  - tanggal_sampai: filter laporan sampai tanggal (YYYY-MM-DD)
     *  - per_page    : jumlah item per halaman (default 15)
     */
    public function index(Request $request): View
    {
        $query = LaporanSampah::with('kategori')->latest();

        // ── Filter pencarian teks ──────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_laporan', 'like', "%{$search}%")
                  ->orWhere('nama_pelapor', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // ── Filter berdasarkan kategori ────────────────────────────────────
        if ($kategoriId = $request->input('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        // ── Filter berdasarkan status laporan ──────────────────────────────
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // ── Filter berdasarkan level risiko kategori ───────────────────────
        if ($levelRisiko = $request->input('level_risiko')) {
            $query->whereHas('kategori', function ($q) use ($levelRisiko) {
                $q->where('level_risiko', $levelRisiko);
            });
        }

        // ── Filter berdasarkan rentang tanggal ────────────────────────────
        if ($tanggalDari = $request->input('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $tanggalDari);
        }

        if ($tanggalSampai = $request->input('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $tanggalSampai);
        }

        $perPage  = min((int) $request->input('per_page', 15), 100); // max 100
        $laporan  = $query->paginate($perPage)->withQueryString();

        // Data untuk dropdown filter
        $kategoriList = KategoriSampah::aktif()->orderBy('nama_kategori')->get();

        return view('admin.laporan.index', compact('laporan', 'kategoriList'));
    }

    // =========================================================================
    // CREATE & STORE
    // =========================================================================

    /**
     * Tampilkan form pengajuan laporan baru.
     * Dapat diakses oleh publik (pelapor).
     */
    public function create(): View
    {
        $kategoriList = KategoriSampah::aktif()->orderBy('nama_kategori')->get();

        return view('laporan.create', compact('kategoriList'));
    }

    /**
     * Simpan laporan baru ke database.
     * kode_laporan di-generate otomatis oleh Model (boot method).
     */
    public function store(StoreLaporanSampahRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('laporan/foto', 'public');
        }

        $laporan = LaporanSampah::create($data);

        return redirect()
            ->route('laporan.show', $laporan)
            ->with('success', "Laporan berhasil dikirim! Kode laporan Anda: {$laporan->kode_laporan}. Simpan kode ini untuk memantau status laporan.");
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    /**
     * Tampilkan detail laporan.
     */
    public function show(LaporanSampah $laporanSampah): View
    {
        $laporanSampah->load('kategori');

        return view('admin.laporan.show', compact('laporanSampah'));
    }

    // =========================================================================
    // EDIT & UPDATE
    // =========================================================================

    /**
     * Tampilkan form edit laporan (admin only).
     */
    public function edit(LaporanSampah $laporanSampah): View
    {
        $kategoriList = KategoriSampah::aktif()->orderBy('nama_kategori')->get();

        return view('admin.laporan.edit', compact('laporanSampah', 'kategoriList'));
    }

    /**
     * Simpan perubahan laporan ke database.
     */
    public function update(UpdateLaporanSampahRequest $request, LaporanSampah $laporanSampah): RedirectResponse
    {
        $data = $request->validated();

        // Update foto: hapus yang lama jika ada foto baru
        if ($request->hasFile('foto')) {
            if ($laporanSampah->foto) {
                Storage::disk('public')->delete($laporanSampah->foto);
            }
            $data['foto'] = $request->file('foto')->store('laporan/foto', 'public');
        }

        // Hapus foto jika user memilih opsi "hapus foto"
        if ($request->boolean('hapus_foto') && $laporanSampah->foto) {
            Storage::disk('public')->delete($laporanSampah->foto);
            $data['foto'] = null;
        }

        $laporanSampah->update($data);

        return redirect()
            ->route('admin.laporan.show', $laporanSampah)
            ->with('success', "Laporan {$laporanSampah->kode_laporan} berhasil diperbarui.");
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    /**
     * Hapus laporan dari database beserta fotonya.
     */
    public function destroy(LaporanSampah $laporanSampah): RedirectResponse
    {
        $kode = $laporanSampah->kode_laporan;

        // Hapus file foto dari storage
        if ($laporanSampah->foto) {
            Storage::disk('public')->delete($laporanSampah->foto);
        }

        $laporanSampah->delete();

        return redirect()
            ->route('admin.laporan.index')
            ->with('success', "Laporan {$kode} berhasil dihapus.");
    }

    // =========================================================================
    // UPDATE STATUS (Fungsi Khusus Petugas)
    // =========================================================================

    /**
     * Tampilkan form update status laporan.
     */
    public function editStatus(LaporanSampah $laporanSampah): View
    {
        $laporanSampah->load('kategori');

        return view('admin.laporan.edit-status', compact('laporanSampah'));
    }

    /**
     * Update status laporan dan catatan petugas.
     *
     * Validasi khusus:
     *  - Status harus salah satu enum yang valid
     *  - catatan_petugas wajib diisi jika status = 'ditolak'
     */
    public function updateStatus(UpdateStatusLaporanRequest $request, LaporanSampah $laporanSampah): RedirectResponse
    {
        $statusLama = $laporanSampah->status;
        $statusBaru = $request->validated()['status'];

        $laporanSampah->update($request->validated());

        $pesan = "Status laporan {$laporanSampah->kode_laporan} berhasil diubah dari '{$statusLama}' menjadi '{$statusBaru}'.";

        return redirect()
            ->route('admin.laporan.show', $laporanSampah)
            ->with('success', $pesan);
    }

    // =========================================================================
    // FILTER CEPAT (Helper Shortcut)
    // =========================================================================

    /**
     * Filter laporan berdasarkan kategori tertentu.
     * Shortcut dari index dengan pre-fill filter kategori_id.
     */
    public function byKategori(KategoriSampah $kategoriSampah): View
    {
        $laporan = LaporanSampah::with('kategori')
            ->where('kategori_id', $kategoriSampah->id)
            ->latest()
            ->paginate(15);

        $kategoriList = KategoriSampah::aktif()->orderBy('nama_kategori')->get();

        return view('admin.laporan.index', compact('laporan', 'kategoriList'))
            ->with('filterKategori', $kategoriSampah);
    }

    /**
     * Filter laporan berdasarkan status tertentu.
     * Shortcut dari dashboard untuk melihat detail laporan per-status.
     */
    public function byStatus(string $status): View|RedirectResponse
    {
        $statusValid = ['baru', 'diproses', 'selesai', 'ditolak'];

        if (! in_array($status, $statusValid)) {
            return redirect()
                ->route('admin.laporan.index')
                ->with('error', 'Status tidak valid.');
        }

        $laporan = LaporanSampah::with('kategori')
            ->where('status', $status)
            ->latest()
            ->paginate(15);

        $kategoriList = KategoriSampah::aktif()->orderBy('nama_kategori')->get();

        return view('admin.laporan.index', compact('laporan', 'kategoriList'))
            ->with('filterStatus', $status);
    }

    /**
     * Filter laporan berdasarkan level risiko kategori.
     * Berguna untuk penanganan prioritas (tampilkan risiko tinggi duluan).
     */
    public function byRisiko(string $levelRisiko): View|RedirectResponse
    {
        $levelValid = ['rendah', 'sedang', 'tinggi'];

        if (! in_array($levelRisiko, $levelValid)) {
            return redirect()
                ->route('admin.laporan.index')
                ->with('error', 'Level risiko tidak valid.');
        }

        $laporan = LaporanSampah::with('kategori')
            ->whereHas('kategori', fn ($q) => $q->where('level_risiko', $levelRisiko))
            ->latest()
            ->paginate(15);

        $kategoriList = KategoriSampah::aktif()->orderBy('nama_kategori')->get();

        return view('admin.laporan.index', compact('laporan', 'kategoriList'))
            ->with('filterRisiko', $levelRisiko);
    }

    // =========================================================================
    // TRACKING PUBLIK
    // =========================================================================

    /**
     * Tampilkan halaman tracking laporan berdasarkan kode_laporan.
     * Dapat diakses oleh publik (pelapor ingin cek status).
     */
    public function track(Request $request): View
    {
        $laporan = null;

        if ($kode = $request->input('kode')) {
            $laporan = LaporanSampah::with('kategori')
                ->where('kode_laporan', strtoupper(trim($kode)))
                ->first();
        }

        return view('laporan.track', compact('laporan'));
    }
}
