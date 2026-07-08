<?php

namespace App\Repositories\Eloquent;

use App\DTO\DtoNilaiTukar;
use App\Models\Negara;
use App\Models\NilaiTukar;
use App\Repositories\Kontrak\RepositoriNilaiTukarInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Repositori Nilai Tukar Menggunakan Eloquent
 */
class RepositoriNilaiTukar implements RepositoriNilaiTukarInterface
{
    /**
     * Simpan data nilai tukar baru.
     */
    public function simpan(Negara $negara, DtoNilaiTukar $dto): NilaiTukar
    {
        return NilaiTukar::updateOrCreate(
            [
                'negara_id'       => $negara->id,
                'kode_mata_uang'  => $dto->kodeMataUang,
                'tanggal_berlaku' => $dto->tanggalBerlaku,
            ],
            array_merge($dto->keArray(), ['dibuat_pada' => Carbon::now()])
        );
    }

    /**
     * Dapatkan nilai tukar terkini untuk negara dan kode mata uang tertentu.
     */
    public function terkini(Negara $negara, string $kodeMataUang): ?NilaiTukar
    {
        return NilaiTukar::where('negara_id', $negara->id)
            ->where('kode_mata_uang', strtoupper($kodeMataUang))
            ->orderBy('tanggal_berlaku', 'desc')
            ->first();
    }

    /**
     * Cek apakah nilai tukar tertentu sudah ada di tanggal tersebut.
     */
    public function sudahAda(int $negaraId, string $kodeMataUang, string $tanggal): bool
    {
        return NilaiTukar::where('negara_id', $negaraId)
            ->where('kode_mata_uang', strtoupper($kodeMataUang))
            ->where('tanggal_berlaku', $tanggal)
            ->exists();
    }

    /**
     * Dapatkan seluruh nilai tukar terkini untuk seluruh mata uang milik negara tersebut.
     *
     * @return Collection<int, NilaiTukar>
     */
    public function semuaTerkini(Negara $negara): Collection
    {
        // Mendapatkan record unik berdasarkan kode_mata_uang dengan tanggal terbaru
        return NilaiTukar::where('negara_id', $negara->id)
            ->whereIn('id', function($query) use ($negara) {
                $query->selectRaw('MAX(id)')
                    ->from('nilai_tukar')
                    ->where('negara_id', $negara->id)
                    ->groupBy('kode_mata_uang');
            })
            ->get();
    }
}
