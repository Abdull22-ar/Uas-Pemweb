<?php

namespace Database\Seeders;

use App\Models\KategoriSampah;
use Illuminate\Database\Seeder;

class KategoriSampahSeeder extends Seeder
{
    /**
     * Seed data master kategori sampah beserta level risiko kesehatannya.
     */
    public function run(): void
    {
        $kategori = [
            // ── RISIKO RENDAH ────────────────────────────────────────────────
            [
                'nama_kategori' => 'Sampah Organik',
                'deskripsi'     => 'Sampah yang berasal dari bahan organik seperti sisa makanan, daun, dan ranting. Dapat diolah menjadi kompos.',
                'level_risiko'  => 'rendah',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Sampah Kertas & Kardus',
                'deskripsi'     => 'Sampah berbahan dasar kertas seperti koran, majalah, kardus bekas, dan buku. Dapat didaur ulang.',
                'level_risiko'  => 'rendah',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Sampah Kaca & Keramik',
                'deskripsi'     => 'Pecahan kaca, botol kaca, dan keramik. Perlu penanganan hati-hati karena tepi tajam, namun risiko kesehatan lingkungan rendah.',
                'level_risiko'  => 'rendah',
                'status_aktif'  => true,
            ],

            // ── RISIKO SEDANG ────────────────────────────────────────────────
            [
                'nama_kategori' => 'Sampah Plastik',
                'deskripsi'     => 'Sampah berbahan plastik seperti botol, kantong, dan kemasan. Sulit terurai dan dapat mencemari tanah serta perairan.',
                'level_risiko'  => 'sedang',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Sampah Logam & Kaleng',
                'deskripsi'     => 'Kaleng bekas, besi tua, dan logam lainnya. Berpotensi menimbulkan karat dan menjadi tempat berkembang biak nyamuk.',
                'level_risiko'  => 'sedang',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Sampah Tekstil & Pakaian',
                'deskripsi'     => 'Pakaian bekas, kain perca, dan produk tekstil lainnya. Mengandung pewarna kimia yang dapat mencemari air tanah.',
                'level_risiko'  => 'sedang',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Sampah Elektronik (E-Waste)',
                'deskripsi'     => 'Peralatan elektronik rusak/bekas seperti HP, komputer, dan TV. Mengandung logam berat jika tidak dikelola dengan benar.',
                'level_risiko'  => 'sedang',
                'status_aktif'  => true,
            ],

            // ── RISIKO TINGGI ────────────────────────────────────────────────
            [
                'nama_kategori' => 'Sampah Medis & Bahan Infeksius',
                'deskripsi'     => 'Jarum suntik, perban bekas, obat-obatan kedaluwarsa, dan limbah medis lainnya. Berpotensi menyebarkan penyakit menular.',
                'level_risiko'  => 'tinggi',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Limbah Bahan Berbahaya & Beracun (B3)',
                'deskripsi'     => 'Cat bekas, baterai, pestisida, dan bahan kimia berbahaya lainnya. Sangat berbahaya bagi kesehatan dan lingkungan.',
                'level_risiko'  => 'tinggi',
                'status_aktif'  => true,
            ],
            [
                'nama_kategori' => 'Sampah Konstruksi & Bangunan',
                'deskripsi'     => 'Puing bangunan, asbes, dan material konstruksi lainnya. Debu asbes sangat berbahaya bagi saluran pernapasan.',
                'level_risiko'  => 'tinggi',
                'status_aktif'  => true,
            ],
        ];

        foreach ($kategori as $data) {
            KategoriSampah::updateOrCreate(
                ['nama_kategori' => $data['nama_kategori']],
                $data
            );
        }

        $this->command->info('✅  10 Kategori Sampah berhasil di-seed.');
    }
}
