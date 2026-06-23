<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\LaporanSampahController;
use Illuminate\Support\Facades\Route;

// =============================================================================
// ROOT REDIRECT
// =============================================================================

// Redirect root langsung ke halaman login admin
Route::get('/', fn () => redirect()->route('login'));

// =============================================================================
// AUTENTIKASI
// =============================================================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =============================================================================
// AREA ADMIN – Diproteksi Middleware Auth
// =============================================================================

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // 🌟 Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 🌟 Manajemen Kategori Sampah (CRUD Lengkap)
    Route::resource('kategori', KategoriSampahController::class)
         ->parameters(['kategori' => 'kategoriSampah']);

    // Toggle aktif/nonaktif kategori
    Route::patch('kategori/{kategoriSampah}/toggle-status', [KategoriSampahController::class, 'toggleStatus'])
         ->name('kategori.toggle-status');

    // 🌟 Manajemen Laporan Sampah (CRUD Admin)
    Route::resource('laporan', LaporanSampahController::class)
         ->parameters(['laporan' => 'laporanSampah']);

    // Update status & catatan petugas
    Route::get('laporan/{laporanSampah}/status', [LaporanSampahController::class, 'editStatus'])
         ->name('laporan.edit-status');
    Route::patch('laporan/{laporanSampah}/status', [LaporanSampahController::class, 'updateStatus'])
         ->name('laporan.update-status');

    // Filter cepat laporan
    Route::get('laporan/filter/kategori/{kategoriSampah}', [LaporanSampahController::class, 'byKategori'])
         ->name('laporan.by-kategori');
    Route::get('laporan/filter/status/{status}', [LaporanSampahController::class, 'byStatus'])
         ->name('laporan.by-status');
    Route::get('laporan/filter/risiko/{levelRisiko}', [LaporanSampahController::class, 'byRisiko'])
         ->name('laporan.by-risiko');
});

// Alias: /dashboard -> /admin/dashboard (kemudahan akses)
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
     ->middleware('auth')
     ->name('dashboard');
