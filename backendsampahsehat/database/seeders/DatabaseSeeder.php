<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Urutan pemanggilan seeder penting:
     *   1. AdminSeeder          – buat user admin & petugas
     *   2. KategoriSampahSeeder – isi master kategori (diperlukan seeder berikutnya)
     *   3. LaporanSampahSeeder  – isi data laporan dummy (FK ke kategori)
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            KategoriSampahSeeder::class,
            LaporanSampahSeeder::class,
        ]);
    }
}
