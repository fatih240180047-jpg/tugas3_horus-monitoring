<?php

namespace App\Repositories\Kontrak;

use App\DTO\DtoBerita;
use App\Models\ArtikelBerita;
use App\Models\Negara;

/**
 * Interface Repositori Berita
 */
interface RepositoriBeritaInterface
{
    public function simpan(Negara $negara, DtoBerita $dto): ?ArtikelBerita;

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ArtikelBerita>
     */
    public function terkini(Negara $negara, int $batas = 10): \Illuminate\Database\Eloquent\Collection;

    public function sudahAda(int $negaraId, string $judul): bool;
}
