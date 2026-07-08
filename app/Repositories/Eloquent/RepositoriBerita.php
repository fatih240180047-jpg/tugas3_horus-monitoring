<?php

namespace App\Repositories\Eloquent;

use App\DTO\DtoBerita;
use App\Models\ArtikelBerita;
use App\Models\Negara;
use App\Repositories\Kontrak\RepositoriBeritaInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Repositori Berita Menggunakan Eloquent
 */
class RepositoriBerita implements RepositoriBeritaInterface
{
    /**
     * Simpan berita baru. Jika sudah ada judul yang persis sama, abaikan untuk mencegah duplikat.
     */
    public function simpan(Negara $negara, DtoBerita $dto): ?ArtikelBerita
    {
        if ($this->sudahAda($negara->id, $dto->judul)) {
            return null;
        }

        return ArtikelBerita::create(array_merge($dto->keArray(), [
            'negara_id'   => $negara->id,
            'dibuat_pada' => Carbon::now(),
        ]));
    }

    /**
     * Dapatkan daftar berita terkini untuk negara tertentu.
     *
     * @return Collection<int, ArtikelBerita>
     */
    public function terkini(Negara $negara, int $batas = 10): Collection
    {
        return ArtikelBerita::where('negara_id', $negara->id)
            ->orderBy('diterbitkan_pada', 'desc')
            ->limit($batas)
            ->get();
    }

    /**
     * Cek apakah sudah ada berita dengan judul tertentu untuk negara tersebut.
     */
    public function sudahAda(int $negaraId, string $judul): bool
    {
        return ArtikelBerita::where('negara_id', $negaraId)
            ->where('judul', $judul)
            ->exists();
    }
}
