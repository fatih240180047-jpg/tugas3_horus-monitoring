<?php

namespace App\Repositories\Kontrak;

use App\Models\Negara;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface Repositori Negara
 */
interface RepositoriNegaraInterface
{
    /**
     * Dapatkan semua negara yang dipantau.
     *
     * @return Collection<int, Negara>
     */
    public function semuaYangDipantau(): Collection;

    /**
     * Cari negara berdasarkan ID.
     */
    public function temukan(int $id): ?Negara;

    /**
     * Cari negara berdasarkan Kode ISO.
     */
    public function temukanBerdasarkanKodeIso(string $kodeIso): ?Negara;

    /**
     * Simpan negara baru atau perbarui negara lama.
     */
    public function simpan(array $data): Negara;
}
