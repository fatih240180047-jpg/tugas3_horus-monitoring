<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel intelijen eksternal.
 *
 * Membuat empat tabel untuk menyimpan data dari provider eksternal:
 * - catatan_cuaca   : Data cuaca historis dari OpenWeather
 * - indikator_ekonomi : Indikator makroekonomi dari World Bank
 * - nilai_tukar     : Nilai tukar mata uang historis dari ExchangeRate API
 * - artikel_berita  : Berita internasional relevan dari NewsAPI
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // ---------------------------------------------------
        // Catatan Cuaca (Weather Records)
        // ---------------------------------------------------
        Schema::create('catatan_cuaca', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id')->comment('FK → negara');
            $table->date('tanggal_observasi')->comment('Tanggal pengamatan cuaca');
            $table->decimal('suhu', 5, 2)->nullable()->comment('Suhu dalam Celsius');
            $table->decimal('kelembaban', 5, 2)->nullable()->comment('Kelembaban dalam persen (%)');
            $table->decimal('curah_hujan', 8, 2)->nullable()->comment('Curah hujan dalam milimeter');
            $table->decimal('kecepatan_angin', 6, 2)->nullable()->comment('Kecepatan angin dalam km/jam');
            $table->string('kondisi_cuaca', 100)->nullable()->comment('Deskripsi kondisi cuaca');
            $table->string('sumber_api', 100)->nullable()->comment('Nama provider data');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('restrict');
            $table->index('negara_id');
            $table->index('tanggal_observasi');
            $table->index('kondisi_cuaca');
            $table->unique(['negara_id', 'tanggal_observasi'], 'unik_cuaca_negara_tanggal');
        });

        // ---------------------------------------------------
        // Indikator Ekonomi (Economic Indicators)
        // ---------------------------------------------------
        Schema::create('indikator_ekonomi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id')->comment('FK → negara');
            $table->date('tanggal_indikator')->comment('Tanggal referensi indikator');
            $table->decimal('pdb', 18, 2)->nullable()->comment('Produk Domestik Bruto (USD)');
            $table->decimal('tingkat_inflasi', 8, 2)->nullable()->comment('Inflasi dalam persen (%)');
            $table->decimal('tingkat_pengangguran', 8, 2)->nullable()->comment('Pengangguran dalam persen (%)');
            $table->decimal('tingkat_bunga', 8, 2)->nullable()->comment('Suku bunga dalam persen (%)');
            $table->decimal('neraca_perdagangan', 18, 2)->nullable()->comment('Neraca perdagangan (USD)');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('restrict');
            $table->index('negara_id');
            $table->index('tanggal_indikator');
            $table->index(['negara_id', 'tanggal_indikator']);
        });

        // ---------------------------------------------------
        // Nilai Tukar (Exchange Rates)
        // ---------------------------------------------------
        Schema::create('nilai_tukar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id')->comment('FK → negara');
            $table->char('kode_mata_uang', 3)->comment('Kode ISO 4217, contoh: IDR, USD');
            $table->decimal('nilai_tukar', 18, 6)->comment('Nilai tukar terhadap USD');
            $table->date('tanggal_berlaku')->comment('Tanggal efektif nilai tukar');
            $table->string('sumber_api', 100)->nullable()->comment('Provider data');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('restrict');
            $table->index('negara_id');
            $table->index('kode_mata_uang');
            $table->index('tanggal_berlaku');
            $table->unique(['negara_id', 'kode_mata_uang', 'tanggal_berlaku'], 'unik_nilai_tukar');
        });

        // ---------------------------------------------------
        // Artikel Berita (News Articles)
        // ---------------------------------------------------
        Schema::create('artikel_berita', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id')->comment('FK → negara');
            $table->string('judul', 500)->comment('Judul berita');
            $table->text('ringkasan')->nullable()->comment('Ringkasan isi berita');
            $table->string('kategori', 100)->nullable()->comment('Kategori berita');
            $table->enum('sentimen', ['positif', 'netral', 'negatif'])->comment('Klasifikasi sentimen');
            $table->enum('keparahan', ['rendah', 'sedang', 'tinggi', 'kritis'])->comment('Tingkat keparahan risiko');
            $table->string('sumber', 255)->nullable()->comment('Nama sumber berita');
            $table->timestamp('diterbitkan_pada')->comment('Waktu publikasi');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('restrict');
            $table->index('negara_id');
            $table->index('sentimen');
            $table->index('keparahan');
            $table->index('diterbitkan_pada');
            $table->index(['negara_id', 'diterbitkan_pada']);
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel_berita');
        Schema::dropIfExists('nilai_tukar');
        Schema::dropIfExists('indikator_ekonomi');
        Schema::dropIfExists('catatan_cuaca');
    }
};
