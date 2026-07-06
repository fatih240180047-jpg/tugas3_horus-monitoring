<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel pengguna (users).
 *
 * Membuat tabel inti untuk autentikasi dan sesi pengguna platform.
 * Penamaan tabel menggunakan Bahasa Indonesia sesuai standar proyek.
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('avatar', 255)->nullable()->comment('Path foto profil');
            $table->boolean('status')->default(true)->comment('Status akun aktif/nonaktif');
            $table->timestamp('last_login_at')->nullable()->comment('Waktu login terakhir');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('last_login_at');
        });

        Schema::create('token_reset_kata_sandi', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sesi', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('token_reset_kata_sandi');
        Schema::dropIfExists('sesi');
    }
};
