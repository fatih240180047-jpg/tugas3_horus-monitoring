<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Negara
 *
 * Merepresentasikan profil master setiap negara yang dipantau.
 * Menjadi referensi sentral untuk seluruh modul intelijen.
 *
 * @property int $id
 * @property string $kode_iso
 * @property string $nama
 * @property string|null $ibu_kota
 * @property string|null $kawasan
 * @property string|null $sub_kawasan
 * @property float|null $lintang
 * @property float|null $bujur
 * @property int|null $populasi
 * @property bool $status_pemantauan
 */
class Negara extends Model
{
    protected $table = 'negara';

    protected $fillable = [
        'kode_iso',
        'nama',
        'ibu_kota',
        'kawasan',
        'sub_kawasan',
        'lintang',
        'bujur',
        'populasi',
        'status_pemantauan',
    ];

    protected function casts(): array
    {
        return [
            'lintang'            => 'float',
            'bujur'              => 'float',
            'populasi'           => 'integer',
            'status_pemantauan'  => 'boolean',
        ];
    }

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Negara memiliki banyak catatan cuaca.
     */
    public function catatanCuaca(): HasMany
    {
        return $this->hasMany(CatatanCuaca::class, 'negara_id');
    }

    /**
     * Negara memiliki banyak indikator ekonomi.
     */
    public function indikatorEkonomi(): HasMany
    {
        return $this->hasMany(IndikatorEkonomi::class, 'negara_id');
    }

    /**
     * Negara memiliki banyak nilai tukar.
     */
    public function nilaiTukar(): HasMany
    {
        return $this->hasMany(NilaiTukar::class, 'negara_id');
    }

    /**
     * Negara memiliki banyak artikel berita.
     */
    public function artikelBerita(): HasMany
    {
        return $this->hasMany(ArtikelBerita::class, 'negara_id');
    }

    /**
     * Negara memiliki banyak penilaian risiko.
     */
    public function penilaianRisiko(): HasMany
    {
        return $this->hasMany(PenilaianRisiko::class, 'negara_id');
    }

    /**
     * Negara memiliki banyak pelabuhan.
     */
    public function pelabuhans(): HasMany
    {
        return $this->hasMany(Pelabuhan::class, 'negara_id');
    }

    /**
     * Negara memiliki banyak lapisan SIG.
     */
    public function lapisanSig(): HasMany
    {
        return $this->hasMany(LapisanSig::class, 'negara_id');
    }

    // =========================================================
    // HELPER
    // =========================================================

    /**
     * Dapatkan penilaian risiko terkini untuk negara ini.
     */
    public function penilaianRisikoTerkini(): ?PenilaianRisiko
    {
        return $this->penilaianRisiko()
            ->orderByDesc('dihitung_pada')
            ->first();
    }

    /**
     * Dapatkan catatan cuaca terkini untuk negara ini.
     */
    public function cuacaTerkini(): ?CatatanCuaca
    {
        return $this->catatanCuaca()
            ->orderByDesc('tanggal_observasi')
            ->first();
    }

    /**
     * Dapatkan nilai tukar terkini untuk negara ini.
     */
    public function nilaiTukarTerkini(): ?NilaiTukar
    {
        return $this->nilaiTukar()
            ->orderByDesc('tanggal_berlaku')
            ->first();
    }

    /**
     * Accessor untuk bendera emoji berdasarkan kode ISO (Alpha-3 -> Alpha-2).
     */
    public function getBenderaAttribute(): string
    {
        $map = [
            'IDN' => '🇮🇩', 'USA' => '🇺🇸', 'CHN' => '🇨🇳', 'SGP' => '🇸🇬',
            'JPN' => '🇯🇵', 'DEU' => '🇩🇪', 'GBR' => '🇬🇧', 'IND' => '🇮🇳',
            'AUS' => '🇦🇺', 'BRA' => '🇧🇷', 'CAN' => '🇨🇦', 'FRA' => '🇫🇷',
            'NLD' => '🇳🇱', 'ARE' => '🇦🇪', 'SAU' => '🇸🇦', 'MYS' => '🇲🇾',
            'THA' => '🇹🇭', 'VNM' => '🇻🇳', 'PHL' => '🇵🇭', 'KOR' => '🇰🇷',
        ];

        return $map[$this->kode_iso] ?? '🏳️';
    }
}
