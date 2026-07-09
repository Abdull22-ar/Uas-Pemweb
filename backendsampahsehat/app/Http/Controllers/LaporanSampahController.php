<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanSampahRequest;
use App\Http\Requests\UpdateStatusLaporanRequest;
use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
     *  - search        : cari kode_laporan, nama_pelapor, atau lokasi
     *  - kategori_id   : filter berdasarkan kategori
     *  - status        : filter berdasarkan status laporan
     *  - level_risiko  : filter berdasarkan level risiko kategori
     *  - tanggal_dari  : filter laporan dari tanggal (YYYY-MM-DD)
     *  - tanggal_sampai: filter laporan sampai tanggal (YYYY-MM-DD)
     *  - per_page      : jumlah item per halaman (default 15)
     */
    public function index(Request $request): View
    {
        $query = LaporanSampah::with('kategori', 'petugas')->latest();

        // Petugas hanya melihat laporan sesuai spesialisasi risiko
        $user = auth()->user();
        if ($user->role === 'petugas' && $user->spesialis_risiko) {
            $query->whereHas('kategori', fn($q) =>
                $q->where('level_risiko', $user->spesialis_risiko)
            );
        }

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

        $perPage = max(1, min((int) $request->input('per_page', 15), 100));
        $laporan = $query->paginate($perPage)->withQueryString();

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
            $file = $request->file('foto');
            
            // Generate nama file unik untuk menghindari overwrite
            $extension = $file->getClientOriginalExtension();
            $filenameOnly = uniqid() . '_' . time() . '.' . $extension;
            $relativePath = 'laporan/foto/' . $filenameOnly;
            
            // Pastikan direktori tujuan ada
            $destinationPath = public_path('laporan/foto');
            if (!is_dir($destinationPath)) {
                @mkdir($destinationPath, 0755, true);
            }
            
            // Pindahkan file ke folder public/laporan/foto
            $file->move($destinationPath, $filenameOnly);
            
            $data['foto'] = $relativePath;
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
        $user = auth()->user();
        if ($user->role === 'petugas' && $user->spesialis_risiko) {
            $allowed = $laporanSampah->kategori?->level_risiko === $user->spesialis_risiko;
            abort_unless($allowed, 403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $laporanSampah->load('kategori', 'petugas');

        return view('admin.laporan.show', compact('laporanSampah'));
    }


    // =========================================================================
    // DESTROY
    // =========================================================================

    /**
     * Hapus laporan dari database beserta fotonya.
     */
    public function destroy(LaporanSampah $laporanSampah): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat menghapus laporan.');

        $kode = $laporanSampah->kode_laporan;

        // Hapus file foto dari public folder
        if ($laporanSampah->foto) {
            $filePath = public_path($laporanSampah->foto);
            @unlink($filePath);
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
        $laporanSampah->load('kategori', 'petugas');

        $user = auth()->user();
        if ($user->role === 'petugas' && $user->spesialis_risiko) {
            $allowed = $laporanSampah->kategori?->level_risiko === $user->spesialis_risiko;
            abort_unless($allowed, 403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        // Petugas lapangan (assignable) per level risiko
        $petugasRendah = User::where('role', 'petugas')
            ->where('spesialis_risiko', 'rendah')
            ->where('email', '!=', 'petugas_rendah@Silaris.id')
            ->whereNotNull('kontak')
            ->get(['id', 'name', 'kontak', 'lokasi']);

        $petugasSedang = User::where('role', 'petugas')
            ->where('spesialis_risiko', 'sedang')
            ->where('email', '!=', 'petugas_sedang@Silaris.id')
            ->whereNotNull('kontak')
            ->get(['id', 'name', 'kontak', 'lokasi']);

        $petugasTinggi = User::where('role', 'petugas')
            ->where('spesialis_risiko', 'tinggi')
            ->where('email', '!=', 'petugas_tinggi@Silaris.id')
            ->whereNotNull('kontak')
            ->get(['id', 'name', 'kontak', 'lokasi']);

        return view('admin.laporan.edit-status', compact('laporanSampah', 'petugasRendah', 'petugasSedang', 'petugasTinggi'));
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
        $data = $request->validated();
        
        $statusBaru = $data['status'];

        // Petugas wajib dipilih jika status diproses untuk semua level risiko
        $level = $laporanSampah->kategori?->level_risiko;
        if ($statusBaru === 'diproses' && in_array($level, ['rendah', 'sedang', 'tinggi'])) {
            $request->validate(['petugas_id' => 'required|exists:users,id']);
        }

        if ($request->filled('petugas_id')) {
            $data['petugas_id'] = $request->petugas_id;
        }

        // Update foto: hapus yang lama jika ada foto baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($laporanSampah->foto) {
                $oldFilePath = public_path($laporanSampah->foto);
                @unlink($oldFilePath);
            }
            
            // Upload foto baru
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $filenameOnly = uniqid() . '_' . time() . '.' . $extension;
            $relativePath = 'laporan/foto/' . $filenameOnly;
            
            $destinationPath = public_path('laporan/foto');
            if (!is_dir($destinationPath)) {
                @mkdir($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filenameOnly);
            $data['foto'] = $relativePath;
        }

        // Hapus foto jika user memilih opsi "hapus foto"
        if ($request->boolean('hapus_foto') && $laporanSampah->foto && !$request->hasFile('foto')) {
            $oldFilePath = public_path($laporanSampah->foto);
            @unlink($oldFilePath);
            $data['foto'] = null;
        }

        $laporanSampah->update($data);

        $pesan = "Status laporan {$laporanSampah->kode_laporan} berhasil diubah dari '{$statusLama}' menjadi '{$statusBaru}'.";

        return redirect()
            ->route('admin.laporan.show', $laporanSampah)
            ->with('success', $pesan);
    }

    // =========================================================================
    // FILTER CEPAT (Helper Shortcut)
    // =========================================================================

    /**
     * Filter laporan berdasarkan status tertentu.
     * Shortcut dari dashboard untuk melihat detail laporan per-status.
     */
    public function byStatus(string $status): RedirectResponse
    {
        return redirect()->route('admin.laporan.index', ['status' => $status]);
    }

    /**
     * Filter laporan berdasarkan kategori tertentu.
     * Shortcut dari sidebar/dashboard.
     */
    public function byKategori(KategoriSampah $kategoriSampah): RedirectResponse
    {
        return redirect()->route('admin.laporan.index', ['kategori_id' => $kategoriSampah->id]);
    }

    /**
     * Filter laporan berdasarkan level risiko kategori (misal: "tinggi").
     */
    public function byRisiko(string $levelRisiko): RedirectResponse
    {
        return redirect()->route('admin.laporan.index', ['level_risiko' => $levelRisiko]);
    }

    // =========================================================================
    // INLINE ASSIGN PETUGAS
    // =========================================================================

    public function assignPetugas(Request $request, LaporanSampah $laporanSampah): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat menugaskan petugas.');

        $request->validate(['petugas_id' => 'nullable|exists:users,id']);

        $laporanSampah->update(['petugas_id' => $request->petugas_id]);

        $pesan = $request->petugas_id
            ? "Petugas berhasil ditugaskan ke laporan {$laporanSampah->kode_laporan}."
            : "Penugasan petugas untuk laporan {$laporanSampah->kode_laporan} telah dihapus.";

        return redirect()->back()->with('success', $pesan);
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
