<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel sistem pendukung.
 *
 * Membuat tabel operasional platform:
 * - log_aktivitas      : Audit log tidak dapat diubah dari aksi pengguna
 * - pengaturan         : Konfigurasi aplikasi yang dapat dikonfigurasi
 * - laporan            : Metadata laporan yang dihasilkan
 * - ekspor_laporan     : Riwayat ekspor laporan
 * - log_sinkronisasi_api : Riwayat sinkronisasi setiap API eksternal
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // ---------------------------------------------------
        // Log Aktivitas (Activity Logs)
        // ---------------------------------------------------
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengguna_id')->nullable()->comment('Pengguna yang melakukan aksi');
            $table->string('modul', 100)->comment('Nama modul terkait');
            $table->string('aksi', 150)->comment('Aksi yang dieksekusi');
            $table->string('entitas', 100)->nullable()->comment('Nama entitas terkait');
            $table->unsignedBigInteger('entitas_id')->nullable()->comment('ID entitas terkait');
            $table->json('nilai_lama')->nullable()->comment('Nilai sebelum perubahan');
            $table->json('nilai_baru')->nullable()->comment('Nilai sesudah perubahan');
            $table->string('alamat_ip', 45)->nullable()->comment('Alamat IP klien');
            $table->text('user_agent')->nullable()->comment('Informasi browser');
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('pengguna_id')->references('id')->on('pengguna')->onDelete('set null');
            $table->index('pengguna_id');
            $table->index('modul');
            $table->index('aksi');
            $table->index('dibuat_pada');
        });

        // ---------------------------------------------------
        // Pengaturan Aplikasi (Settings)
        // ---------------------------------------------------
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100)->comment('Kelompok pengaturan, contoh: risk, api, application');
            $table->string('kunci', 150)->unique()->comment('Kunci konfigurasi, contoh: risk.bobot_cuaca');
            $table->text('nilai')->nullable()->comment('Nilai konfigurasi');
            $table->string('tipe_data', 50)->comment('Tipe data: string, integer, boolean, json');
            $table->text('deskripsi')->nullable()->comment('Penjelasan pengaturan');
            $table->unsignedBigInteger('diperbarui_oleh')->nullable()->comment('ID pengguna yang memperbarui');
            $table->timestamps();

            $table->foreign('diperbarui_oleh')->references('id')->on('pengguna')->onDelete('set null');
            $table->index('kategori');
        });

        // ---------------------------------------------------
        // Laporan (Reports)
        // ---------------------------------------------------
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255)->comment('Judul laporan');
            $table->string('jenis_laporan', 100)->comment('Kategori laporan');
            $table->unsignedBigInteger('dibuat_oleh')->nullable()->comment('ID pengguna pembuat');
            $table->string('path_file', 500)->nullable()->comment('Lokasi file laporan');
            $table->timestamp('dihasilkan_pada')->useCurrent();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('dibuat_oleh')->references('id')->on('pengguna')->onDelete('set null');
            $table->index('dibuat_oleh');
            $table->index('jenis_laporan');
        });

        // ---------------------------------------------------
        // Ekspor Laporan (Report Exports)
        // ---------------------------------------------------
        Schema::create('ekspor_laporan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_id')->comment('FK → laporan');
            $table->enum('format_ekspor', ['PDF', 'Excel', 'CSV'])->comment('Format file ekspor');
            $table->unsignedBigInteger('diekspor_oleh')->nullable()->comment('ID pengguna pengekspor');
            $table->timestamp('diekspor_pada')->useCurrent();

            $table->foreign('laporan_id')->references('id')->on('laporan')->onDelete('cascade');
            $table->foreign('diekspor_oleh')->references('id')->on('pengguna')->onDelete('set null');
            $table->index('laporan_id');
        });

        // ---------------------------------------------------
        // Log Sinkronisasi API (API Sync Logs)
        // ---------------------------------------------------
        Schema::create('log_sinkronisasi_api', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 100)->comment('Nama provider API');
            $table->string('endpoint', 255)->comment('Endpoint yang diminta');
            $table->enum('status', ['Berhasil', 'Gagal'])->comment('Status sinkronisasi');
            $table->integer('jumlah_rekaman')->default(0)->comment('Jumlah rekaman yang diproses');
            $table->integer('waktu_eksekusi_ms')->nullable()->comment('Durasi eksekusi dalam milidetik');
            $table->text('pesan_error')->nullable()->comment('Deskripsi kegagalan jika ada');
            $table->timestamp('dieksekusi_pada')->useCurrent();

            $table->index('provider');
            $table->index('status');
            $table->index('dieksekusi_pada');
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_sinkronisasi_api');
        Schema::dropIfExists('ekspor_laporan');
        Schema::dropIfExists('laporan');
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('log_aktivitas');
    }
};
