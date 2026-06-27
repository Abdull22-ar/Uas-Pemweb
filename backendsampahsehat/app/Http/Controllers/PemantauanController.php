<?php

namespace App\Http\Controllers;

use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PemantauanController extends Controller
{
    public function petugas(): View
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Halaman ini khusus admin.');

        // ── Semua petugas dengan lokasi (untuk peta) ─────────────────────
        $petugasMapData = User::where('role', 'petugas')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'spesialis_risiko', 'latitude', 'longitude', 'lokasi', 'kontak'])
            ->map(fn($p) => [
                'id'       => $p->id,
                'nama'     => $p->name,
                'lat'      => (float) $p->latitude,
                'lng'      => (float) $p->longitude,
                'risiko'   => $p->spesialis_risiko ?? 'rendah',
                'lokasi'   => $p->lokasi,
                'kontak'   => $p->kontak,
            ]);

        // ── Laporan sedang diproses dengan petugas ───────────────────────
        $laporanDiproses = LaporanSampah::with('kategori', 'petugas')
            ->where('status', 'diproses')
            ->whereNotNull('petugas_id')
            ->latest()
            ->get();

        // ── Kelompokkan per level risiko ──────────────────────────────────
        $kelompok = [];
        foreach (['rendah', 'sedang', 'tinggi'] as $level) {
            $laporanLevel = $laporanDiproses->filter(fn($l) =>
                $l->kategori?->level_risiko === $level
            );

            // Kelompokkan per kategori
            $perKategori = [];
            foreach ($laporanLevel->groupBy(fn($l) => $l->kategori?->nama_kategori ?? 'Lainnya') as $kategori => $items) {
                $perKategori[] = [
                    'nama_kategori' => $kategori,
                    'laporan'       => $items,
                    'total'         => $items->count(),
                    'petugas_list'  => $items->pluck('petugas.name')->unique()->values()->toArray(),
                ];
            }

            $kelompok[$level] = [
                'total'    => $laporanLevel->count(),
                'kategori' => $perKategori,
            ];
        }

        // ── Kategori aktif per level ──────────────────────────────────────
        $kategoriPerRisiko = [];
        foreach (['rendah', 'sedang', 'tinggi'] as $level) {
            $kategoriPerRisiko[$level] = KategoriSampah::where('status_aktif', true)
                ->where('level_risiko', $level)
                ->orderBy('nama_kategori')
                ->get();
        }

        // ── Data laporan diproses dengan lokasi (untuk peta) ───────────────
        $laporanMapData = $laporanDiproses
            ->filter(fn($l) => $l->latitude && $l->longitude)
            ->map(fn($l) => [
                'kode'       => $l->kode_laporan,
                'lat'        => (float) $l->latitude,
                'lng'        => (float) $l->longitude,
                'lokasi'     => $l->lokasi,
                'status'     => $l->status,
                'label_status' => $l->label_status,
                'petugas'    => $l->petugas?->name ?? '-',
                'kategori'   => $l->kategori?->nama_kategori ?? '-',
                'risiko'     => $l->kategori?->level_risiko ?? 'rendah',
                'label_risiko' => $l->kategori?->label_risiko ?? 'rendah',
            ]);

        // ── Ringkasan ─────────────────────────────────────────────────────
        $totalPetugas = User::where('role', 'petugas')->count();
        $totalDiproses = $laporanDiproses->count();
        $petugasAktif = $laporanDiproses->pluck('petugas_id')->unique()->count();

        return view('admin.pemantauan.petugas', compact(
            'petugasMapData',
            'laporanMapData',
            'kelompok',
            'kategoriPerRisiko',
            'totalPetugas',
            'totalDiproses',
            'petugasAktif',
        ));
    }
}
