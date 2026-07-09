<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->unique('nama_kategori');
            $table->index('level_risiko');
            $table->index('status_aktif');
        });

        Schema::table('laporan_sampah', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->dropIndex(['nama_kategori']);
            $table->dropIndex(['level_risiko']);
            $table->dropIndex(['status_aktif']);
        });

        Schema::table('laporan_sampah', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
