<?php

namespace App\Repositories\Kontrak;

use App\DTO\DtoCuaca;
use App\Models\CatatanCuaca;
use App\Models\Negara;

/**
 * Interface Repositori Cuaca
 *
 * Mendefinisikan kontrak persisten untuk data cuaca.
 * Semua akses database cuaca wajib melalui interface ini.
 */
interface RepositoriCuacaInterface
{
    /**
     * Simpan satu catatan cuaca. Abaikan duplikat (negara + tanggal).
     */
    public function simpan(Negara $negara, DtoCuaca $dto): CatatanCuaca;

    /**
     * Ambil cuaca terkini untuk sebuah negara.
     */
    public function terkini(Negara $negara): ?CatatanCuaca;

    /**
     * Ambil riwayat cuaca dalam rentang tanggal.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, CatatanCuaca>
     */
    public function riwayat(Negara $negara, string $dari, string $sampai): \Illuminate\Database\Eloquent\Collection;

    /**
     * Cek apakah sudah ada data untuk negara dan tanggal tertentu.
     */
    public function sudahAda(int $negaraId, string $tanggal): bool;
}
