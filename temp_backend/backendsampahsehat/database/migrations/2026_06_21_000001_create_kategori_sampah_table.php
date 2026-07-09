<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel kategori_sampah.
     */
    public function up(): void
    {
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100);
            $table->text('deskripsi')->nullable();
            $table->enum('level_risiko', ['rendah', 'sedang', 'tinggi'])->default('rendah');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_sampah');
    }
};
