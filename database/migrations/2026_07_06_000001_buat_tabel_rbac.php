<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel RBAC (Role-Based Access Control).
 *
 * Membuat tabel peran, izin, dan hubungan pivot peran-pengguna.
 * Implementasi sistem otorisasi berbasis peran sesuai DDS.
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Tabel Peran (Roles)
        Schema::create('peran', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nama peran');
            $table->string('slug', 100)->unique()->comment('Identifikasi internal peran');
            $table->text('description')->nullable()->comment('Deskripsi peran');
            $table->timestamps();

            $table->index('name');
        });

        // Tabel Izin (Permissions)
        Schema::create('izin', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->comment('Nama izin');
            $table->string('slug', 150)->unique()->comment('Kunci izin, contoh: users.view');
            $table->string('modul', 100)->comment('Modul terkait');
            $table->timestamps();

            $table->index('modul');
        });

        // Tabel Pivot Peran-Pengguna (Role-User)
        Schema::create('peran_pengguna', function (Blueprint $table) {
            $table->unsignedBigInteger('pengguna_id');
            $table->unsignedBigInteger('peran_id');
            $table->timestamp('ditetapkan_pada')->useCurrent()->comment('Waktu penugasan peran');

            $table->primary(['pengguna_id', 'peran_id']);

            $table->foreign('pengguna_id')
                ->references('id')
                ->on('pengguna')
                ->onDelete('cascade');

            $table->foreign('peran_id')
                ->references('id')
                ->on('peran')
                ->onDelete('cascade');
        });

        // Tabel Pivot Peran-Izin (Role-Permission)
        Schema::create('peran_izin', function (Blueprint $table) {
            $table->unsignedBigInteger('peran_id');
            $table->unsignedBigInteger('izin_id');

            $table->primary(['peran_id', 'izin_id']);

            $table->foreign('peran_id')
                ->references('id')
                ->on('peran')
                ->onDelete('cascade');

            $table->foreign('izin_id')
                ->references('id')
                ->on('izin')
                ->onDelete('cascade');
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('peran_izin');
        Schema::dropIfExists('peran_pengguna');
        Schema::dropIfExists('izin');
        Schema::dropIfExists('peran');
    }
};
