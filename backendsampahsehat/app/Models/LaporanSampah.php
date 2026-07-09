<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanSampah extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'laporan_sampah';

    /**
     * Kolom yang boleh diisi secara massal.
     */
    protected $fillable = [
        'kode_laporan',
        'nama_pelapor',
        'kontak_pelapor',
        'kategori_id',
        'lokasi',
        'latitude',
        'longitude',
        'deskripsi',
        'foto',
        'status',
        'petugas_id',
        'catatan_petugas',
    ];

    /**
     * Casting tipe data kolom.
     */
    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    // =========================================================================
    // BOOT - AUTO-GENERATE KODE LAPORAN
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $laporan) {
            if (empty($laporan->kode_laporan)) {
                $laporan->kode_laporan = self::generateKodeLaporan();
            }
        });
    }

    public static function generateKodeLaporan(): string
    {
        do {
            $kode = 'SPH-' . date('ymd') . mt_rand(1000, 9999);
        } while (static::where('kode_laporan', $kode)->exists());

        return $kode;
    }

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Laporan ini dimiliki oleh satu kategori sampah.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    // =========================================================================
    // SCOPE QUERY (HELPER)
    // =========================================================================

    /**
     * Scope untuk filter berdasarkan status laporan.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk laporan baru yang belum diproses.
     */
    public function scopeBaru($query)
    {
        return $query->where('status', 'baru');
    }

    /**
     * Scope untuk laporan yang sedang diproses.
     */
    public function scopeDiproses($query)
    {
        return $query->where('status', 'diproses');
    }

    // =========================================================================
    // ACCESSOR
    // =========================================================================

    /**
     * Tampilkan label status dalam Bahasa Indonesia.
     */
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'baru'     => '🆕 Baru',
            'diproses' => '⚙️ Sedang Diproses',
            'selesai'  => '✅ Selesai',
            'ditolak'  => '❌ Ditolak',
            default    => 'Tidak Diketahui',
        };
    }

    /**
     * Tampilkan URL foto lengkap (jika foto tersimpan di public folder).
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }
        
        // Langsung return asset URL tanpa cek file_exists() karena sering gagal di shared hosting (cPanel)
        return asset($this->foto);
    }

    /**
     * Kembalikan koordinat sebagai array [lat, lng].
     */
    public function getKoordinatAttribute(): ?array
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return [
                'latitude'  => $this->latitude,
                'longitude' => $this->longitude,
            ];
        }

        return null;
    }
}
