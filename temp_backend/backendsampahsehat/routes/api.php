<?php

use App\Http\Controllers\Api\KategoriSampahController;
use App\Http\Controllers\Api\LaporanSampahController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// GET /api/kategori -> Untuk dropdown form
Route::get('kategori', [KategoriSampahController::class, 'index'])
    ->name('api.kategori.index');

// POST /api/laporan -> Buat laporan
Route::post('laporan', [LaporanSampahController::class, 'store'])
    ->name('api.laporan.store')
    ->middleware('throttle:3,1');

// GET /api/laporan/{kode} -> Cek status
Route::get('laporan/{kode_laporan}', [LaporanSampahController::class, 'show'])
    ->name('api.laporan.show')
    ->where('kode_laporan', 'SPH-[0-9]+');

// GET /api/laporan-publik -> Daftar laporan publik terbatas
Route::get('laporan-publik', [LaporanSampahController::class, 'laporanPublik'])
    ->name('api.laporan.publik');

// =============================================================================
// FALLBACK – Route tidak ditemukan dalam scope /api/*
// =============================================================================
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint API tidak ditemukan. Periksa URL dan metode HTTP yang digunakan.',
        'data'    => null,
    ], 404);
});
