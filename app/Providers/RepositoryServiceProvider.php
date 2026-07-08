<?php

namespace App\Providers;

use App\Repositories\Eloquent\RepositoriBerita;
use App\Repositories\Eloquent\RepositoriCuaca;
use App\Repositories\Eloquent\RepositoriEkonomi;
use App\Repositories\Eloquent\RepositoriNegara;
use App\Repositories\Eloquent\RepositoriNilaiTukar;
use App\Repositories\Eloquent\RepositoriRisiko;
use App\Repositories\Kontrak\RepositoriBeritaInterface;
use App\Repositories\Kontrak\RepositoriCuacaInterface;
use App\Repositories\Kontrak\RepositoriEkonomiInterface;
use App\Repositories\Kontrak\RepositoriNegaraInterface;
use App\Repositories\Kontrak\RepositoriNilaiTukarInterface;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider untuk Repositori
 *
 * Mendaftarkan semua implementasi repositori Eloquent ke kontrak interface masing-masing.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan ke dalam container.
     */
    public function register(): void
    {
        $this->app->bind(RepositoriNegaraInterface::class, RepositoriNegara::class);
        $this->app->bind(RepositoriCuacaInterface::class, RepositoriCuaca::class);
        $this->app->bind(RepositoriEkonomiInterface::class, RepositoriEkonomi::class);
        $this->app->bind(RepositoriNilaiTukarInterface::class, RepositoriNilaiTukar::class);
        $this->app->bind(RepositoriBeritaInterface::class, RepositoriBerita::class);
        $this->app->bind(RepositoriRisikoInterface::class, RepositoriRisiko::class);
    }

    /**
     * Bootstrapping layanan aplikasi.
     */
    public function boot(): void
    {
        //
    }
}
