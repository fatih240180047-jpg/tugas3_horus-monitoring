<?php

namespace App\Repositories\Eloquent;

use App\DTO\DtoEkonomi;
use App\Models\IndikatorEkonomi;
use App\Models\Negara;
use App\Repositories\Kontrak\RepositoriEkonomiInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Repositori Ekonomi Menggunakan Eloquent
 */
class RepositoriEkonomi implements RepositoriEkonomiInterface
{
    /**
     * Simpan data indikator ekonomi.
     */
    public function simpan(Negara $negara, DtoEkonomi $dto): IndikatorEkonomi
    {
        return IndikatorEkonomi::updateOrCreate(
            [
                'negara_id'         => $negara->id,
                'tanggal_indikator' => $dto->tanggalIndikator,
            ],
            array_merge($dto->keArray(), ['dibuat_pada' => Carbon::now()])
        );
    }

    /**
     * Ambil data ekonomi terkini untuk sebuah negara.
     */
    public function terkini(Negara $negara): ?IndikatorEkonomi
    {
        return IndikatorEkonomi::where('negara_id', $negara->id)
            ->orderBy('tanggal_indikator', 'desc')
            ->first();
    }

    /**
     * Ambil riwayat data ekonomi dalam rentang tahun.
     *
     * @return Collection<int, IndikatorEkonomi>
     */
    public function riwayat(Negara $negara, string $dariTahun, string $sampaiTahun): Collection
    {
        $dariTanggal   = $dariTahun . '-01-01';
        $sampaiTanggal = $sampaiTahun . '-12-31';

        return IndikatorEkonomi::where('negara_id', $negara->id)
            ->whereBetween('tanggal_indikator', [$dariTanggal, $sampaiTanggal])
            ->orderBy('tanggal_indikator', 'asc')
            ->get();
    }

    /**
     * Cek apakah data ekonomi untuk negara dan tanggal indikator tertentu sudah ada.
     */
    public function sudahAda(int $negaraId, string $tanggalIndikator): bool
    {
        return IndikatorEkonomi::where('negara_id', $negaraId)
            ->where('tanggal_indikator', $tanggalIndikator)
            ->exists();
    }
}
