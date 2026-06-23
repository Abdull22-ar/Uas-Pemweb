<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSampah extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'kategori_sampah';

    /**
     * Kolom yang boleh diisi secara massal.
     */
    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'level_risiko',
        'status_aktif',
    ];

    /**
     * Casting tipe data kolom.
     */
    protected $casts = [
        'status_aktif' => 'boolean',
        'level_risiko' => 'string',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Satu kategori memiliki banyak laporan sampah.
     */
    public function laporanSampah(): HasMany
    {
        return $this->hasMany(LaporanSampah::class, 'kategori_id');
    }

    // =========================================================================
    // SCOPE QUERY (HELPER)
    // =========================================================================

    /**
     * Scope untuk mengambil hanya kategori yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Scope untuk filter berdasarkan level risiko.
     */
    public function scopeLevelRisiko($query, string $level)
    {
        return $query->where('level_risiko', $level);
    }

    // =========================================================================
    // ACCESSOR
    // =========================================================================

    /**
     * Tampilkan label badge level risiko (untuk API response).
     */
    public function getLabelRisikoAttribute(): string
    {
        return match ($this->level_risiko) {
            'rendah' => '🟢 Rendah',
            'sedang' => '🟡 Sedang',
            'tinggi' => '🔴 Tinggi',
            default  => 'Tidak Diketahui',
        };
    }
}
