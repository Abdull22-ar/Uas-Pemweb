<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel laporan_sampah.
     */
    public function up(): void
    {
        Schema::create('laporan_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laporan', 20)->unique();
            $table->string('nama_pelapor', 100);
            $table->string('kontak_pelapor', 20);
            $table->foreignId('kategori_id')
                  ->constrained('kategori_sampah')
                  ->restrictOnDelete();
            $table->string('lokasi');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_sampah');
    }
};
