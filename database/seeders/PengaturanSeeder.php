<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

/**
 * Seeder Pengaturan
 *
 * Mengisi tabel pengaturan dengan konfigurasi default platform,
 * termasuk bobot risiko, preferensi API, dan pengaturan umum.
 */
class PengaturanSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $daftarPengaturan = [
            // --------------------------------------------------
            // Pengaturan Umum
            // --------------------------------------------------
            [
                'kategori'    => 'aplikasi',
                'kunci'       => 'aplikasi.nama',
                'nilai'       => 'Supply Chain Intelligence Platform',
                'tipe_data'   => 'string',
                'deskripsi'   => 'Nama resmi platform',
            ],
            [
                'kategori'    => 'aplikasi',
                'kunci'       => 'aplikasi.zona_waktu',
                'nilai'       => 'Asia/Jakarta',
                'tipe_data'   => 'string',
                'deskripsi'   => 'Zona waktu default tampilan platform',
            ],
            [
                'kategori'    => 'dasbor',
                'kunci'       => 'dasbor.interval_refresh',
                'nilai'       => '300',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Interval refresh otomatis dasbor dalam detik (0 = nonaktif)',
            ],

            // --------------------------------------------------
            // Bobot Risiko (Dapat dikonfigurasi oleh Administrator)
            // --------------------------------------------------
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.bobot_cuaca',
                'nilai'       => '20',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Bobot komponen cuaca dalam perhitungan skor risiko (%)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.bobot_ekonomi',
                'nilai'       => '25',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Bobot komponen ekonomi dalam perhitungan skor risiko (%)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.bobot_nilai_tukar',
                'nilai'       => '15',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Bobot komponen nilai tukar dalam perhitungan skor risiko (%)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.bobot_berita',
                'nilai'       => '20',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Bobot komponen berita dalam perhitungan skor risiko (%)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.bobot_logistik',
                'nilai'       => '10',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Bobot komponen logistik dalam perhitungan skor risiko (%)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.bobot_politik',
                'nilai'       => '10',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Bobot komponen politik dalam perhitungan skor risiko (%)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.ambang_rendah',
                'nilai'       => '25',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Batas atas skor Risiko Rendah (0–nilai ini)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.ambang_sedang',
                'nilai'       => '50',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Batas atas skor Risiko Sedang (ambang_rendah+1 hingga nilai ini)',
            ],
            [
                'kategori'    => 'risiko',
                'kunci'       => 'risiko.ambang_tinggi',
                'nilai'       => '75',
                'tipe_data'   => 'integer',
                'deskripsi'   => 'Batas atas skor Risiko Tinggi (ambang_sedang+1 hingga nilai ini; di atas ini = Kritis)',
            ],

            // --------------------------------------------------
            // Konfigurasi API
            // --------------------------------------------------
            [
                'kategori'    => 'api',
                'kunci'       => 'api.cuaca.aktif',
                'nilai'       => 'true',
                'tipe_data'   => 'boolean',
                'deskripsi'   => 'Aktifkan sinkronisasi data cuaca dari OpenWeather API',
            ],
            [
                'kategori'    => 'api',
                'kunci'       => 'api.ekonomi.aktif',
                'nilai'       => 'true',
                'tipe_data'   => 'boolean',
                'deskripsi'   => 'Aktifkan sinkronisasi data ekonomi dari World Bank API',
            ],
            [
                'kategori'    => 'api',
                'kunci'       => 'api.nilai_tukar.aktif',
                'nilai'       => 'true',
                'tipe_data'   => 'boolean',
                'deskripsi'   => 'Aktifkan sinkronisasi nilai tukar dari ExchangeRate API',
            ],
            [
                'kategori'    => 'api',
                'kunci'       => 'api.berita.aktif',
                'nilai'       => 'true',
                'tipe_data'   => 'boolean',
                'deskripsi'   => 'Aktifkan sinkronisasi berita dari NewsAPI',
            ],
            [
                'kategori'    => 'api',
                'kunci'       => 'api.mode_simulasi',
                'nilai'       => 'true',
                'tipe_data'   => 'boolean',
                'deskripsi'   => 'Gunakan data mock/simulasi jika kunci API tidak dikonfigurasi',
            ],

            // --------------------------------------------------
            // Notifikasi
            // --------------------------------------------------
            [
                'kategori'    => 'notifikasi',
                'kunci'       => 'notifikasi.risiko_kritis',
                'nilai'       => 'true',
                'tipe_data'   => 'boolean',
                'deskripsi'   => 'Kirim notifikasi saat negara mencapai level risiko Kritis',
            ],
        ];

        $ditambahkan = 0;

        foreach ($daftarPengaturan as $data) {
            Pengaturan::firstOrCreate(
                ['kunci' => $data['kunci']],
                $data
            );
            $ditambahkan++;
        }

        $this->command->info("✓ Pengaturan default berhasil dimuat: {$ditambahkan} konfigurasi.");
    }
}
