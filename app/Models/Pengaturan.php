<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Model Pengaturan (Settings) - Konfigurasi aplikasi dinamis */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'kategori', 'kunci', 'nilai', 'tipe_data', 'deskripsi', 'diperbarui_oleh',
    ];

    public function diperbarui(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diperbarui_oleh');
    }

    /**
     * Ambil nilai pengaturan dengan konversi tipe data otomatis.
     */
    public function nilaiDikonversi(): mixed
    {
        return match ($this->tipe_data) {
            'integer' => (int) $this->nilai,
            'boolean' => filter_var($this->nilai, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->nilai, true),
            'float'   => (float) $this->nilai,
            default   => $this->nilai,
        };
    }
}
