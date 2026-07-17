<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder Utama
 *
 * Menjalankan seluruh seeder dalam urutan yang benar
 * untuk menghindari pelanggaran foreign key constraint.
 *
 * Urutan:
 * 1. PeranDanIzinSeeder — harus sebelum Negara (tidak ada FK, tapi konvensi)
 * 2. NegaraSeeder       — harus sebelum data intelijen
 * 3. PengaturanSeeder   — konfigurasi bobot risiko dan API
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder aplikasi.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║   Supply Chain Intelligence Platform — Seeder   ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');
        $this->command->info('');

        $this->call([
            PeranDanIzinSeeder::class,
            NegaraSeeder::class,
            PengaturanSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Semua seeder berhasil dijalankan!');
        $this->command->info('');
        $this->command->info('Akun uji coba tersedia:');
        $this->command->table(
            ['Peran', 'Email', 'Password'],
            [
                ['Super Administrator', 'superadmin@horus.local', 'SuperAdmin123!'],
                ['Administrator',       'admin@horus.local',      'Admin123!'],
                ['Eksekutif',           'eksekutif@horus.local',  'Eksekutif123!'],
                ['Analis Risiko',       'analis@horus.local',     'Analis123!'],
                ['Manajer Pengadaan',   'pengadaan@horus.local',  'Pengadaan123!'],
            ]
        );
    }
}

