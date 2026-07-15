<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ArtikelBerita (News Article)
 *
 * Menyimpan berita internasional relevan dari NewsAPI.
 * Setiap artikel diproses melalui analisis sentimen dan klasifikasi keparahan.
 */
class ArtikelBerita extends Model
{
    protected $table = 'artikel_berita';

    public $timestamps = false;

    protected $fillable = [
        'negara_id',
        'judul',
        'ringkasan',
        'kategori',
        'sentimen',
        'keparahan',
        'sumber',
        'diterbitkan_pada',
        'dibuat_pada',
        'url_asli',
        'dampak_scm',
    ];

    protected function casts(): array
    {
        return [
            'diterbitkan_pada' => 'datetime',
            'dibuat_pada'      => 'datetime',
        ];
    }

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }
}
