<?php

namespace App\Console\Commands;

use App\Models\Negara;
use App\Services\Kontrak\LayananBeritaInterface;
use App\Services\Kontrak\LayananCuacaInterface;
use App\Services\Kontrak\LayananEkonomiInterface;
use App\Services\Kontrak\LayananNilaiTukarInterface;
use App\Services\Kontrak\MesinRisikoInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command Artisan untuk Sinkronisasi Intelijen Massal.
 *
 * Berguna untuk dijadwalkan via cron (misal: jalankan tiap 6 jam)
 * agar data intelijen SCM selalu real-time tanpa perlu klik manual.
 *
 * Penggunaan:
 * - php artisan intelijen:sinkronkan --semua
 * - php artisan intelijen:sinkronkan --negara=IDN
 */
class SinkronisasiIntelijen extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'intelijen:sinkronkan 
                            {--semua : Sinkronisasikan semua negara} 
                            {--negara= : Sinkronisasikan satu negara spesifik berdasarkan kode ISO}';

    /**
     * Deskripsi console command.
     *
     * @var string
     */
    protected $description = 'Melakukan sinkronisasi data intelijen eksternal (cuaca, berita, ekonomi, forex) dan rekalkulasi risiko SCM.';

    /**
     * Execute the console command.
     */
    public function handle(
        LayananCuacaInterface $layananCuaca,
        LayananEkonomiInterface $layananEkonomi,
        LayananNilaiTukarInterface $layananNilaiTukar,
        LayananBeritaInterface $layananBerita,
        MesinRisikoInterface $mesinRisiko
    ) {
        $semua = $this->option('semua');
        $kodeIso = $this->option('negara');

        if (!$semua && !$kodeIso) {
            $this->error('Anda harus menyediakan opsi --semua atau --negara={KODE_ISO}.');
            return Command::FAILURE;
        }

        $query = Negara::query();
        if ($kodeIso) {
            $query->where('kode_iso', strtoupper($kodeIso));
        }

        $negaraList = $query->get();

        if ($negaraList->isEmpty()) {
            $this->warn('Tidak ada negara yang ditemukan untuk disinkronkan.');
            return Command::SUCCESS;
        }

        $this->info("Memulai sinkronisasi intelijen untuk {$negaraList->count()} negara...");
        $tahunIni = (int) date('Y');

        $bar = $this->output->createProgressBar($negaraList->count());
        $bar->start();

        foreach ($negaraList as $negara) {
            try {
                // 1. Cuaca 7 Hari (Open-Meteo)
                $layananCuaca->sinkronkan($negara);

                // 2. Ekonomi (World Bank)
                $layananEkonomi->sinkronkan($negara, $tahunIni);

                // 3. Forex Valas (ExchangeRate API / er-api)
                $mataUang = \App\Services\Implementasi\LayananNilaiTukar::dapatkanMataUangNegara($negara->kode_iso);
                $layananNilaiTukar->sinkronkan($negara, $mataUang);

                // 4. Berita (GNews / NewsAPI)
                $layananBerita->sinkronkan($negara);

                // 5. Rekalkulasi Risiko SCM
                $mesinRisiko->hitung($negara);

            } catch (\Exception $e) {
                Log::error("Gagal sinkronisasi negara {$negara->nama} via Artisan: " . $e->getMessage());
                $this->error("\nError pada {$negara->nama}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Sinkronisasi intelijen SCM berhasil diselesaikan!');

        return Command::SUCCESS;
    }
}
