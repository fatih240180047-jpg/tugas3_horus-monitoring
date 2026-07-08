<?php

namespace App\Services\Kontrak;

use App\Models\Negara;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kontrak Layanan Berita
 */
interface LayananBeritaInterface
{
    /**
     * Sinkronisasikan artikel berita terkini yang relevan untuk sebuah negara.
     * Mengembalikan koleksi artikel berita yang berhasil disimpan.
     *
     * @return Collection
     */
    public function sinkronkan(Negara $negara): Collection;
}
