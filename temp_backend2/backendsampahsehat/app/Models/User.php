<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'role', 'spesialis_risiko', 'kategori_id', 'latitude', 'longitude', 'lokasi', 'kontak'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function kategori()
    {
        return $this->belongsTo(\App\Models\KategoriSampah::class, 'kategori_id');
    }

    public function laporanSampahDitugaskan(): HasMany
    {
        return $this->hasMany(LaporanSampah::class, 'petugas_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'latitude'          => 'float',
            'longitude'         => 'float',
        ];
    }
}
