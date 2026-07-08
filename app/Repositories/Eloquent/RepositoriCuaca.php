<?php

namespace App\Repositories\Eloquent;

use App\DTO\DtoCuaca;
use App\Models\CatatanCuaca;
use App\Models\Negara;
use App\Repositories\Kontrak\RepositoriCuacaInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Repositori Cuaca Menggunakan Eloquent
 */
class RepositoriCuaca implements RepositoriCuacaInterface
{
    /**
     * Simpan data cuaca.
     */
    public function simpan(Negara $negara, DtoCuaca $dto): CatatanCuaca
    {
        return CatatanCuaca::updateOrCreate(
            [
                'negara_id'         => $negara->id,
                'tanggal_observasi' => $dto->tanggalObservasi,
            ],
            array_merge($dto->keArray(), ['dibuat_pada' => Carbon::now()])
        );
    }

    /**
     * Dapatkan catatan cuaca terkini untuk negara tertentu.
     */
    public function terkini(Negara $negara): ?CatatanCuaca
    {
        return CatatanCuaca::where('negara_id', $negara->id)
            ->orderBy('tanggal_observasi', 'desc')
            ->first();
    }

    /**
     * Dapatkan riwayat cuaca dalam rentang tanggal tertentu.
     *
     * @return Collection<int, CatatanCuaca>
     */
    public function riwayat(Negara $negara, string $dari, string $sampai): Collection
    {
        return CatatanCuaca::where('negara_id', $negara->id)
            ->whereBetween('tanggal_observasi', [$dari, $sampai])
            ->orderBy('tanggal_observasi', 'asc')
            ->get();
    }

    /**
     * Cek apakah sudah ada data cuaca untuk negara dan tanggal tertentu.
     */
    public function sudahAda(int $negaraId, string $tanggal): bool
    {
        return CatatanCuaca::where('negara_id', $negaraId)
            ->where('tanggal_observasi', $tanggal)
            ->exists();
    }
}
