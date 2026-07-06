<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model RekomendasiRisiko (Risk Recommendation)
 *
 * Rekomendasi bisnis yang dihasilkan otomatis oleh Mesin Rekomendasi.
 * Setiap rekomendasi selalu mereferensi sebuah PenilaianRisiko.
 */
class RekomendasiRisiko extends Model
{
    protected $table = 'rekomendasi_risiko';

    public $timestamps = false;

    protected $fillable = [
        'penilaian_risiko_id',
        'rekomendasi',
        'prioritas',
        'status',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'dibuat_pada' => 'datetime',
        ];
    }

    public function penilaianRisiko(): BelongsTo
    {
        return $this->belongsTo(PenilaianRisiko::class, 'penilaian_risiko_id');
    }
}
