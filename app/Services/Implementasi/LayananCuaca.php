<?php

namespace App\Services\Implementasi;

use App\DTO\DtoCuaca;
use App\Models\CatatanCuaca;
use App\Models\Negara;
use App\Models\LogSinkronisasiApi;
use App\Repositories\Kontrak\RepositoriCuacaInterface;
use App\Services\Kontrak\LayananCuacaInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

/**
 * Implementasi Layanan Cuaca - Versi 2.0
 *
 * Menggunakan Open-Meteo API (Gratis, tanpa API key) untuk mendapatkan
 * prakiraan cuaca 7 hari ke depan per negara berdasarkan koordinat.
 *
 * Setiap panggilan API menghasilkan 7 record catatan harian di database.
 * Sistem akan fallback ke data simulasi jika API tidak tersedia.
 */
class LayananCuaca implements LayananCuacaInterface
{
    // URL Open-Meteo Forecast API — bebas digunakan tanpa API key
    private const OPEN_METEO_URL = 'https://api.open-meteo.com/v1/forecast';

    // Parameter harian yang diminta dari API
    private const PARAMETER_HARIAN = [
        'temperature_2m_max',
        'temperature_2m_min',
        'precipitation_sum',
        'windspeed_10m_max',
        'weathercode',
        'precipitation_hours',
    ];

    public function __construct(
        protected RepositoriCuacaInterface $repositoriCuaca
    ) {}

    /**
     * Sinkronisasikan prakiraan cuaca 7 hari untuk sebuah negara.
     * Mengembalikan catatan cuaca hari ini.
     */
    public function sinkronkan(Negara $negara): CatatanCuaca
    {
        $start = microtime(true);

        // Validasi koordinat negara
        if (!$negara->lintang || !$negara->bujur) {
            Log::warning("Koordinat tidak tersedia untuk {$negara->nama}, menggunakan data simulasi.");
            return $this->jalankanSimulasi($negara, $start);
        }

        try {
            $response = Http::timeout(30)->get(self::OPEN_METEO_URL, [
                'latitude'        => $negara->lintang,
                'longitude'       => $negara->bujur,
                'daily'           => implode(',', self::PARAMETER_HARIAN),
                'forecast_days'   => 7,
                'timezone'        => 'auto', // Sesuaikan zona waktu otomatis
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $daily = $json['daily'] ?? [];
                $jumlahRecord = 0;
                $catatanHariIni = null;

                // Simpan 7 hari ke database
                for ($i = 0; $i < 7; $i++) {
                    if (!isset($daily['time'][$i])) {
                        continue;
                    }

                    $dto = DtoCuaca::dariResponOpenMeteo($daily, $i, $negara->kode_iso);
                    $catatan = $this->repositoriCuaca->simpan($negara, $dto);

                    if ($catatan) {
                        $jumlahRecord++;
                        if ($i === 0) {
                            $catatanHariIni = $catatan; // Record hari ini
                        }
                    }
                }

                $this->catatLog('Open-Meteo', 'forecast/7days', 'Berhasil', $jumlahRecord, $start);

                return $catatanHariIni ?? $this->jalankanSimulasi($negara, $start);
            }

            throw new Exception("HTTP {$response->status()}: {$response->body()}");
        } catch (Exception $e) {
            Log::error("Gagal sinkronisasi cuaca Open-Meteo untuk {$negara->nama}: " . $e->getMessage());
            $this->catatLog('Open-Meteo', 'forecast/7days', 'Gagal', 0, $start, $e->getMessage());

            // Fallback ke simulasi yang mencakup 7 hari juga
            return $this->jalankanSimulasi($negara, $start);
        }
    }

    /**
     * Jalankan mode simulasi — simpan 7 hari data simulasi ke database.
     */
    private function jalankanSimulasi(Negara $negara, float $start): CatatanCuaca
    {
        $catatanHariIni = null;

        for ($i = 0; $i < 7; $i++) {
            $tanggal = date('Y-m-d', strtotime("+{$i} days"));
            $dto = DtoCuaca::dariSimulasi($negara->kode_iso, $tanggal);
            $catatan = $this->repositoriCuaca->simpan($negara, $dto);

            if ($i === 0) {
                $catatanHariIni = $catatan;
            }
        }

        $this->catatLog('Open-Meteo (Simulasi)', 'mock_forecast', 'Berhasil', 7, $start);

        return $catatanHariIni;
    }

    /**
     * Catat log sinkronisasi API ke database.
     */
    private function catatLog(
        string $provider,
        string $endpoint,
        string $status,
        int $jumlahRekaman,
        float $start,
        ?string $pesan = null
    ): void {
        LogSinkronisasiApi::create([
            'provider'          => $provider,
            'endpoint'          => $endpoint,
            'status'            => $status,
            'jumlah_rekaman'    => $jumlahRekaman,
            'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
            'pesan_error'       => $pesan,
            'dieksekusi_pada'   => Carbon::now(),
        ]);
    }
}
