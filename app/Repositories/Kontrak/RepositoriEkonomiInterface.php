<?php

namespace App\Repositories\Kontrak;

use App\DTO\DtoEkonomi;
use App\Models\IndikatorEkonomi;
use App\Models\Negara;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface Repositori Ekonomi
 */
interface RepositoriEkonomiInterface
{
    /**
     * Simpan data indikator ekonomi.
     */
    public function simpan(Negara $negara, DtoEkonomi $dto): IndikatorEkonomi;

    /**
     * Ambil data ekonomi terkini untuk sebuah negara.
     */
    public function terkini(Negara $negara): ?IndikatorEkonomi;

    /**
     * Ambil riwayat data ekonomi dalam rentang tahun.
     *
     * @return Collection<int, IndikatorEkonomi>
     */
    public function riwayat(Negara $negara, string $dariTahun, string $sampaiTahun): Collection;

    /**
     * Cek apakah data ekonomi untuk negara dan tanggal indikator tertentu sudah ada.
     */
    public function sudahAda(int $negaraId, string $tanggalIndikator): bool;
}
