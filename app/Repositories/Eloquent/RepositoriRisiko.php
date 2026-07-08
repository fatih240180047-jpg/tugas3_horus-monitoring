<?php

namespace App\Repositories\Eloquent;

use App\Models\Negara;
use App\Models\PenilaianRisiko;
use App\Models\RekomendasiRisiko;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Repositori Risiko Menggunakan Eloquent
 */
class RepositoriRisiko implements RepositoriRisikoInterface
{
    /**
     * Simpan penilaian risiko baru.
     */
    public function simpanPenilaian(Negara $negara, array $data): PenilaianRisiko
    {
        return PenilaianRisiko::create(array_merge($data, [
            'negara_id'     => $negara->id,
            'dihitung_pada' => Carbon::now(),
            'dibuat_pada'   => Carbon::now(),
        ]));
    }

    /**
     * Simpan rekomendasi risiko baru.
     */
    public function simpanRekomendasi(PenilaianRisiko $penilaian, array $data): RekomendasiRisiko
    {
        return RekomendasiRisiko::create(array_merge($data, [
            'penilaian_risiko_id' => $penilaian->id,
            'dibuat_pada'         => Carbon::now(),
        ]));
    }

    /**
     * Dapatkan penilaian risiko terkini untuk sebuah negara.
     */
    public function terkini(Negara $negara): ?PenilaianRisiko
    {
        return PenilaianRisiko::where('negara_id', $negara->id)
            ->orderBy('dihitung_pada', 'desc')
            ->first();
    }

    /**
     * Dapatkan riwayat penilaian risiko untuk sebuah negara.
     *
     * @return Collection<int, PenilaianRisiko>
     */
    public function riwayat(Negara $negara, int $batas = 30): Collection
    {
        return PenilaianRisiko::where('negara_id', $negara->id)
            ->orderBy('dihitung_pada', 'desc')
            ->limit($batas)
            ->get();
    }

    /**
     * Dapatkan semua penilaian risiko terbaru dari setiap negara yang dipantau.
     *
     * @return Collection<int, PenilaianRisiko>
     */
    public function semuaTerkini(): Collection
    {
        return PenilaianRisiko::with('negara')
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                    ->from('penilaian_risiko')
                    ->groupBy('negara_id');
            })
            ->get();
    }
}
