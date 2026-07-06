<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model IndikatorEkonomi (Economic Indicator)
 *
 * Menyimpan indikator makroekonomi dari World Bank API.
 * Data bersifat append-only (riwayat tidak pernah ditimpa).
 */
class IndikatorEkonomi extends Model
{
    protected $table = 'indikator_ekonomi';

    public $timestamps = false;

    protected $fillable = [
        'negara_id',
        'tanggal_indikator',
        'pdb',
        'tingkat_inflasi',
        'tingkat_pengangguran',
        'tingkat_bunga',
        'neraca_perdagangan',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_indikator'     => 'date',
            'pdb'                   => 'float',
            'tingkat_inflasi'       => 'float',
            'tingkat_pengangguran'  => 'float',
            'tingkat_bunga'         => 'float',
            'neraca_perdagangan'    => 'float',
            'dibuat_pada'           => 'datetime',
        ];
    }

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }
}
