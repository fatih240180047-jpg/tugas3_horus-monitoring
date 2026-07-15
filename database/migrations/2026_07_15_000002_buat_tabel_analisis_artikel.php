<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel analisis_artikel.
 *
 * Menyimpan anotasi intelijen dari Analis Risiko & Manajer Pengadaan
 * terhadap berita yang telah diklasifikasikan oleh sistem AI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artikel_berita_id')->constrained('artikel_berita')->onDelete('cascade');
            $table->foreignId('pengguna_id')->constrained('pengguna')->onDelete('cascade');

            // Anotasi Analis
            $table->text('komentar_analis')->comment('Komentar kualitatif analis terhadap artikel ini');
            $table->text('rekomendasi_tindakan')->nullable()->comment('Rekomendasi langkah mitigasi SCM');
            $table->enum('tingkat_kepercayaan', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->enum('dampak_scm', ['tidak_berdampak', 'minor', 'signifikan', 'kritis'])->default('minor');

            // Status Review & Persetujuan
            $table->enum('status', ['draft', 'menunggu_review', 'disetujui', 'ditolak'])->default('draft');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_reviewer')->nullable()->comment('Catatan dari eksekutif atau admin saat review');

            $table->timestamps();

            $table->unique(['artikel_berita_id', 'pengguna_id'], 'satu_analisis_per_analis');
            $table->index('status');
            $table->index('pengguna_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_artikel');
    }
};
