<?php

namespace App\Services\Kontrak;

use App\Models\Negara;
use App\Models\PenilaianRisiko;

/**
 * Kontrak Mesin Perhitungan Risiko
 */
interface MesinRisikoInterface
{
    /**
     * Hitung skor risiko total untuk sebuah negara berdasarkan data intelijen terbaru.
     * Menggunakan bobot tertimbang dari tabel pengaturan.
     */
    public function hitung(Negara $negara): PenilaianRisiko;
}
