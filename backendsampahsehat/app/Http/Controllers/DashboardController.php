<?php

namespace App\Http\Controllers;

use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard admin dengan ringkasan statistik.
     *
     * Statistik yang ditampilkan:
     * - Jumlah laporan per status (baru, diproses, selesai, ditolak)
     * - Jumlah laporan dengan kategori risiko tinggi
     * - Total laporan keseluruhan
     * - Jumlah kategori aktif
     * - 5 laporan terbaru
     */
    public function index(): View
    {
        // ── Hitung laporan per status ──────────────────────────────────────
        $totalBaru     = LaporanSampah::where('status', 'baru')->count();
        $totalDiproses = LaporanSampah::where('status', 'diproses')->count();
        $totalSelesai  = LaporanSampah::where('status', 'selesai')->count();
        $totalDitolak  = LaporanSampah::where('status', 'ditolak')->count();

        // ── Hitung laporan risiko tinggi (bergabung dengan tabel kategori) ─
        $totalRisikoTinggi = LaporanSampah::whereHas('kategori', function ($query) {
            $query->where('level_risiko', 'tinggi');
        })->count();

        // ── Hitung laporan yang belum ditangani (baru + diproses) ─────────
        $totalBelumSelesai = $totalBaru + $totalDiproses;

        // ── Total keseluruhan ──────────────────────────────────────────────
        $totalLaporan  = LaporanSampah::count();
        $totalKategori = KategoriSampah::where('status_aktif', true)->count();

        // ── Persentase penyelesaian ────────────────────────────────────────
        $persentaseSelesai = $totalLaporan > 0
            ? round(($totalSelesai / $totalLaporan) * 100, 1)
            : 0;

        // ── 5 laporan terbaru untuk tabel ringkasan ────────────────────────
        $laporanTerbaru = LaporanSampah::with('kategori')
            ->latest()
            ->limit(5)
            ->get();

        // ── Laporan per kategori (untuk grafik/ringkasan) ──────────────────
        $laporanPerKategori = KategoriSampah::withCount('laporanSampah')
            ->where('status_aktif', true)
            ->orderByDesc('laporan_sampah_count')
            ->limit(5)
            ->get();

        // ── Laporan risiko tinggi yang masih aktif ─────────────────────────
        $laporanRisikoTinggiAktif = LaporanSampah::with('kategori')
            ->whereHas('kategori', fn ($q) => $q->where('level_risiko', 'tinggi'))
            ->whereIn('status', ['baru', 'diproses'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalBaru',
            'totalDiproses',
            'totalSelesai',
            'totalDitolak',
            'totalRisikoTinggi',
            'totalBelumSelesai',
            'totalLaporan',
            'totalKategori',
            'persentaseSelesai',
            'laporanTerbaru',
            'laporanPerKategori',
            'laporanRisikoTinggiAktif',
        ));
    }
}
