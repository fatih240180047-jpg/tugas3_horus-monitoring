<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Model EksporLaporan (Report Export) */
class EksporLaporan extends Model
{
    protected $table = 'ekspor_laporan';
    public $timestamps = false;

    protected $fillable = [
        'laporan_id', 'format_ekspor', 'diekspor_oleh', 'diekspor_pada',
    ];

    protected function casts(): array
    {
        return ['diekspor_pada' => 'datetime'];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function pengekspor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diekspor_oleh');
    }
}
