<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Model LogSinkronisasiApi (API Sync Log) */
class LogSinkronisasiApi extends Model
{
    protected $table = 'log_sinkronisasi_api';
    public $timestamps = false;

    protected $fillable = [
        'provider', 'endpoint', 'status', 'jumlah_rekaman',
        'waktu_eksekusi_ms', 'pesan_error', 'dieksekusi_pada',
    ];

    protected function casts(): array
    {
        return ['dieksekusi_pada' => 'datetime'];
    }
}
