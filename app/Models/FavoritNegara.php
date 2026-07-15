<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model FavoritNegara
 *
 * Menyimpan bookmark negara pilihan pengguna.
 */
class FavoritNegara extends Model
{
    protected $table = 'favorit_negara';

    protected $fillable = [
        'pengguna_id',
        'negara_id',
    ];

    /**
     * Relasi ke Pengguna.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    /**
     * Relasi ke Negara.
     */
    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }
}
