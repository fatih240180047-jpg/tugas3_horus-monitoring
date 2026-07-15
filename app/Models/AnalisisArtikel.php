<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model AnalisisArtikel
 *
 * Menyimpan anotasi intelijen dari Analis Risiko & Manajer Pengadaan
 * terhadap artikel berita yang telah diklasifikasikan sistem AI.
 *
 * @property int    $id
 * @property int    $artikel_berita_id
 * @property int    $pengguna_id
 * @property string $komentar_analis
 * @property string $rekomendasi_tindakan
 * @property string $tingkat_kepercayaan   (rendah|sedang|tinggi)
 * @property string $dampak_scm            (tidak_berdampak|minor|signifikan|kritis)
 * @property string $status               (draft|menunggu_review|disetujui|ditolak)
 * @property int|null $disetujui_oleh
 * @property \Carbon\Carbon|null $disetujui_pada
 * @property string|null $catatan_reviewer
 */
class AnalisisArtikel extends Model
{
    protected $table = 'analisis_artikel';

    protected $fillable = [
        'artikel_berita_id',
        'pengguna_id',
        'komentar_analis',
        'rekomendasi_tindakan',
        'tingkat_kepercayaan',
        'dampak_scm',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'catatan_reviewer',
    ];

    protected function casts(): array
    {
        return [
            'disetujui_pada' => 'datetime',
        ];
    }

    // =========================================================
    // RELASI
    // =========================================================

    public function artikel(): BelongsTo
    {
        return $this->belongsTo(ArtikelBerita::class, 'artikel_berita_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'disetujui_oleh');
    }

    // =========================================================
    // HELPER
    // =========================================================

    public function labelStatus(): string
    {
        return match ($this->status) {
            'draft'             => 'Draft',
            'menunggu_review'   => 'Menunggu Review',
            'disetujui'         => 'Disetujui',
            'ditolak'           => 'Ditolak',
            default             => ucfirst($this->status),
        };
    }

    public function warnaBadgeStatus(): string
    {
        return match ($this->status) {
            'draft'             => 'badge-sedang',
            'menunggu_review'   => 'badge-tinggi',
            'disetujui'         => 'badge-rendah',
            'ditolak'           => 'badge-kritis',
            default             => '',
        };
    }

    public function labelDampakScm(): string
    {
        return match ($this->dampak_scm) {
            'tidak_berdampak'   => 'Tidak Berdampak',
            'minor'             => 'Minor',
            'signifikan'        => 'Signifikan',
            'kritis'            => 'Kritis',
            default             => ucfirst($this->dampak_scm),
        };
    }
}
