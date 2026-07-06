<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel analitik.
 *
 * Membuat tabel untuk menyimpan hasil olahan mesin analitik:
 * - penilaian_risiko    : Hasil perhitungan skor risiko negara
 * - rekomendasi_risiko  : Rekomendasi bisnis otomatis
 * - lapisan_sig         : Metadata visualisasi peta GIS
 * - cache_dasbor        : Data KPI ter-cache untuk performa dasbor
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // ---------------------------------------------------
        // Penilaian Risiko (Risk Assessments)
        // ---------------------------------------------------
        Schema::create('penilaian_risiko', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id')->comment('FK → negara');
            $table->decimal('skor_cuaca', 5, 2)->nullable()->comment('Komponen skor cuaca (0-100)');
            $table->decimal('skor_ekonomi', 5, 2)->nullable()->comment('Komponen skor ekonomi (0-100)');
            $table->decimal('skor_nilai_tukar', 5, 2)->nullable()->comment('Komponen skor nilai tukar (0-100)');
            $table->decimal('skor_berita', 5, 2)->nullable()->comment('Komponen skor berita (0-100)');
            $table->decimal('skor_logistik', 5, 2)->nullable()->comment('Komponen skor logistik (0-100)');
            $table->decimal('skor_politik', 5, 2)->nullable()->comment('Komponen skor politik (0-100)');
            $table->decimal('skor_total', 5, 2)->comment('Skor risiko akhir tertimbang (0-100)');
            $table->enum('level_risiko', ['Rendah', 'Sedang', 'Tinggi', 'Kritis'])->comment('Klasifikasi risiko');
            $table->json('penjelasan')->nullable()->comment('Alasan-alasan penyusun skor risiko');
            $table->timestamp('dihitung_pada')->useCurrent()->comment('Waktu kalkulasi');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('restrict');
            $table->index('negara_id');
            $table->index('skor_total');
            $table->index('level_risiko');
            $table->index('dihitung_pada');
            $table->index(['negara_id', 'dihitung_pada']);
        });

        // ---------------------------------------------------
        // Rekomendasi Risiko (Risk Recommendations)
        // ---------------------------------------------------
        Schema::create('rekomendasi_risiko', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penilaian_risiko_id')->comment('FK → penilaian_risiko');
            $table->text('rekomendasi')->comment('Tindakan yang disarankan');
            $table->enum('prioritas', ['Rendah', 'Sedang', 'Tinggi', 'Kritis'])->comment('Tingkat prioritas');
            $table->enum('status', ['Tertunda', 'Diterima', 'Ditolak', 'Selesai'])->default('Tertunda');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('penilaian_risiko_id')
                ->references('id')
                ->on('penilaian_risiko')
                ->onDelete('cascade');

            $table->index('penilaian_risiko_id');
            $table->index('prioritas');
            $table->index('status');
        });

        // ---------------------------------------------------
        // Lapisan SIG (GIS Layers)
        // ---------------------------------------------------
        Schema::create('lapisan_sig', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id')->comment('FK → negara');
            $table->decimal('lintang', 10, 7)->comment('Koordinat lintang marker');
            $table->decimal('bujur', 10, 7)->comment('Koordinat bujur marker');
            $table->string('jenis_lapisan', 100)->comment('Jenis lapisan SIG');
            $table->string('warna', 30)->nullable()->comment('Warna visualisasi (hex)');
            $table->integer('ukuran_penanda')->nullable()->comment('Ukuran marker');
            $table->json('data_popup')->nullable()->comment('Metadata popup informasi');
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('cascade');
            $table->index('negara_id');
            $table->index('jenis_lapisan');
        });

        // ---------------------------------------------------
        // Cache Dasbor (Dashboard Cache)
        // ---------------------------------------------------
        Schema::create('cache_dasbor', function (Blueprint $table) {
            $table->id();
            $table->string('widget', 100)->comment('Nama widget dasbor');
            $table->string('kunci_cache', 255)->unique()->comment('Identifikasi unik cache');
            $table->json('muatan')->comment('Data ter-cache dalam JSON');
            $table->timestamp('kedaluwarsa_pada')->comment('Waktu kedaluwarsa cache');
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index('widget');
            $table->index('kedaluwarsa_pada');
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_dasbor');
        Schema::dropIfExists('lapisan_sig');
        Schema::dropIfExists('rekomendasi_risiko');
        Schema::dropIfExists('penilaian_risiko');
    }
};
