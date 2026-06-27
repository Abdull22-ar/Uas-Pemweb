<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@Silaris.id'],
            [
                'name'              => 'Admin Silaris',
                'email'             => 'admin@Silaris.id',
                'password'          => Hash::make('admin123'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // ── HAPUS SEMUA PETUGAS LAMA ─────────────────────────────────
        User::where('role', 'petugas')->delete();

        // ── PETUGAS (Login Akun) ─────────────────────────────────────
        $petugas = [
            ['name' => 'Petugas Rendah', 'email' => 'petugas_rendah@Silaris.id', 'risiko' => 'rendah', 'kontak' => '081234567891'],
            ['name' => 'Petugas Sedang', 'email' => 'petugas_sedang@Silaris.id', 'risiko' => 'sedang', 'kontak' => '081234567892'],
            ['name' => 'Petugas Tinggi', 'email' => 'petugas_tinggi@Silaris.id', 'risiko' => 'tinggi', 'kontak' => '081234567893'],
        ];

        foreach ($petugas as $data) {
            User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'kontak'            => $data['kontak'],
                'password'          => Hash::make('petugas123'),
                'role'              => 'petugas',
                'spesialis_risiko'  => $data['risiko'],
                'email_verified_at' => now(),
                'latitude'          => -7.2575 + rand(-150, 150) / 1000,
                'longitude'         => 112.7521 + rand(-150, 150) / 1000,
                'lokasi'            => 'Area ' . ucfirst($data['risiko']),
            ]);
        }

        // ── PETUGAS LAPANGAN (Assignable) ────────────────────────────
        $petugasLapangan = [
            // Rendah
            ['name' => 'Bayu',   'email' => 'bayu@silaris.id',   'risiko' => 'rendah', 'kontak' => '081234567001', 'spesialisasi' => 'Kaca & Keramik'],
            ['name' => 'Asep',   'email' => 'asep@silaris.id',   'risiko' => 'rendah', 'kontak' => '081234567002', 'spesialisasi' => 'Kertas & Kardus'],
            ['name' => 'Riski',  'email' => 'riski@silaris.id',  'risiko' => 'rendah', 'kontak' => '081234567003', 'spesialisasi' => 'Organik'],
            // Sedang
            ['name' => 'Rangga', 'email' => 'rangga@silaris.id', 'risiko' => 'sedang', 'kontak' => '081234567004', 'spesialisasi' => 'Elektronik (E-Waste)'],
            ['name' => 'Firman', 'email' => 'firman@silaris.id', 'risiko' => 'sedang', 'kontak' => '081234567005', 'spesialisasi' => 'Logam & Kaleng'],
            ['name' => 'Brian',  'email' => 'brian@silaris.id',  'risiko' => 'sedang', 'kontak' => '081234567006', 'spesialisasi' => 'Plastik'],
            ['name' => 'Jihan',  'email' => 'jihan@silaris.id',  'risiko' => 'sedang', 'kontak' => '081234567007', 'spesialisasi' => 'Tekstil & Pakaian'],
            // Tinggi
            ['name' => 'Rehan',  'email' => 'rehan@silaris.id',  'risiko' => 'tinggi', 'kontak' => '081234567008', 'spesialisasi' => 'Limbah Berbahaya & Racun (B3)'],
            ['name' => 'Guntur', 'email' => 'guntur@silaris.id', 'risiko' => 'tinggi', 'kontak' => '081234567009', 'spesialisasi' => 'Konstruksi & Bangunan'],
            ['name' => 'Heru',   'email' => 'heru@silaris.id',   'risiko' => 'tinggi', 'kontak' => '081234567010', 'spesialisasi' => 'Medis & Bahan Infeksius'],
        ];

        foreach ($petugasLapangan as $data) {
            User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'kontak'            => $data['kontak'],
                'password'          => Hash::make('petugas123'),
                'role'              => 'petugas',
                'spesialis_risiko'  => $data['risiko'],
                'email_verified_at' => now(),
                'latitude'          => -7.2575 + rand(-150, 150) / 1000,
                'longitude'         => 112.7521 + rand(-150, 150) / 1000,
                'lokasi'            => 'Menangani: ' . $data['spesialisasi'],
            ]);
        }

        $this->command->info('✅  1 Admin, 3 Petugas Login, & 10 Petugas Lapangan berhasil dibuat.');
    }
}