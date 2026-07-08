<?php

namespace App\Repositories\Eloquent;

use App\Models\Negara;
use App\Repositories\Kontrak\RepositoriNegaraInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repositori Negara Menggunakan Eloquent
 */
class RepositoriNegara implements RepositoriNegaraInterface
{
    /**
     * Dapatkan semua negara yang aktif dipantau.
     *
     * @return Collection<int, Negara>
     */
    public function semuaYangDipantau(): Collection
    {
        return Negara::where('status_pemantauan', true)->get();
    }

    /**
     * Cari negara berdasarkan ID.
     */
    public function temukan(int $id): ?Negara
    {
        return Negara::find($id);
    }

    /**
     * Cari negara berdasarkan Kode ISO (case-insensitive).
     */
    public function temukanBerdasarkanKodeIso(string $kodeIso): ?Negara
    {
        return Negara::where('kode_iso', strtoupper($kodeIso))->first();
    }

    /**
     * Simpan negara baru atau perbarui negara lama.
     */
    public function simpan(array $data): Negara
    {
        return Negara::updateOrCreate(
            ['kode_iso' => strtoupper($data['kode_iso'])],
            $data
        );
    }
}
