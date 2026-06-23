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

    /**
     * Boot model: generate kode_laporan otomatis sebelum data disimpan pertama kali.
     *
     * Format: SPH-001, SPH-002, ..., SPH-999, SPH-1000, dst.
     * Nomor urut diambil dari ID tertinggi yang sudah ada agar tidak tabrakan
     * meski ada data yang dihapus di tengah.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $laporan) {
            if (empty($laporan->kode_laporan)) {
                $laporan->kode_laporan = self::generateKodeLaporan();
            }
        });
    }

    /**
     * Generate kode laporan berikutnya secara aman (race-condition safe).
     *
     * Strategi:
     * 1. Ambil nomor terbesar dari kode_laporan yang ada di DB.
     * 2. Tambah 1, format dengan padding 3 digit (min), lebih jika perlu.
     */
    public static function generateKodeLaporan(): string
    {
        // Generate kode unik berupa SPH-TANGGAL-RANDOM agar terhindar dari race condition
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
     * Tampilkan URL foto lengkap (jika foto tersimpan di storage).
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : null;
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
