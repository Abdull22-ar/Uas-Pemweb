<?php

namespace Database\Seeders;

use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use Illuminate\Database\Seeder;

class LaporanSampahSeeder extends Seeder
{
    /**
     * Seed data laporan sampah dummy yang realistis untuk demo.
     */
    public function run(): void
    {
        // Ambil ID kategori yang sudah ada di DB
        $kategoriSampahPlastik   = KategoriSampah::where('nama_kategori', 'Sampah Plastik')->value('id');
        $kategoriOrganik         = KategoriSampah::where('nama_kategori', 'Sampah Organik')->value('id');
        $kategoriB3              = KategoriSampah::where('nama_kategori', 'Limbah Bahan Berbahaya & Beracun (B3)')->value('id');
        $kategoriMedis           = KategoriSampah::where('nama_kategori', 'Sampah Medis & Bahan Infeksius')->value('id');
        $kategoriElektronik      = KategoriSampah::where('nama_kategori', 'Sampah Elektronik (E-Waste)')->value('id');
        $kategoriLogam           = KategoriSampah::where('nama_kategori', 'Sampah Logam & Kaleng')->value('id');
        $kategoriKonstruksi      = KategoriSampah::where('nama_kategori', 'Sampah Konstruksi & Bangunan')->value('id');

        $laporanDummy = [
            // ── STATUS: BARU ─────────────────────────────────────────────────
            [
                'nama_pelapor'    => 'Rina Kartika',
                'kontak_pelapor'  => '081234567890',
                'kategori_id'     => $kategoriSampahPlastik,
                'lokasi'          => 'Jl. Merdeka No. 12, Kel. Sukamaju, Kec. Cibeunying, Bandung',
                'latitude'        => -6.9175,
                'longitude'       => 107.6191,
                'deskripsi'       => 'Terdapat tumpukan sampah plastik yang sudah menumpuk di pinggir jalan depan gang. Bau menyengat dan mengganggu warga sekitar.',
                'foto'            => null,
                'status'          => 'baru',
                'catatan_petugas' => null,
            ],
            [
                'nama_pelapor'    => 'Agus Setiawan',
                'kontak_pelapor'  => '082198765432',
                'kategori_id'     => $kategoriMedis,
                'lokasi'          => 'Jl. Kebon Jeruk Raya No. 5, Jakarta Barat',
                'latitude'        => -6.1895,
                'longitude'       => 106.7750,
                'deskripsi'       => 'Ditemukan jarum suntik dan perban bekas dibuang sembarangan di area belakang apotek. Sangat berbahaya terutama untuk anak-anak.',
                'foto'            => null,
                'status'          => 'baru',
                'catatan_petugas' => null,
            ],
            [
                'nama_pelapor'    => 'Siti Nurhaliza',
                'kontak_pelapor'  => '085678901234',
                'kategori_id'     => $kategoriOrganik,
                'lokasi'          => 'Pasar Tradisional Gemah Ripah, Jl. Pasar Lama, Semarang',
                'latitude'        => -6.9939,
                'longitude'       => 110.4203,
                'deskripsi'       => 'Sampah sisa pasar berupa sayur dan buah busuk menumpuk sejak 3 hari yang lalu. Lalat berkembang biak dan menjadi sumber penyakit.',
                'foto'            => null,
                'status'          => 'baru',
                'catatan_petugas' => null,
            ],

            // ── STATUS: DIPROSES ──────────────────────────────────────────────
            [
                'nama_pelapor'    => 'Budi Prasetyo',
                'kontak_pelapor'  => '087765432109',
                'kategori_id'     => $kategoriB3,
                'lokasi'          => 'Kawasan Industri Pulogadung Blok F-12, Jakarta Timur',
                'latitude'        => -6.1910,
                'longitude'       => 106.9036,
                'deskripsi'       => 'Limbah kimia cair dibuang ke selokan. Air berubah warna menjadi hitam kemerahan dan berbau menyengat. Warga takut terkena dampak kesehatan.',
                'foto'            => null,
                'status'          => 'diproses',
                'catatan_petugas' => 'Tim lapangan sudah melakukan pengecekan awal. Sampel air diambil untuk uji laboratorium. Menunggu hasil uji sebelum tindakan lanjutan.',
            ],
            [
                'nama_pelapor'    => 'Dewi Rahayu',
                'kontak_pelapor'  => '089934567812',
                'kategori_id'     => $kategoriElektronik,
                'lokasi'          => 'Pasar Loak Elektronik Jl. Brigjen Katamso, Yogyakarta',
                'latitude'        => -7.8012,
                'longitude'       => 110.3647,
                'deskripsi'       => 'Tumpukan barang elektronik rusak seperti TV, komputer lama, dan baterai dibuang sembarangan. Mengandung merkuri dan timbal yang berbahaya.',
                'foto'            => null,
                'status'          => 'diproses',
                'catatan_petugas' => 'Koordinasi dengan Dinas Lingkungan Hidup untuk pengangkutan e-waste terjadwal.',
            ],

            // ── STATUS: SELESAI ───────────────────────────────────────────────
            [
                'nama_pelapor'    => 'Hendra Wijaya',
                'kontak_pelapor'  => '081345678923',
                'kategori_id'     => $kategoriLogam,
                'lokasi'          => 'Jl. Veteran No. 45, Kel. Karang Asem, Surabaya',
                'latitude'        => -7.2575,
                'longitude'       => 112.7521,
                'deskripsi'       => 'Tumpukan kaleng dan besi tua di lahan kosong mengakibatkan genangan air dan menjadi sarang nyamuk Aedes aegypti.',
                'foto'            => null,
                'status'          => 'selesai',
                'catatan_petugas' => 'Sampah berhasil dibersihkan pada 18 Juni 2026. Warga diimbau untuk tidak membuang sampah di lahan kosong. Koordinasi dengan RT/RW setempat selesai dilakukan.',
            ],
            [
                'nama_pelapor'    => 'Lestari Handayani',
                'kontak_pelapor'  => '082213456789',
                'kategori_id'     => $kategoriOrganik,
                'lokasi'          => 'Jl. Cempaka Putih Tengah VII, Jakarta Pusat',
                'latitude'        => -6.1744,
                'longitude'       => 106.8693,
                'deskripsi'       => 'Sampah organik membusuk di trotoar karena tidak ada tempat sampah di area ini. Permintaan penambahan fasilitas tempat sampah.',
                'foto'            => null,
                'status'          => 'selesai',
                'catatan_petugas' => 'Area sudah dibersihkan dan dipasang 2 unit tempat sampah organik/anorganik baru. Petugas kebersihan rutin dijadwalkan setiap hari.',
            ],

            // ── STATUS: DITOLAK ───────────────────────────────────────────────
            [
                'nama_pelapor'    => 'Rizky Firmansyah',
                'kontak_pelapor'  => '085523456791',
                'kategori_id'     => $kategoriKonstruksi,
                'lokasi'          => 'Jl. Sudirman No. 100, Makassar',
                'latitude'        => -5.1477,
                'longitude'       => 119.4327,
                'deskripsi'       => 'Puing bangunan bekas renovasi kantor dibuang di pinggir jalan.',
                'foto'            => null,
                'status'          => 'ditolak',
                'catatan_petugas' => 'Laporan ditolak karena lokasi yang disebutkan berada di luar wilayah yurisdiksi Dinas ini. Harap laporkan ke Dinas Lingkungan Hidup Kota Makassar secara langsung.',
            ],
        ];

        foreach ($laporanDummy as $data) {
            // Gunakan create() agar boot() model ter-trigger untuk generate kode_laporan
            LaporanSampah::create($data);
        }

        $this->command->info('✅  ' . count($laporanDummy) . ' Laporan Sampah dummy berhasil di-seed.');
        $this->command->line('   Kode: SPH-001 s/d SPH-00' . count($laporanDummy));
    }
}
