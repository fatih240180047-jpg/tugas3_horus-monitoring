<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Model Laporan (Report) */
class Laporan extends Model
{
    protected $table = 'laporan';
    public $timestamps = false;

    protected $fillable = [
        'judul', 'jenis_laporan', 'dibuat_oleh', 'path_file', 'dihasilkan_pada', 'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'dihasilkan_pada' => 'datetime',
            'dibuat_pada'     => 'datetime',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    public function ekspor(): HasMany
    {
        return $this->hasMany(EksporLaporan::class, 'laporan_id');
    }
}
