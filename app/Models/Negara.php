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
        'mata_uang',
        'bendera',
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

    /**
     * Negara difavoritkan oleh banyak pengguna.
     */
    public function difavoritkanOleh(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Pengguna::class, 'favorit_negara', 'negara_id', 'pengguna_id');
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
        return $this->attributes['bendera'] ?? 'id';
    }

    /**
     * Dapatkan URL bendera dari FlagCDN berdasarkan kode ISO 2 huruf.
     */
    public function getBenderaUrlAttribute(): string
    {
        $code = strtolower($this->bendera);
        // Fallback jika tidak ada bendera
        if (empty($code) || strlen($code) > 3) {
            $code = 'un'; // Unknown/United Nations flag
        }
        // Jika kodenya masih 3 huruf (karena seeder belum jalan), coba cari fallback
        if (strlen($code) === 3) {
            $map = [
                'idn' => 'id', 'sgp' => 'sg', 'mys' => 'my', 'tha' => 'th', 'vnm' => 'vn',
                'phl' => 'ph', 'chn' => 'cn', 'jpn' => 'jp', 'kor' => 'kr', 'ind' => 'in',
                'sau' => 'sa', 'are' => 'ae', 'deu' => 'de', 'nld' => 'nl', 'gbr' => 'gb',
                'fra' => 'fr', 'usa' => 'us', 'can' => 'ca', 'bra' => 'br', 'aus' => 'au',
                'zaf' => 'za', 'nga' => 'ng', 'rus' => 'ru', 'pak' => 'pk', 'bgd' => 'bd',
                'mex' => 'mx', 'tur' => 'tr', 'egy' => 'eg', 'arg' => 'ar', 'ukr' => 'ua'
            ];
            $code = $map[$code] ?? substr($code, 0, 2);
        }
        return "https://flagcdn.com/w40/{$code}.png";
    }
}
