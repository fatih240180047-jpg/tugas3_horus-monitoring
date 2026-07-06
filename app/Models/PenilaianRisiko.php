<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PenilaianRisiko (Risk Assessment)
 *
 * Menyimpan setiap hasil kalkulasi risiko yang dihasilkan Mesin Risiko.
 * Setiap penilaian adalah snapshot imutable pada titik waktu tertentu.
 *
 * @property float $skor_total  Skor risiko akhir 0–100
 * @property string $level_risiko  Rendah | Sedang | Tinggi | Kritis
 * @property array|null $penjelasan  Alasan-alasan penyusun skor
 */
class PenilaianRisiko extends Model
{
    protected $table = 'penilaian_risiko';

    public $timestamps = false;

    protected $fillable = [
        'negara_id',
        'skor_cuaca',
        'skor_ekonomi',
        'skor_nilai_tukar',
        'skor_berita',
        'skor_logistik',
        'skor_politik',
        'skor_total',
        'level_risiko',
        'penjelasan',
        'dihitung_pada',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'skor_cuaca'       => 'float',
            'skor_ekonomi'     => 'float',
            'skor_nilai_tukar' => 'float',
            'skor_berita'      => 'float',
            'skor_logistik'    => 'float',
            'skor_politik'     => 'float',
            'skor_total'       => 'float',
            'penjelasan'       => 'array',
            'dihitung_pada'    => 'datetime',
            'dibuat_pada'      => 'datetime',
        ];
    }

    public function negara(): BelongsTo
    {
        return $this->belongsTo(Negara::class, 'negara_id');
    }

    public function rekomendasi(): HasMany
    {
        return $this->hasMany(RekomendasiRisiko::class, 'penilaian_risiko_id');
    }

    /**
     * Dapatkan warna hex level risiko untuk visualisasi SIG/Dasbor.
     */
    public function warnaLevelRisiko(): string
    {
        return match ($this->level_risiko) {
            'Rendah'  => '#16a34a', // Hijau
            'Sedang'  => '#d97706', // Jingga
            'Tinggi'  => '#dc2626', // Merah
            'Kritis'  => '#7f1d1d', // Merah Tua
            default   => '#6b7280', // Abu-abu
        };
    }
}
