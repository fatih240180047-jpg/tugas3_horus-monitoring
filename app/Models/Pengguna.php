<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model Pengguna
 *
 * Merepresentasikan entitas pengguna yang terautentikasi pada platform.
 * Mendukung RBAC melalui relasi ke tabel peran dan izin.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $avatar
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nama tabel database.
     */
    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'status'            => 'boolean',
        ];
    }

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Pengguna memiliki banyak peran (many-to-many).
     */
    public function peran(): BelongsToMany
    {
        return $this->belongsToMany(
            Peran::class,
            'peran_pengguna',
            'pengguna_id',
            'peran_id'
        )->withPivot('ditetapkan_pada');
    }

    /**
     * Pengguna memiliki banyak log aktivitas (one-to-many).
     */
    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'pengguna_id');
    }

    /**
     * Pengguna memiliki banyak laporan (one-to-many).
     */
    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'dibuat_oleh');
    }

    // =========================================================
    // HELPER PERAN
    // =========================================================

    /**
     * Periksa apakah pengguna memiliki peran tertentu berdasarkan slug.
     */
    public function mempunyaiPeran(string $slugPeran): bool
    {
        return $this->peran()->where('slug', $slugPeran)->exists();
    }

    /**
     * Periksa apakah pengguna adalah Super Administrator.
     */
    public function adalahSuperAdmin(): bool
    {
        return $this->mempunyaiPeran('super-administrator');
    }

    /**
     * Periksa apakah pengguna adalah Administrator.
     */
    public function adalahAdmin(): bool
    {
        return $this->mempunyaiPeran('administrator') || $this->adalahSuperAdmin();
    }

    /**
     * Periksa apakah pengguna adalah Eksekutif.
     */
    public function adalahEksekutif(): bool
    {
        return $this->mempunyaiPeran('eksekutif');
    }

    /**
     * Periksa apakah pengguna adalah Manajer Pengadaan.
     */
    public function adalahManajerPengadaan(): bool
    {
        return $this->mempunyaiPeran('manajer-pengadaan');
    }

    /**
     * Periksa apakah pengguna adalah Analis Risiko.
     */
    public function adalahAnalisRisiko(): bool
    {
        return $this->mempunyaiPeran('analis-risiko');
    }

    /**
     * Dapatkan peran utama pengguna sebagai string.
     */
    public function peranUtama(): string
    {
        $peran = $this->peran()->first();
        return $peran ? $peran->name : 'Pengunjung';
    }
}
