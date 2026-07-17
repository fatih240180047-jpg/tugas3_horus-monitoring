<?php

namespace Database\Seeders;

use App\Models\Negara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

/**
 * Seeder Negara — Versi 3.0 (Data Riil Global)
 *
 * Mengisi tabel negara secara otomatis dengan data dari GitHub:
 * - Metadata dari mledoze/countries
 * - Terjemahan nama Bahasa Indonesia dari umpirsky/country-list
 */
class NegaraSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌍 Mengambil metadata negara...');
        $resMetadata = Http::timeout(30)->get('https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json');
        
        $this->command->info('🌍 Mengambil terjemahan nama negara dalam Bahasa Indonesia...');
        $resTranslations = Http::timeout(30)->get('https://raw.githubusercontent.com/umpirsky/country-list/master/data/id/country.json');

        if (!$resMetadata->successful() || !$resTranslations->successful()) {
            $this->command->error("Gagal mengambil data dari GitHub. Menggunakan fallback...");
            $this->seedFallback();
            return;
        }

        $countries = $resMetadata->json();
        $translations = $resTranslations->json();

        $ditambahkan = 0;
        foreach ($countries as $c) {
            $cca2 = strtoupper($c['cca2'] ?? '');
            $cca3 = strtoupper($c['cca3'] ?? '');
            if (!$cca2 || !$cca3) continue;

            // Get translation, fallback to English common name
            $nama = $translations[$cca2] ?? ($c['name']['common'] ?? $cca3);
            
            // Konvensi khusus SCM
            if ($cca3 === 'CHN') $nama = 'Tiongkok';
            if ($cca3 === 'USA') $nama = 'Amerika Serikat';

            $ibuKota = isset($c['capital'][0]) ? $c['capital'][0] : null;
            $lintang = $c['latlng'][0] ?? null;
            $bujur = $c['latlng'][1] ?? null;
            
            $kawasan = $this->normalisasiKawasan($c['region'] ?? '');
            $subKawasan = $c['subregion'] ?? null;

            // Ekstrak populasi, mata uang dan bendera
            $populasi = $c['population'] ?? null;
            $currencies = $c['currencies'] ?? [];
            $mataUang = !empty($currencies) ? array_key_first($currencies) : null;
            $bendera = $c['flag'] ?? null;

            Negara::updateOrCreate(
                ['kode_iso' => $cca3],
                [
                    'nama' => $nama,
                    'ibu_kota' => $ibuKota,
                    'kawasan' => $kawasan,
                    'sub_kawasan' => $subKawasan,
                    'lintang' => $lintang,
                    'bujur' => $bujur,
                    'populasi' => $populasi,
                    'mata_uang' => $mataUang,
                    'bendera' => $bendera,
                    'status_pemantauan' => true,
                ]
            );
            $ditambahkan++;
        }

        $this->command->info("✅ Berhasil memuat {$ditambahkan} negara ke database!");
    }

    private function normalisasiKawasan(string $region): string
    {
        return match ($region) {
            'Africa'   => 'Afrika',
            'Americas' => 'Amerika',
            'Asia'     => 'Asia',
            'Europe'   => 'Eropa',
            'Oceania'  => 'Oseania',
            'Antarctic'=> 'Antarktika',
            default    => $region ?: 'Lainnya',
        };
    }
    
    private function seedFallback(): void
    {
        $daftarNegara = [
            ['kode_iso' => 'IDN', 'nama' => 'Indonesia',        'ibu_kota' => 'Jakarta',         'kawasan' => 'Asia',     'sub_kawasan' => 'South-Eastern Asia',   'lintang' => -0.7893,  'bujur' => 113.9213],
            ['kode_iso' => 'SGP', 'nama' => 'Singapura',        'ibu_kota' => 'Singapura',       'kawasan' => 'Asia',     'sub_kawasan' => 'South-Eastern Asia',   'lintang' => 1.3521,   'bujur' => 103.8198],
            ['kode_iso' => 'MYS', 'nama' => 'Malaysia',         'ibu_kota' => 'Kuala Lumpur',    'kawasan' => 'Asia',     'sub_kawasan' => 'South-Eastern Asia',   'lintang' => 4.2105,   'bujur' => 101.9758],
            ['kode_iso' => 'THA', 'nama' => 'Thailand',         'ibu_kota' => 'Bangkok',         'kawasan' => 'Asia',     'sub_kawasan' => 'South-Eastern Asia',   'lintang' => 15.8700,  'bujur' => 100.9925],
            ['kode_iso' => 'VNM', 'nama' => 'Vietnam',          'ibu_kota' => 'Hanoi',           'kawasan' => 'Asia',     'sub_kawasan' => 'South-Eastern Asia',   'lintang' => 14.0583,  'bujur' => 108.2772],
            ['kode_iso' => 'PHL', 'nama' => 'Filipina',         'ibu_kota' => 'Manila',          'kawasan' => 'Asia',     'sub_kawasan' => 'South-Eastern Asia',   'lintang' => 12.8797,  'bujur' => 121.7740],
            ['kode_iso' => 'CHN', 'nama' => 'Tiongkok',         'ibu_kota' => 'Beijing',         'kawasan' => 'Asia',     'sub_kawasan' => 'Eastern Asia',         'lintang' => 35.8617,  'bujur' => 104.1954],
            ['kode_iso' => 'JPN', 'nama' => 'Jepang',           'ibu_kota' => 'Tokyo',           'kawasan' => 'Asia',     'sub_kawasan' => 'Eastern Asia',         'lintang' => 36.2048,  'bujur' => 138.2529],
            ['kode_iso' => 'KOR', 'nama' => 'Korea Selatan',    'ibu_kota' => 'Seoul',           'kawasan' => 'Asia',     'sub_kawasan' => 'Eastern Asia',         'lintang' => 35.9078,  'bujur' => 127.7669],
            ['kode_iso' => 'IND', 'nama' => 'India',            'ibu_kota' => 'New Delhi',       'kawasan' => 'Asia',     'sub_kawasan' => 'Southern Asia',        'lintang' => 20.5937,  'bujur' => 78.9629],
            ['kode_iso' => 'SAU', 'nama' => 'Arab Saudi',       'ibu_kota' => 'Riyadh',          'kawasan' => 'Asia',     'sub_kawasan' => 'Western Asia',         'lintang' => 23.8859,  'bujur' => 45.0792],
            ['kode_iso' => 'ARE', 'nama' => 'Uni Emirat Arab',  'ibu_kota' => 'Abu Dhabi',       'kawasan' => 'Asia',     'sub_kawasan' => 'Western Asia',         'lintang' => 23.4241,  'bujur' => 53.8478],
            ['kode_iso' => 'DEU', 'nama' => 'Jerman',           'ibu_kota' => 'Berlin',          'kawasan' => 'Eropa',    'sub_kawasan' => 'Western Europe',       'lintang' => 51.1657,  'bujur' => 10.4515],
            ['kode_iso' => 'NLD', 'nama' => 'Belanda',          'ibu_kota' => 'Amsterdam',       'kawasan' => 'Eropa',    'sub_kawasan' => 'Western Europe',       'lintang' => 52.1326,  'bujur' => 5.2913],
            ['kode_iso' => 'GBR', 'nama' => 'Britania Raya',   'ibu_kota' => 'London',          'kawasan' => 'Eropa',    'sub_kawasan' => 'Northern Europe',      'lintang' => 55.3781,  'bujur' => -3.4360],
            ['kode_iso' => 'FRA', 'nama' => 'Prancis',         'ibu_kota' => 'Paris',           'kawasan' => 'Eropa',    'sub_kawasan' => 'Western Europe',       'lintang' => 46.2276,  'bujur' => 2.2137],
            ['kode_iso' => 'USA', 'nama' => 'Amerika Serikat', 'ibu_kota' => 'Washington D.C.', 'kawasan' => 'Amerika',  'sub_kawasan' => 'Northern America',     'lintang' => 37.0902,  'bujur' => -95.7129],
            ['kode_iso' => 'CAN', 'nama' => 'Kanada',          'ibu_kota' => 'Ottawa',          'kawasan' => 'Amerika',  'sub_kawasan' => 'Northern America',     'lintang' => 56.1304,  'bujur' => -106.3468],
            ['kode_iso' => 'BRA', 'nama' => 'Brasil',          'ibu_kota' => 'Brasília',        'kawasan' => 'Amerika',  'sub_kawasan' => 'South America',        'lintang' => -14.2350, 'bujur' => -51.9253],
            ['kode_iso' => 'AUS', 'nama' => 'Australia',       'ibu_kota' => 'Canberra',        'kawasan' => 'Oseania',  'sub_kawasan' => 'Australia and New Zealand','lintang' => -25.2744,'bujur' => 133.7751],
            ['kode_iso' => 'ZAF', 'nama' => 'Afrika Selatan',  'ibu_kota' => 'Pretoria',        'kawasan' => 'Afrika',   'sub_kawasan' => 'Sub-Saharan Africa',   'lintang' => -30.5595, 'bujur' => 22.9375],
            ['kode_iso' => 'NGA', 'nama' => 'Nigeria',         'ibu_kota' => 'Abuja',           'kawasan' => 'Afrika',   'sub_kawasan' => 'Sub-Saharan Africa',   'lintang' => 9.0820,   'bujur' => 8.6753],
            ['kode_iso' => 'RUS', 'nama' => 'Rusia',           'ibu_kota' => 'Moskow',          'kawasan' => 'Eropa',    'sub_kawasan' => 'Eastern Europe',       'lintang' => 61.5240,  'bujur' => 105.3188],
            ['kode_iso' => 'PAK', 'nama' => 'Pakistan',        'ibu_kota' => 'Islamabad',       'kawasan' => 'Asia',     'sub_kawasan' => 'Southern Asia',        'lintang' => 30.3753,  'bujur' => 69.3451],
            ['kode_iso' => 'BGD', 'nama' => 'Bangladesh',      'ibu_kota' => 'Dhaka',           'kawasan' => 'Asia',     'sub_kawasan' => 'Southern Asia',        'lintang' => 23.6850,  'bujur' => 90.3563],
            ['kode_iso' => 'MEX', 'nama' => 'Meksiko',         'ibu_kota' => 'Mexico City',     'kawasan' => 'Amerika',  'sub_kawasan' => 'Latin America',        'lintang' => 23.6345,  'bujur' => -102.5528],
            ['kode_iso' => 'TUR', 'nama' => 'Turki',           'ibu_kota' => 'Ankara',          'kawasan' => 'Asia',     'sub_kawasan' => 'Western Asia',         'lintang' => 38.9637,  'bujur' => 35.2433],
            ['kode_iso' => 'EGY', 'nama' => 'Mesir',           'ibu_kota' => 'Kairo',           'kawasan' => 'Afrika',   'sub_kawasan' => 'Northern Africa',      'lintang' => 26.0975,  'bujur' => 29.9099],
            ['kode_iso' => 'ARG', 'nama' => 'Argentina',       'ibu_kota' => 'Buenos Aires',    'kawasan' => 'Amerika',  'sub_kawasan' => 'South America',        'lintang' => -38.4161, 'bujur' => -63.6167],
            ['kode_iso' => 'UKR', 'nama' => 'Ukraina',         'ibu_kota' => 'Kyiv',            'kawasan' => 'Eropa',    'sub_kawasan' => 'Eastern Europe',       'lintang' => 48.3794,  'bujur' => 31.1656],
        ];

        $ditambahkan = 0;
        foreach ($daftarNegara as $data) {
            Negara::updateOrCreate(
                ['kode_iso' => $data['kode_iso']],
                array_merge($data, ['status_pemantauan' => true])
            );
            $ditambahkan++;
        }
        $this->command->info("✓ Fallback: {$ditambahkan} negara strategis berhasil dimuat.");
    }
}
