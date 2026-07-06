<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Model CacheDasbor (Dashboard Cache) - Data KPI ter-cache untuk performa */
class CacheDasbor extends Model
{
    protected $table = 'cache_dasbor';
    public $timestamps = false;

    protected $fillable = [
        'widget', 'kunci_cache', 'muatan', 'kedaluwarsa_pada', 'diperbarui_pada',
    ];

    protected function casts(): array
    {
        return [
            'muatan'           => 'array',
            'kedaluwarsa_pada' => 'datetime',
            'diperbarui_pada'  => 'datetime',
        ];
    }

    /**
     * Periksa apakah cache sudah kedaluwarsa.
     */
    public function sudahKedaluwarsa(): bool
    {
        return $this->kedaluwarsa_pada->isPast();
    }
}
