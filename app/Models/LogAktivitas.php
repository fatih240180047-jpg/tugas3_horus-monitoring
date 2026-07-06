<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Model LogAktivitas (Activity Log) - Immutable audit trail */
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    public $timestamps = false;

    protected $fillable = [
        'pengguna_id', 'modul', 'aksi', 'entitas', 'entitas_id',
        'nilai_lama', 'nilai_baru', 'alamat_ip', 'user_agent', 'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'nilai_lama'  => 'array',
            'nilai_baru'  => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}
