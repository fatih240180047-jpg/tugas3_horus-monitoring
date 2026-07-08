<?php

namespace App\Services\Kontrak;

use App\Models\CatatanCuaca;
use App\Models\Negara;

/**
 * Kontrak Layanan Cuaca
 */
interface LayananCuacaInterface
{
    /**
     * Sinkronisasikan data cuaca terkini untuk sebuah negara.
     * Menggunakan API OpenWeather jika terkonfigurasi, jika tidak beralih ke Mode Simulasi.
     */
    public function sinkronkan(Negara $negara): CatatanCuaca;
}
