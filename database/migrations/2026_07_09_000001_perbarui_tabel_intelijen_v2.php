<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Pembaruan Tabel Intelijen.
 *
 * Menambahkan kolom-kolom yang diperlukan untuk mendukung:
 * - Data cuaca 7 hari (suhu min/max & insight SCM)
 * - Berita dengan URL asli dan analisis dampak SCM
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom ke tabel catatan_cuaca
        Schema::table('catatan_cuaca', function (Blueprint $table) {
            $table->decimal('suhu_min', 5, 2)->nullable()->after('suhu')->comment('Suhu minimum hari itu (°C)');
            $table->decimal('suhu_max', 5, 2)->nullable()->after('suhu_min')->comment('Suhu maksimum hari itu (°C)');
            $table->text('insight_scm')->nullable()->after('kondisi_cuaca')->comment('Analisis dampak cuaca ke rantai pasok');
        });

        // Tambah kolom ke tabel nilai_tukar
        Schema::table('nilai_tukar', function (Blueprint $table) {
            $table->text('insight_scm')->nullable()->after('sumber_api')->comment('Analisis dampak nilai tukar ke SCM');
        });

        // Tambah kolom ke tabel artikel_berita
        Schema::table('artikel_berita', function (Blueprint $table) {
            $table->string('url_asli', 2048)->nullable()->after('sumber')->comment('URL artikel berita asli');
            $table->text('dampak_scm')->nullable()->after('keparahan')->comment('Analisis dampak berita ke rantai pasok');
        });
    }

    public function down(): void
    {
        Schema::table('catatan_cuaca', function (Blueprint $table) {
            $table->dropColumn(['suhu_min', 'suhu_max', 'insight_scm']);
        });

        Schema::table('nilai_tukar', function (Blueprint $table) {
            $table->dropColumn(['insight_scm']);
        });

        Schema::table('artikel_berita', function (Blueprint $table) {
            $table->dropColumn(['url_asli', 'dampak_scm']);
        });
    }
};
