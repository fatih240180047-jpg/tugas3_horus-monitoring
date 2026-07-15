<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel negara.
 *
 * Menyimpan profil master setiap negara yang dipantau.
 * Tabel ini menjadi referensi sentral untuk seluruh modul intelijen.
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('negara', function (Blueprint $table) {
            $table->id();
            $table->char('kode_iso', 3)->unique()->comment('Kode ISO 3166-1 Alpha-3, contoh: IDN');
            $table->string('nama', 150)->comment('Nama resmi negara');
            $table->string('ibu_kota', 150)->nullable()->comment('Nama ibu kota');
            $table->string('kawasan', 100)->nullable()->comment('Kawasan geografis, contoh: Asia');
            $table->string('sub_kawasan', 100)->nullable()->comment('Sub-kawasan, contoh: South-Eastern Asia');
            $table->decimal('lintang', 10, 7)->nullable()->comment('Koordinat lintang (latitude)');
            $table->decimal('bujur', 10, 7)->nullable()->comment('Koordinat bujur (longitude)');
            $table->unsignedBigInteger('populasi')->nullable()->comment('Jumlah penduduk');
            $table->char('mata_uang', 3)->nullable()->comment('Kode mata uang ISO 4217, contoh: IDR');
            $table->string('bendera', 20)->nullable()->comment('Emoji bendera negara');
            $table->boolean('status_pemantauan')->default(true)->comment('Apakah negara aktif dipantau');
            $table->timestamps();

            $table->index('nama');
            $table->index('kawasan');
            $table->index('status_pemantauan');
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('negara');
    }
};
