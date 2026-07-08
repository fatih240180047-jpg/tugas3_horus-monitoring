<?php

namespace Database\Seeders;

use App\Models\Negara;
use Illuminate\Database\Seeder;

/**
 * Seeder Negara
 *
 * Mengisi tabel negara dengan 30 negara strategis yang relevan
 * untuk pemantauan rantai pasok global.
 */
class NegaraSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $daftarNegara = [
            // ===== ASIA TENGGARA =====
            [
                'kode_iso'        => 'IDN',
                'nama'            => 'Indonesia',
                'ibu_kota'        => 'Jakarta',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'South-Eastern Asia',
                'lintang'         => -0.7893,
                'bujur'           => 113.9213,
                'populasi'        => 277534122,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'SGP',
                'nama'            => 'Singapura',
                'ibu_kota'        => 'Singapura',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'South-Eastern Asia',
                'lintang'         => 1.3521,
                'bujur'           => 103.8198,
                'populasi'        => 5896686,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'MYS',
                'nama'            => 'Malaysia',
                'ibu_kota'        => 'Kuala Lumpur',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'South-Eastern Asia',
                'lintang'         => 4.2105,
                'bujur'           => 101.9758,
                'populasi'        => 32365999,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'THA',
                'nama'            => 'Thailand',
                'ibu_kota'        => 'Bangkok',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'South-Eastern Asia',
                'lintang'         => 15.8700,
                'bujur'           => 100.9925,
                'populasi'        => 69626167,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'VNM',
                'nama'            => 'Vietnam',
                'ibu_kota'        => 'Hanoi',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'South-Eastern Asia',
                'lintang'         => 14.0583,
                'bujur'           => 108.2772,
                'populasi'        => 97338583,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'PHL',
                'nama'            => 'Filipina',
                'ibu_kota'        => 'Manila',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'South-Eastern Asia',
                'lintang'         => 12.8797,
                'bujur'           => 121.7740,
                'populasi'        => 113880328,
                'status_pemantauan' => true,
            ],
            // ===== ASIA TIMUR =====
            [
                'kode_iso'        => 'CHN',
                'nama'            => 'Tiongkok',
                'ibu_kota'        => 'Beijing',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Eastern Asia',
                'lintang'         => 35.8617,
                'bujur'           => 104.1954,
                'populasi'        => 1412600000,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'JPN',
                'nama'            => 'Jepang',
                'ibu_kota'        => 'Tokyo',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Eastern Asia',
                'lintang'         => 36.2048,
                'bujur'           => 138.2529,
                'populasi'        => 124612530,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'KOR',
                'nama'            => 'Korea Selatan',
                'ibu_kota'        => 'Seoul',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Eastern Asia',
                'lintang'         => 35.9078,
                'bujur'           => 127.7669,
                'populasi'        => 51744876,
                'status_pemantauan' => true,
            ],
            // ===== ASIA SELATAN =====
            [
                'kode_iso'        => 'IND',
                'nama'            => 'India',
                'ibu_kota'        => 'New Delhi',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Southern Asia',
                'lintang'         => 20.5937,
                'bujur'           => 78.9629,
                'populasi'        => 1428627663,
                'status_pemantauan' => true,
            ],
            // ===== ASIA BARAT =====
            [
                'kode_iso'        => 'SAU',
                'nama'            => 'Arab Saudi',
                'ibu_kota'        => 'Riyadh',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Western Asia',
                'lintang'         => 23.8859,
                'bujur'           => 45.0792,
                'populasi'        => 36947025,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'ARE',
                'nama'            => 'Uni Emirat Arab',
                'ibu_kota'        => 'Abu Dhabi',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Western Asia',
                'lintang'         => 23.4241,
                'bujur'           => 53.8478,
                'populasi'        => 9890402,
                'status_pemantauan' => true,
            ],
            // ===== EROPA =====
            [
                'kode_iso'        => 'DEU',
                'nama'            => 'Jerman',
                'ibu_kota'        => 'Berlin',
                'kawasan'         => 'Eropa',
                'sub_kawasan'     => 'Western Europe',
                'lintang'         => 51.1657,
                'bujur'           => 10.4515,
                'populasi'        => 83369843,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'NLD',
                'nama'            => 'Belanda',
                'ibu_kota'        => 'Amsterdam',
                'kawasan'         => 'Eropa',
                'sub_kawasan'     => 'Western Europe',
                'lintang'         => 52.1326,
                'bujur'           => 5.2913,
                'populasi'        => 17590672,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'GBR',
                'nama'            => 'Britania Raya',
                'ibu_kota'        => 'London',
                'kawasan'         => 'Eropa',
                'sub_kawasan'     => 'Northern Europe',
                'lintang'         => 55.3781,
                'bujur'           => -3.4360,
                'populasi'        => 67735600,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'FRA',
                'nama'            => 'Prancis',
                'ibu_kota'        => 'Paris',
                'kawasan'         => 'Eropa',
                'sub_kawasan'     => 'Western Europe',
                'lintang'         => 46.2276,
                'bujur'           => 2.2137,
                'populasi'        => 67871925,
                'status_pemantauan' => true,
            ],
            // ===== AMERIKA =====
            [
                'kode_iso'        => 'USA',
                'nama'            => 'Amerika Serikat',
                'ibu_kota'        => 'Washington D.C.',
                'kawasan'         => 'Amerika',
                'sub_kawasan'     => 'Northern America',
                'lintang'         => 37.0902,
                'bujur'           => -95.7129,
                'populasi'        => 331893745,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'CAN',
                'nama'            => 'Kanada',
                'ibu_kota'        => 'Ottawa',
                'kawasan'         => 'Amerika',
                'sub_kawasan'     => 'Northern America',
                'lintang'         => 56.1304,
                'bujur'           => -106.3468,
                'populasi'        => 38250000,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'BRA',
                'nama'            => 'Brasil',
                'ibu_kota'        => 'Brasília',
                'kawasan'         => 'Amerika',
                'sub_kawasan'     => 'South America',
                'lintang'         => -14.2350,
                'bujur'           => -51.9253,
                'populasi'        => 215313498,
                'status_pemantauan' => true,
            ],
            // ===== OSEANIA =====
            [
                'kode_iso'        => 'AUS',
                'nama'            => 'Australia',
                'ibu_kota'        => 'Canberra',
                'kawasan'         => 'Oseania',
                'sub_kawasan'     => 'Australia and New Zealand',
                'lintang'         => -25.2744,
                'bujur'           => 133.7751,
                'populasi'        => 25921100,
                'status_pemantauan' => true,
            ],
            // ===== AFRIKA =====
            [
                'kode_iso'        => 'ZAF',
                'nama'            => 'Afrika Selatan',
                'ibu_kota'        => 'Pretoria',
                'kawasan'         => 'Afrika',
                'sub_kawasan'     => 'Sub-Saharan Africa',
                'lintang'         => -30.5595,
                'bujur'           => 22.9375,
                'populasi'        => 60756135,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'NGA',
                'nama'            => 'Nigeria',
                'ibu_kota'        => 'Abuja',
                'kawasan'         => 'Afrika',
                'sub_kawasan'     => 'Sub-Saharan Africa',
                'lintang'         => 9.0820,
                'bujur'           => 8.6753,
                'populasi'        => 218541212,
                'status_pemantauan' => true,
            ],
            // ===== EROPA TIMUR =====
            [
                'kode_iso'        => 'RUS',
                'nama'            => 'Rusia',
                'ibu_kota'        => 'Moskow',
                'kawasan'         => 'Eropa',
                'sub_kawasan'     => 'Eastern Europe',
                'lintang'         => 61.5240,
                'bujur'           => 105.3188,
                'populasi'        => 144444359,
                'status_pemantauan' => true,
            ],
            // ===== ASIA TENGAH =====
            [
                'kode_iso'        => 'PAK',
                'nama'            => 'Pakistan',
                'ibu_kota'        => 'Islamabad',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Southern Asia',
                'lintang'         => 30.3753,
                'bujur'           => 69.3451,
                'populasi'        => 229488994,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'BGD',
                'nama'            => 'Bangladesh',
                'ibu_kota'        => 'Dhaka',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Southern Asia',
                'lintang'         => 23.6850,
                'bujur'           => 90.3563,
                'populasi'        => 169356251,
                'status_pemantauan' => true,
            ],
            // ===== LAINNYA =====
            [
                'kode_iso'        => 'MEX',
                'nama'            => 'Meksiko',
                'ibu_kota'        => 'Mexico City',
                'kawasan'         => 'Amerika',
                'sub_kawasan'     => 'Latin America and the Caribbean',
                'lintang'         => 23.6345,
                'bujur'           => -102.5528,
                'populasi'        => 130861000,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'TUR',
                'nama'            => 'Turki',
                'ibu_kota'        => 'Ankara',
                'kawasan'         => 'Asia',
                'sub_kawasan'     => 'Western Asia',
                'lintang'         => 38.9637,
                'bujur'           => 35.2433,
                'populasi'        => 84339067,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'EGY',
                'nama'            => 'Mesir',
                'ibu_kota'        => 'Kairo',
                'kawasan'         => 'Afrika',
                'sub_kawasan'     => 'Northern Africa',
                'lintang'         => 26.0975,
                'bujur'           => 29.9099,
                'populasi'        => 104258327,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'ARG',
                'nama'            => 'Argentina',
                'ibu_kota'        => 'Buenos Aires',
                'kawasan'         => 'Amerika',
                'sub_kawasan'     => 'South America',
                'lintang'         => -38.4161,
                'bujur'           => -63.6167,
                'populasi'        => 45510318,
                'status_pemantauan' => true,
            ],
            [
                'kode_iso'        => 'ZAF',
                'nama'            => 'Ukraina',
                'ibu_kota'        => 'Kyiv',
                'kawasan'         => 'Eropa',
                'sub_kawasan'     => 'Eastern Europe',
                'lintang'         => 48.3794,
                'bujur'           => 31.1656,
                'populasi'        => 43467000,
                'status_pemantauan' => false, // Konflik aktif
            ],
        ];

        $ditambahkan = 0;
        $dilewati    = 0;

        foreach ($daftarNegara as $data) {
            $sudahAda = Negara::where('kode_iso', $data['kode_iso'])->exists();
            if ($sudahAda) {
                $dilewati++;
                continue;
            }
            Negara::create($data);
            $ditambahkan++;
        }

        $this->command->info("✓ Negara selesai: {$ditambahkan} ditambahkan, {$dilewati} dilewati (sudah ada).");
    }
}
