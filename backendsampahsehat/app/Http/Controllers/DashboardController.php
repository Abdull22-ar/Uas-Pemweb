<?php

namespace App\Http\Controllers;

use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // ── Base query ────────────────────────────────────────────────────
        $laporanQuery = LaporanSampah::query();

        // Petugas hanya melihat laporan sesuai spesialisasi risiko
        if ($user->role === 'petugas' && $user->spesialis_risiko) {
            $laporanQuery->whereHas('kategori', fn($q) =>
                $q->where('level_risiko', $user->spesialis_risiko)
            );
        }

        // ── Hitung per status ──────────────────────────────────────────────
        $clone = clone $laporanQuery;
        $statusCounts = $clone->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalBaru     = $statusCounts['baru'] ?? 0;
        $totalDiproses = $statusCounts['diproses'] ?? 0;
        $totalSelesai  = $statusCounts['selesai'] ?? 0;
        $totalDitolak  = $statusCounts['ditolak'] ?? 0;

        $totalBelumSelesai = $totalBaru + $totalDiproses;
        $totalLaporan      = (clone $laporanQuery)->count();
        $totalKategori     = KategoriSampah::where('status_aktif', true)->count();
        $totalPetugas      = User::where('role', 'petugas')->count();

        // ── Risiko tinggi ──────────────────────────────────────────────────
        $totalRisikoTinggi = (clone $laporanQuery)
            ->whereHas('kategori', fn($q) => $q->where('level_risiko', 'tinggi'))
            ->count();

        // ── Persentase ────────────────────────────────────────────────────
        $persentaseSelesai = $totalLaporan > 0
            ? round(($totalSelesai / $totalLaporan) * 100, 1)
            : 0;

        // ── 5 laporan terbaru ──────────────────────────────────────────────
        $laporanTerbaru = (clone $laporanQuery)
            ->with('kategori')
            ->latest()
            ->limit(5)
            ->get();

        // ── Laporan risiko tinggi aktif ────────────────────────────────────
        $laporanRisikoTinggiAktif = (clone $laporanQuery)
            ->with('kategori')
            ->whereHas('kategori', fn($q) => $q->where('level_risiko', 'tinggi'))
            ->whereIn('status', ['baru', 'diproses'])
            ->latest()
            ->limit(5)
            ->get();

        // ── Data untuk Leaflet Map ─────────────────────────────────────────
        $laporanMapData = (clone $laporanQuery)
            ->with('kategori')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->get()
            ->map(fn($l) => [
                'kode'       => $l->kode_laporan,
                'lat'        => (float) $l->latitude,
                'lng'        => (float) $l->longitude,
                'lokasi'     => $l->lokasi,
                'status'     => $l->status,
                'label_status' => $l->label_status,
                'risiko'     => $l->kategori?->level_risiko ?? 'rendah',
                'label_risiko' => $l->kategori?->label_risiko ?? 'rendah',
                'kategori'   => $l->kategori?->nama_kategori ?? '-',
                'pelapor'    => $l->nama_pelapor,
            ]);

        // ── Data laporan harian (7 hari terakhir) ──────────────────────────
        $laporanHarian = (clone $laporanQuery)
            ->select(DB::raw("DATE(created_at) as tgl"), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $tglKey = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('D, d M');
            $chartData[]   = $laporanHarian[$tglKey] ?? 0;
        }

        // ── Kategori per level risiko ──────────────────────────────────────
        $kategoriPerRisiko = [];
        foreach (['rendah', 'sedang', 'tinggi'] as $level) {
            $query = KategoriSampah::where('status_aktif', true)
                ->where('level_risiko', $level)
                ->withCount(['laporanSampah' => fn($q) =>
                    $user->role === 'petugas' && $user->spesialis_risiko
                        ? $q->whereHas('kategori', fn($q2) => $q2->where('level_risiko', $user->spesialis_risiko))
                        : $q
                ])
                ->orderBy('laporan_sampah_count', 'desc');

            $kategoriPerRisiko[$level] = $query->get();
        }

        // ── Penanganan harian per risiko (khusus admin) ────────────────────
        $penangananHarian = collect();
        if ($user->role === 'admin') {
            $penangananHarian = LaporanSampah::select(
                    DB::raw("DATE(laporan_sampah.updated_at) as tgl"),
                    'kategori_sampah.level_risiko',
                    DB::raw('count(*) as total')
                )
                ->join('kategori_sampah', 'laporan_sampah.kategori_id', '=', 'kategori_sampah.id')
                ->where('laporan_sampah.status', 'selesai')
                ->where('laporan_sampah.updated_at', '>=', now()->subDays(6)->startOfDay())
                ->groupBy('tgl', 'kategori_sampah.level_risiko')
                ->orderBy('tgl')
                ->get();
        }

        // ── Laporan diproses dengan petugas (admin only) ─────────────────────
        $laporanDiprosesPetugas = collect();
        if ($user->role === 'admin') {
            $laporanDiprosesPetugas = LaporanSampah::with('kategori', 'petugas')
                ->where('status', 'diproses')
                ->whereNotNull('petugas_id')
                ->latest()
                ->take(10)
                ->get();
        }

        // ── Data petugas dengan lokasi (untuk peta) ─────────────────────────
        $petugasMapData = collect();
        if ($user->role === 'admin') {
            $petugasMapData = User::where('role', 'petugas')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'name', 'spesialis_risiko', 'latitude', 'longitude', 'lokasi'])
                ->map(fn($p) => [
                    'id'       => $p->id,
                    'nama'     => $p->name,
                    'lat'      => (float) $p->latitude,
                    'lng'      => (float) $p->longitude,
                    'risiko'   => $p->spesialis_risiko ?? 'rendah',
                    'lokasi'   => $p->lokasi,
                ]);
        }

        return view('admin.dashboard', compact(
            'totalBaru',
            'totalDiproses',
            'totalSelesai',
            'totalDitolak',
            'totalRisikoTinggi',
            'totalBelumSelesai',
            'totalLaporan',
            'totalKategori',
            'totalPetugas',
            'persentaseSelesai',
            'laporanTerbaru',
            'laporanRisikoTinggiAktif',
            'laporanMapData',
            'chartLabels',
            'chartData',
            'kategoriPerRisiko',
            'penangananHarian',
            'laporanDiprosesPetugas',
            'petugasMapData',
        ));
    }
}