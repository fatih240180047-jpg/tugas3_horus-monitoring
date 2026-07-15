<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Pelabuhan — Node Kritis dalam Rantai Pasok Global.
 */
class Pelabuhan extends Model
{
    protected $table = 'pelabuhans';

    protected $fillable = [
        'negara_id', 'nama', 'kode_locode', 'lintang', 'bujur',
        'jenis', 'kapasitas_teu', 'tingkat_kepadatan', 'skor_risiko',
        'operator', 'aktif',
    ];

    protected $casts = [
        'lintang'          => 'float',
        'bujur'            => 'float',
        'kapasitas_teu'    => 'integer',
        'tingkat_kepadatan'=> 'integer',
        'skor_risiko'      => 'integer',
        'aktif'            => 'boolean',
    ];

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class);
    }

    /**
     * Hitung label warna berdasarkan tingkat kepadatan.
     */
    public function labelKepadatan(): string
    {
        return match (true) {
            $this->tingkat_kepadatan >= 80 => 'Kritis',
            $this->tingkat_kepadatan >= 60 => 'Tinggi',
            $this->tingkat_kepadatan >= 40 => 'Sedang',
            default                        => 'Normal',
        };
    }

    /**
     * Kode warna hex untuk peta Leaflet.
     */
    public function warnaMarker(): string
    {
        return match (true) {
            $this->tingkat_kepadatan >= 80 => '#dc2626',
            $this->tingkat_kepadatan >= 60 => '#f97316',
            $this->tingkat_kepadatan >= 40 => '#eab308',
            default                        => '#16a34a',
        };
    }
}
