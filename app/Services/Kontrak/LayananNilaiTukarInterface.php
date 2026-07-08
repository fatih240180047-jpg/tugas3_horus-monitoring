<?php

namespace App\Services\Kontrak;

use App\Models\Negara;
use App\Models\NilaiTukar;

/**
 * Kontrak Layanan Nilai Tukar
 */
interface LayananNilaiTukarInterface
{
    /**
     * Sinkronisasikan nilai tukar mata uang terkini untuk sebuah negara terhadap USD.
     */
    public function sinkronkan(Negara $negara, string $kodeMataUang): NilaiTukar;
}
