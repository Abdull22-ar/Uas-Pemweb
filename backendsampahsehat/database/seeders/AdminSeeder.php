<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Buat akun admin demo untuk sistem Silaris.
     */
    public function run(): void
    {
        // Admin Utama
        User::updateOrCreate(
            ['email' => 'admin@Silaris.id'],
            [
                'name'              => 'Admin Silaris',
                'email'             => 'admin@Silaris.id',
                'password'          => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Petugas Lapangan (akun kedua untuk demo)
        User::updateOrCreate(
            ['email' => 'petugas@Silaris.id'],
            [
                'name'              => 'Budi Santoso',
                'email'             => 'petugas@Silaris.id',
                'password'          => Hash::make('petugas123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅  Admin & Petugas demo berhasil dibuat.');
        $this->command->line('   📧 admin@Silaris.id  | 🔑 admin123');
        $this->command->line('   📧 petugas@Silaris.id | 🔑 petugas123');
    }
}

