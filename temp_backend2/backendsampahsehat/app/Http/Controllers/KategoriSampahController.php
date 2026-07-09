<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriSampahRequest;
use App\Http\Requests\UpdateKategoriSampahRequest;
use App\Models\KategoriSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriSampahController extends Controller
{
    // =========================================================================
    // INDEX – Daftar Semua Kategori
    // =========================================================================

    /**
     * Tampilkan daftar kategori sampah dengan filter & pencarian.
     *
     * Query params:
     *  - search      : cari berdasarkan nama_kategori atau deskripsi
     *  - level_risiko: filter (rendah|sedang|tinggi)
     *  - status_aktif: filter (1|0)
     */
    public function index(Request $request): View
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'petugas']), 403, 'Anda tidak memiliki akses.');

        $query = KategoriSampah::withCount('laporanSampah');

        // Filter pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter level risiko
        if ($level = $request->input('level_risiko')) {
            $query->where('level_risiko', $level);
        }

        // Filter status aktif
        if ($request->has('status_aktif') && $request->input('status_aktif') !== '') {
            $query->where('status_aktif', $request->boolean('status_aktif'));
        }

        $kategori = $query->orderBy('nama_kategori')->paginate(10)->withQueryString();

        return view('admin.kategori.index', compact('kategori'));
    }

    // =========================================================================
    // CREATE & STORE
    // =========================================================================

    /**
     * Tampilkan form tambah kategori baru.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat menambah kategori.');

        return view('admin.kategori.create');
    }

    /**
     * Simpan kategori baru ke database.
     */
    public function store(StoreKategoriSampahRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat menambah kategori.');

        KategoriSampah::create($request->validated());

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori sampah berhasil ditambahkan.');
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    /**
     * Tampilkan detail kategori beserta daftar laporan yang terkait.
     */
    public function show(KategoriSampah $kategoriSampah): View
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'petugas']), 403);

        $laporan = $kategoriSampah
            ->laporanSampah()
            ->latest()
            ->paginate(10);

        return view('admin.kategori.show', compact('kategoriSampah', 'laporan'));
    }

    // =========================================================================
    // EDIT & UPDATE
    // =========================================================================

    /**
     * Tampilkan form edit kategori.
     */
    public function edit(KategoriSampah $kategoriSampah): View
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat mengedit kategori.');

        return view('admin.kategori.edit', compact('kategoriSampah'));
    }

    /**
     * Simpan perubahan kategori ke database.
     */
    public function update(UpdateKategoriSampahRequest $request, KategoriSampah $kategoriSampah): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat mengedit kategori.');

        $kategoriSampah->update($request->validated());

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    /**
     * Hapus kategori dari database.
     * Cegah penghapusan jika masih ada laporan terkait.
     */
    public function destroy(KategoriSampah $kategoriSampah): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat menghapus kategori.');

        // Guard: jangan hapus jika masih punya laporan aktif
        if ($kategoriSampah->laporanSampah()->exists()) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', "Kategori \"{$kategoriSampah->nama_kategori}\" tidak dapat dihapus karena masih memiliki laporan terkait. Nonaktifkan saja jika tidak ingin digunakan.");
        }

        $nama = $kategoriSampah->nama_kategori;
        $kategoriSampah->delete();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }

    // =========================================================================
    // TOGGLE STATUS AKTIF
    // =========================================================================

    /**
     * Aktifkan/nonaktifkan kategori (toggle).
     * Shortcut tanpa perlu membuka halaman edit.
     */
    public function toggleStatus(KategoriSampah $kategoriSampah): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang dapat mengubah status kategori.');

        $kategoriSampah->update([
            'status_aktif' => ! $kategoriSampah->status_aktif,
        ]);

        $pesan = $kategoriSampah->status_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori \"{$kategoriSampah->nama_kategori}\" berhasil {$pesan}.");
    }
}
