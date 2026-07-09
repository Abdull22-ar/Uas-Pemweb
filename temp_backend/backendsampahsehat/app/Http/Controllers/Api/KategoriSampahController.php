<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use Illuminate\Http\JsonResponse;

class KategoriSampahController extends Controller
{
    /**
     * GET /api/kategori-sampah
     *
     * Mengambil semua kategori sampah yang aktif.
     * Digunakan frontend untuk mengisi dropdown pilihan kategori pada form laporan.
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "message": "...",
     *   "data": [ { id, nama_kategori, deskripsi, level_risiko, label_risiko }, ... ],
     *   "meta": { "total": N }
     * }
     */
    public function index(): JsonResponse
    {
        $kategori = KategoriSampah::aktif()
            ->orderBy('level_risiko')        // tampilkan rendah → sedang → tinggi
            ->orderBy('nama_kategori')
            ->get([
                'id',
                'nama_kategori',
                'deskripsi',
                'level_risiko',
            ])
            ->map(fn ($k) => [
                'id'             => $k->id,
                'nama_kategori'  => $k->nama_kategori,
                'deskripsi'      => $k->deskripsi,
                'level_risiko'   => $k->level_risiko,
                'label_risiko'   => $k->label_risiko,    // accessor: 🟢 Rendah, dst.
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori sampah berhasil diambil.',
            'data'    => $kategori,
            'meta'    => [
                'total' => $kategori->count(),
            ],
        ], 200);
    }
}
