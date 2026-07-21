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

        if (config('intelijen.simulasi.aktif')) {
            Log::info("Mode simulasi aktif untuk cuaca {$negara->nama}");
            return $this->ambilCuacaTerakhirDariDb($negara);
        }

        // Validasi koordinat negara
        if (!$negara->lintang || !$negara->bujur) {
            Log::warning("Koordinat tidak tersedia untuk {$negara->nama}. Mencari data riil terakhir dari database.");
            return $this->ambilCuacaTerakhirDariDb($negara);
        }

        try {
            $response = Http::timeout(10)->get(self::OPEN_METEO_URL, [
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

                return $catatanHariIni ?? $this->ambilCuacaTerakhirDariDb($negara);
            }

            throw new Exception("HTTP {$response->status()}: {$response->body()}");
        } catch (Exception $e) {
            Log::error("Gagal sinkronisasi cuaca Open-Meteo untuk {$negara->nama}: " . $e->getMessage());
            $this->catatLog('Open-Meteo', 'forecast/7days', 'Gagal', 0, $start, $e->getMessage());

            // Fallback ke data riil terakhir di DB
            return $this->ambilCuacaTerakhirDariDb($negara);
        }
    }

    /**
     * Cari data cuaca riil terakhir dari database atau hasilkan data simulasi jika kosong.
     */
    private function ambilCuacaTerakhirDariDb(Negara $negara): CatatanCuaca
    {
        $latest = CatatanCuaca::where('negara_id', $negara->id)
            ->orderBy('tanggal_observasi', 'desc')
            ->first();

        if ($latest) {
            return $latest;
        }

        Log::info("Menghasilkan data cuaca tiruan untuk {$negara->nama}");
        $catatanHariIni = null;
        for ($i = 0; $i < 7; $i++) {
            $tanggal = date('Y-m-d', strtotime("+$i days"));
            $suhuMin = (float) rand(10, 20);
            $suhuMax = (float) rand(22, 35);
            $suhu = ($suhuMin + $suhuMax) / 2;
            $kelembaban = (float) rand(50, 90);
            $curahHujan = (float) (rand(0, 5) === 0 ? rand(10, 60) : rand(0, 8));
            $kecepatanAngin = (float) rand(5, 45);
            
            $wmoCodes = [0, 1, 2, 3, 51, 61, 80, 95];
            $wmoCode = $wmoCodes[array_rand($wmoCodes)];
            $kondisi = DtoCuaca::terjemahkanKodeWmo($wmoCode);
            
            $insight = DtoCuaca::analisaInsightScm($curahHujan, $kecepatanAngin, $suhuMax, $suhuMin, $kondisi);

            $dto = new DtoCuaca(
                kodeIso: $negara->kode_iso,
                tanggalObservasi: $tanggal,
                suhu: $suhu,
                suhuMin: $suhuMin,
                suhuMax: $suhuMax,
                kelembaban: $kelembaban,
                curahHujan: $curahHujan,
                kecepatanAngin: $kecepatanAngin,
                kondisiCuaca: $kondisi,
                insightScm: $insight,
                sumberApi: 'Open-Meteo (Simulated Fallback)'
            );

            $catatan = $this->repositoriCuaca->simpan($negara, $dto);
            if ($i === 0) {
                $catatanHariIni = $catatan;
            }
        }

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
