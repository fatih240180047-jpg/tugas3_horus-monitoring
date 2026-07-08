<?php

namespace App\Providers;

use App\Services\Implementasi\LayananBerita;
use App\Services\Implementasi\LayananCuaca;
use App\Services\Implementasi\LayananEkonomi;
use App\Services\Implementasi\LayananNilaiTukar;
use App\Services\Kontrak\LayananBeritaInterface;
use App\Services\Kontrak\LayananCuacaInterface;
use App\Services\Kontrak\LayananEkonomiInterface;
use App\Services\Kontrak\LayananNilaiTukarInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider untuk Layanan Intelijen
 */
class LayananServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan ke dalam container.
     */
    public function register(): void
    {
        $this->app->bind(LayananCuacaInterface::class, LayananCuaca::class);
        $this->app->bind(LayananEkonomiInterface::class, LayananEkonomi::class);
        $this->app->bind(LayananNilaiTukarInterface::class, LayananNilaiTukar::class);
        $this->app->bind(LayananBeritaInterface::class, LayananBerita::class);
        $this->app->bind(
            \App\Services\Kontrak\MesinRisikoInterface::class,
            \App\Services\Implementasi\MesinRisiko::class
        );
    }

    /**
     * Bootstrapping layanan aplikasi.
     */
    public function boot(): void
    {
        //
    }
}
