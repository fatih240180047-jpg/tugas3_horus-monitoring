<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Peran (Role)
 *
 * Mendefinisikan peran keamanan dalam platform.
 *
 * Contoh peran:
 * - super-administrator
 * - administrator
 * - eksekutif
 * - analis-risiko
 * - manajer-pengadaan
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 */
class Peran extends Model
{
    protected $table = 'peran';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Peran memiliki banyak pengguna (many-to-many).
     */
    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(
            Pengguna::class,
            'peran_pengguna',
            'peran_id',
            'pengguna_id'
        )->withPivot('ditetapkan_pada');
    }

    /**
     * Peran memiliki banyak izin (many-to-many).
     */
    public function izin(): BelongsToMany
    {
        return $this->belongsToMany(
            Izin::class,
            'peran_izin',
            'peran_id',
            'izin_id'
        );
    }
}
