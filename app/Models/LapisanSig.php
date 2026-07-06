<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Model LapisanSig (GIS Layer) */
class LapisanSig extends Model
{
    protected $table = 'lapisan_sig';
    public $timestamps = false;

    protected $fillable = [
        'negara_id', 'lintang', 'bujur',
        'jenis_lapisan', 'warna', 'ukuran_penanda', 'data_popup',
        'diperbarui_pada',
    ];

    protected function casts(): array
    {
        return [
            'lintang'       => 'float',
            'bujur'         => 'float',
            'data_popup'    => 'array',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }
}
