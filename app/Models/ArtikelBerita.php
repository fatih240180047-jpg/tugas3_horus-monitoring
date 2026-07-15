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
        'url_sumber',
        'url_asli',
        'diterbitkan_pada',
        'dibuat_pada',
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

    public function analisis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnalisisArtikel::class, 'artikel_berita_id');
    }

    public function analisisTersetujui(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnalisisArtikel::class, 'artikel_berita_id')
            ->where('status', 'disetujui');
    }
}
