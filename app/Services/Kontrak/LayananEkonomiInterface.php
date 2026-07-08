<?php

namespace App\Services\Kontrak;

use App\Models\IndikatorEkonomi;
use App\Models\Negara;

/**
 * Kontrak Layanan Ekonomi
 */
interface LayananEkonomiInterface
{
    /**
     * Sinkronisasikan indikator makroekonomi tahunan untuk sebuah negara.
     */
    public function sinkronkan(Negara $negara, int $tahun): IndikatorEkonomi;
}
