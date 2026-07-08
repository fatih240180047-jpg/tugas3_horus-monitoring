<?php

namespace App\Repositories\Kontrak;

use App\DTO\DtoNilaiTukar;
use App\Models\Negara;
use App\Models\NilaiTukar;

/**
 * Interface Repositori Nilai Tukar
 */
interface RepositoriNilaiTukarInterface
{
    public function simpan(Negara $negara, DtoNilaiTukar $dto): NilaiTukar;
    public function terkini(Negara $negara, string $kodeMataUang): ?NilaiTukar;
    public function sudahAda(int $negaraId, string $kodeMataUang, string $tanggal): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, NilaiTukar>
     */
    public function semuaTerkini(Negara $negara): \Illuminate\Database\Eloquent\Collection;
}
