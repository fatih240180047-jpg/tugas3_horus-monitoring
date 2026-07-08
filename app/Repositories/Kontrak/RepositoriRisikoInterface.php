<?php

namespace App\Repositories\Kontrak;

use App\Models\Negara;
use App\Models\PenilaianRisiko;
use App\Models\RekomendasiRisiko;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface Repositori Risiko
 */
interface RepositoriRisikoInterface
{
    /**
     * Simpan penilaian risiko baru.
     */
    public function simpanPenilaian(Negara $negara, array $data): PenilaianRisiko;

    /**
     * Simpan rekomendasi risiko baru.
     */
    public function simpanRekomendasi(PenilaianRisiko $penilaian, array $data): RekomendasiRisiko;

    /**
     * Dapatkan penilaian risiko terkini untuk sebuah negara.
     */
    public function terkini(Negara $negara): ?PenilaianRisiko;

    /**
     * Dapatkan riwayat penilaian risiko untuk sebuah negara.
     *
     * @return Collection<int, PenilaianRisiko>
     */
    public function riwayat(Negara $negara, int $batas = 30): Collection;

    /**
     * Dapatkan semua penilaian risiko terbaru dari setiap negara yang dipantau.
     *
     * @return Collection<int, PenilaianRisiko>
     */
    public function semuaTerkini(): Collection;
}
