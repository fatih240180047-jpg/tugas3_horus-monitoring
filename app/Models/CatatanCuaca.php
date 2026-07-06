<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CatatanCuaca (Weather Record)
 *
 * Menyimpan observasi cuaca historis dari provider eksternal.
 * Data diingestasi melalui pipeline: DTO → Validasi → Normalisasi → Persisten.
 *
 * @property int $id
 * @property int $negara_id
 * @property \Illuminate\Support\Carbon $tanggal_observasi
 * @property float|null $suhu
 * @property float|null $kelembaban
 * @property float|null $curah_hujan
 * @property float|null $kecepatan_angin
 * @property string|null $kondisi_cuaca
 * @property string|null $sumber_api
 */
class CatatanCuaca extends Model
{
    protected $table = 'catatan_cuaca';

    public $timestamps = false;

    protected $fillable = [
        'negara_id',
        'tanggal_observasi',
        'suhu',
        'kelembaban',
        'curah_hujan',
        'kecepatan_angin',
        'kondisi_cuaca',
        'sumber_api',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_observasi' => 'date',
            'suhu'              => 'float',
            'kelembaban'        => 'float',
            'curah_hujan'       => 'float',
            'kecepatan_angin'   => 'float',
            'dibuat_pada'       => 'datetime',
        ];
    }

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }
}
