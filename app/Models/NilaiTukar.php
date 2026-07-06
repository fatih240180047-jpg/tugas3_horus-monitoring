<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model NilaiTukar (Exchange Rate)
 *
 * Menyimpan nilai tukar historis dari ExchangeRate API.
 * Data bersifat append-only dan immutable setelah disimpan.
 */
class NilaiTukar extends Model
{
    protected $table = 'nilai_tukar';

    public $timestamps = false;

    protected $fillable = [
        'negara_id',
        'kode_mata_uang',
        'nilai_tukar',
        'tanggal_berlaku',
        'sumber_api',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'nilai_tukar'      => 'float',
            'tanggal_berlaku'  => 'date',
            'dibuat_pada'      => 'datetime',
        ];
    }

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }
}
