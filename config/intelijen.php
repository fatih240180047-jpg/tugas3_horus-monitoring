<?php

/**
 * Konfigurasi Layanan API Intelijen Eksternal
 *
 * Setiap provider memiliki konfigurasi tersendiri yang dapat
 * disesuaikan melalui file .env tanpa mengubah kode sumber.
 *
 * Jika kunci API dikosongkan, platform akan secara otomatis
 * menggunakan MODE SIMULASI untuk menghasilkan data realistis.
 */
return [

    // ============================================================
    // Provider Cuaca: OpenWeather API
    // ============================================================
    'cuaca' => [
        'aktif'          => env('WEATHER_API_KEY') !== '',
        'kunci_api'      => env('WEATHER_API_KEY', ''),
        'url_dasar'      => env('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5'),
        'timeout'        => 30,
        'maks_percobaan' => 3,
        'jeda_percobaan' => 10,
        'durasi_cache'   => 15,  // Menit
        'satuan'         => 'metric', // Celsius
        'nama_antrian'   => 'cuaca',
    ],

    // ============================================================
    // Provider Ekonomi: World Bank API
    // ============================================================
    'ekonomi' => [
        'aktif'          => true, // Public API, tidak butuh kunci
        'kunci_api'      => '',
        'url_dasar'      => env('WORLD_BANK_API_URL', 'https://api.worldbank.org/v2'),
        'timeout'        => 60,
        'maks_percobaan' => 2,
        'jeda_percobaan' => 30,
        'durasi_cache'   => 1440, // Menit (24 jam)
        'format'         => 'json',
        'nama_antrian'   => 'ekonomi',
        // Kode indikator World Bank
        'indikator' => [
            'pdb'                  => 'NY.GDP.MKTP.CD',
            'tingkat_inflasi'      => 'FP.CPI.TOTL.ZG',
            'tingkat_pengangguran' => 'SL.UEM.TOTL.ZS',
            'tingkat_bunga'        => 'FR.INR.RINR',
            'neraca_perdagangan'   => 'BN.CAB.XOKA.CD',
        ],
    ],

    // ============================================================
    // Provider Nilai Tukar: ExchangeRate API
    // ============================================================
    'nilai_tukar' => [
        'aktif'          => env('EXCHANGE_RATE_API_KEY') !== '',
        'kunci_api'      => env('EXCHANGE_RATE_API_KEY', ''),
        'url_dasar'      => env('EXCHANGE_RATE_API_URL', 'https://v6.exchangerate-api.com/v6'),
        'timeout'        => 30,
        'maks_percobaan' => 3,
        'jeda_percobaan' => 10,
        'durasi_cache'   => 30,  // Menit
        'mata_uang_dasar'=> 'USD',
        'nama_antrian'   => 'nilai_tukar',
    ],

    // ============================================================
    // Provider Berita: NewsAPI
    // ============================================================
    'berita' => [
        'aktif'          => env('NEWS_API_KEY') !== '',
        'kunci_api'      => env('NEWS_API_KEY', ''),
        'url_dasar'      => env('NEWS_API_URL', 'https://newsapi.org/v2'),
        'timeout'        => 45,
        'maks_percobaan' => 3,
        'jeda_percobaan' => 10,
        'durasi_cache'   => 10,  // Menit
        'bahasa'         => 'en',
        'halaman_per_minta' => 100,
        'nama_antrian'   => 'berita',
        // Kata kunci pencarian per kategori
        'kategori_kunci' => [
            'supply_chain'  => 'supply chain OR logistics OR shipping',
            'ekonomi'       => 'economy OR inflation OR trade',
            'politik'       => 'political crisis OR sanctions OR conflict',
            'bencana'       => 'natural disaster OR flood OR earthquake',
        ],
    ],

    // ============================================================
    // Provider GIS: OpenStreetMap (Leaflet)
    // ============================================================
    'sig' => [
        'aktif'        => true, // Public, tidak butuh kunci
        'tile_url'     => env('OPENSTREETMAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'atribusi'     => '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        'zoom_default' => 2,
        'zoom_maks'    => 18,
        'zoom_min'     => 2,
        'timeout'      => 20,
    ],

    // ============================================================
    // Mode Simulasi Global
    // ============================================================
    'simulasi' => [
        'aktif'          => env('API_SIMULATION_MODE', true),
        'deskripsi'      => 'Mode simulasi aktif ketika kunci API tidak dikonfigurasi.',
        'variasi_data'   => true,  // Tambahkan variasi acak pada data mock
        'seed_acak'      => 12345, // Seed untuk data acak yang konsisten
    ],

];
