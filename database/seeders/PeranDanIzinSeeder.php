<?php

namespace Database\Seeders;

use App\Models\Izin;
use App\Models\Peran;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder Peran Dan Izin
 *
 * Mengisi tabel peran, izin, dan hubungan peran-izin dengan data awal.
 * Juga membuat pengguna uji coba untuk setiap peran.
 */
class PeranDanIzinSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // --------------------------------------------------
        // DEFINISI IZIN PER MODUL
        // --------------------------------------------------
        $daftarIzin = [
            // Modul Pengguna
            ['name' => 'Lihat Pengguna',   'slug' => 'pengguna.lihat',  'modul' => 'pengguna'],
            ['name' => 'Buat Pengguna',    'slug' => 'pengguna.buat',   'modul' => 'pengguna'],
            ['name' => 'Ubah Pengguna',    'slug' => 'pengguna.ubah',   'modul' => 'pengguna'],
            ['name' => 'Hapus Pengguna',   'slug' => 'pengguna.hapus',  'modul' => 'pengguna'],

            // Modul Negara
            ['name' => 'Lihat Negara',     'slug' => 'negara.lihat',    'modul' => 'negara'],
            ['name' => 'Kelola Negara',    'slug' => 'negara.kelola',   'modul' => 'negara'],

            // Modul Intelijen
            ['name' => 'Lihat Cuaca',      'slug' => 'cuaca.lihat',     'modul' => 'intelijen'],
            ['name' => 'Lihat Ekonomi',    'slug' => 'ekonomi.lihat',   'modul' => 'intelijen'],
            ['name' => 'Lihat Nilai Tukar','slug' => 'nilai-tukar.lihat','modul' => 'intelijen'],
            ['name' => 'Lihat Berita',     'slug' => 'berita.lihat',    'modul' => 'intelijen'],

            // Modul Risiko
            ['name' => 'Hitung Risiko',    'slug' => 'risiko.hitung',   'modul' => 'risiko'],
            ['name' => 'Lihat Risiko',     'slug' => 'risiko.lihat',    'modul' => 'risiko'],
            ['name' => 'Kelola Bobot Risiko','slug' => 'risiko.bobot',  'modul' => 'risiko'],

            // Modul SIG
            ['name' => 'Lihat Peta SIG',   'slug' => 'sig.lihat',       'modul' => 'sig'],

            // Modul Laporan
            ['name' => 'Lihat Laporan',    'slug' => 'laporan.lihat',   'modul' => 'laporan'],
            ['name' => 'Ekspor Laporan',   'slug' => 'laporan.ekspor',  'modul' => 'laporan'],

            // Modul Administrasi
            ['name' => 'Kelola Pengaturan','slug' => 'pengaturan.kelola','modul' => 'administrasi'],
            ['name' => 'Lihat Log Aktivitas','slug' => 'log.lihat',     'modul' => 'administrasi'],
            ['name' => 'Sinkronisasi API', 'slug' => 'api.sinkronisasi','modul' => 'administrasi'],

            // Dasbor
            ['name' => 'Akses Dasbor',     'slug' => 'dasbor.akses',   'modul' => 'dasbor'],
        ];

        foreach ($daftarIzin as $izinData) {
            Izin::firstOrCreate(['slug' => $izinData['slug']], $izinData);
        }

        // --------------------------------------------------
        // DEFINISI PERAN DAN IZINNYA
        // --------------------------------------------------
        $daftarPeran = [
            [
                'name'        => 'Super Administrator',
                'slug'        => 'super-administrator',
                'description' => 'Akses penuh ke semua fitur dan pengaturan sistem.',
                'izin'        => Izin::all()->pluck('id')->toArray(), // Semua izin
            ],
            [
                'name'        => 'Administrator',
                'slug'        => 'administrator',
                'description' => 'Manajemen pengguna, negara, pengaturan, dan monitoring API.',
                'izin'        => Izin::whereIn('slug', [
                    'pengguna.lihat', 'pengguna.buat', 'pengguna.ubah',
                    'negara.lihat', 'negara.kelola',
                    'cuaca.lihat', 'ekonomi.lihat', 'nilai-tukar.lihat', 'berita.lihat',
                    'risiko.hitung', 'risiko.lihat', 'risiko.bobot',
                    'sig.lihat', 'laporan.lihat', 'laporan.ekspor',
                    'pengaturan.kelola', 'log.lihat', 'api.sinkronisasi', 'dasbor.akses',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Eksekutif',
                'slug'        => 'eksekutif',
                'description' => 'Akses baca ke dasbor, ringkasan risiko, dan laporan eksekutif.',
                'izin'        => Izin::whereIn('slug', [
                    'negara.lihat', 'cuaca.lihat', 'ekonomi.lihat', 'nilai-tukar.lihat', 'berita.lihat',
                    'risiko.lihat', 'sig.lihat', 'laporan.lihat', 'laporan.ekspor', 'dasbor.akses',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Analis Risiko',
                'slug'        => 'analis-risiko',
                'description' => 'Analisis mendalam skor risiko, berita, dan tren data intelijen.',
                'izin'        => Izin::whereIn('slug', [
                    'negara.lihat', 'cuaca.lihat', 'ekonomi.lihat', 'nilai-tukar.lihat', 'berita.lihat',
                    'risiko.hitung', 'risiko.lihat', 'sig.lihat', 'laporan.lihat', 'laporan.ekspor', 'dasbor.akses',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Manajer Pengadaan',
                'slug'        => 'manajer-pengadaan',
                'description' => 'Pemantauan cuaca, nilai tukar, dan kondisi rantai pasok per negara.',
                'izin'        => Izin::whereIn('slug', [
                    'negara.lihat', 'cuaca.lihat', 'ekonomi.lihat', 'nilai-tukar.lihat', 'berita.lihat',
                    'risiko.lihat', 'sig.lihat', 'laporan.lihat', 'dasbor.akses',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($daftarPeran as $peranData) {
            $izinIds = $peranData['izin'];
            unset($peranData['izin']);

            $peran = Peran::firstOrCreate(['slug' => $peranData['slug']], $peranData);
            $peran->izin()->sync($izinIds);
        }

        // --------------------------------------------------
        // PENGGUNA UJI COBA PER PERAN
        // --------------------------------------------------
        $waktuSekarang = Carbon::now();

        $daftarPengguna = [
            [
                'name'     => 'Super Administrator',
                'email'    => 'superadmin@horus.local',
                'password' => Hash::make('SuperAdmin123!'),
                'status'   => true,
                'peran'    => 'super-administrator',
            ],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@horus.local',
                'password' => Hash::make('Admin123!'),
                'status'   => true,
                'peran'    => 'administrator',
            ],
            [
                'name'     => 'Budi Eksekutif',
                'email'    => 'eksekutif@horus.local',
                'password' => Hash::make('Eksekutif123!'),
                'status'   => true,
                'peran'    => 'eksekutif',
            ],
            [
                'name'     => 'Rina Analis Risiko',
                'email'    => 'analis@horus.local',
                'password' => Hash::make('Analis123!'),
                'status'   => true,
                'peran'    => 'analis-risiko',
            ],
            [
                'name'     => 'Doni Manajer Pengadaan',
                'email'    => 'pengadaan@horus.local',
                'password' => Hash::make('Pengadaan123!'),
                'status'   => true,
                'peran'    => 'manajer-pengadaan',
            ],
        ];

        foreach ($daftarPengguna as $penggunaData) {
            $slugPeran = $penggunaData['peran'];
            unset($penggunaData['peran']);

            $pengguna = Pengguna::firstOrCreate(
                ['email' => $penggunaData['email']],
                array_merge($penggunaData, ['email_verified_at' => $waktuSekarang])
            );

            $peran = Peran::where('slug', $slugPeran)->first();
            if ($peran) {
                $pengguna->peran()->syncWithoutDetaching([
                    $peran->id => ['ditetapkan_pada' => $waktuSekarang],
                ]);
            }
        }

        $this->command->info('✓ Peran, izin, dan pengguna uji coba berhasil dibuat.');
    }
}

