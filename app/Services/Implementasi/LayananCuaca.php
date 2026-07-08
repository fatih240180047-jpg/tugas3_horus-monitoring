<?php

namespace App\Services\Implementasi;

use App\DTO\DtoCuaca;
use App\Models\CatatanCuaca;
use App\Models\Negara;
use App\Models\LogSinkronisasiApi;
use App\Repositories\Kontrak\RepositoriCuacaInterface;
use App\Services\Kontrak\LayananCuacaInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

/**
 * Implementasi Layanan Cuaca
 */
class LayananCuaca implements LayananCuacaInterface
{
    public function __construct(
        protected RepositoriCuacaInterface $repositoriCuaca
    ) {}

    /**
     * Sinkronisasikan data cuaca terkini untuk sebuah negara.
     */
    public function sinkronkan(Negara $negara): CatatanCuaca
    {
        $start = microtime(true);
        $config = config('intelijen.cuaca');
        $simulasi = config('intelijen.simulasi.aktif');

        // Gunakan mode simulasi jika kunci API kosong atau dinonaktifkan
        if (empty($config['kunci_api']) || !$config['aktif'] || $simulasi) {
            $dto = DtoCuaca::dariSimulasi($negara->kode_iso, date('Y-m-d'));
            $catatan = $this->repositoriCuaca->simpan($negara, $dto);

            LogSinkronisasiApi::create([
                'provider'          => 'OpenWeather (Simulasi)',
                'endpoint'          => 'mock_weather_data',
                'status'            => 'Berhasil',
                'jumlah_rekaman'    => 1,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => null,
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            return $catatan;
        }

        try {
            // Panggil API OpenWeather asli
            $response = Http::timeout($config['timeout'])
                ->get($config['url_dasar'] . '/weather', [
                    'lat'   => $negara->lintang,
                    'lon'   => $negara->bujur,
                    'appid' => $config['kunci_api'],
                    'units' => $config['satuan'],
                ]);

            if ($response->successful()) {
                $dto = DtoCuaca::dariResponOpenWeather($response->json(), $negara->kode_iso);
                $catatan = $this->repositoriCuaca->simpan($negara, $dto);

                LogSinkronisasiApi::create([
                    'provider'          => 'OpenWeather',
                    'endpoint'          => 'weather',
                    'status'            => 'Berhasil',
                    'jumlah_rekaman'    => 1,
                    'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                    'pesan_error'       => null,
                    'dieksekusi_pada'   => Carbon::now(),
                ]);

                return $catatan;
            }

            throw new Exception("HTTP request gagal dengan status: " . $response->status());
        } catch (Exception $e) {
            Log::error("Gagal melakukan sinkronisasi cuaca untuk negara {$negara->nama}: " . $e->getMessage());

            LogSinkronisasiApi::create([
                'provider'          => 'OpenWeather',
                'endpoint'          => 'weather',
                'status'            => 'Gagal',
                'jumlah_rekaman'    => 0,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => $e->getMessage(),
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            // Fallback ke data simulasi agar sistem tetap berjalan robust
            $dto = DtoCuaca::dariSimulasi($negara->kode_iso, date('Y-m-d'));
            return $this->repositoriCuaca->simpan($negara, $dto);
        }
    }
}
