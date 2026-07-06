<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Izin (Permission)
 *
 * Menyimpan semua izin yang digunakan oleh sistem RBAC.
 *
 * Contoh slug izin:
 * - pengguna.lihat
 * - pengguna.buat
 * - pengguna.ubah
 * - pengguna.hapus
 * - negara.lihat
 * - negara.kelola
 * - risiko.hitung
 * - laporan.ekspor
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $modul
 */
class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = [
        'name',
        'slug',
        'modul',
    ];

    /**
     * Izin dimiliki oleh banyak peran (many-to-many).
     */
    public function peran(): BelongsToMany
    {
        return $this->belongsToMany(
            Peran::class,
            'peran_izin',
            'izin_id',
            'peran_id'
        );
    }
}
