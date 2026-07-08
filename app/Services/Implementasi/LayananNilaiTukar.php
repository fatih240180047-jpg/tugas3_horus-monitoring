<?php

namespace App\Services\Implementasi;

use App\DTO\DtoNilaiTukar;
use App\Models\Negara;
use App\Models\NilaiTukar;
use App\Models\LogSinkronisasiApi;
use App\Repositories\Kontrak\RepositoriNilaiTukarInterface;
use App\Services\Kontrak\LayananNilaiTukarInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

/**
 * Implementasi Layanan Nilai Tukar
 */
class LayananNilaiTukar implements LayananNilaiTukarInterface
{
    public function __construct(
        protected RepositoriNilaiTukarInterface $repositoriNilaiTukar
    ) {}

    /**
     * Sinkronisasikan nilai tukar mata uang terkini untuk sebuah negara terhadap USD.
     */
    public function sinkronkan(Negara $negara, string $kodeMataUang): NilaiTukar
    {
        $start = microtime(true);
        $config = config('intelijen.nilai_tukar');
        $simulasi = config('intelijen.simulasi.aktif');

        // Jika negara tujuan menggunakan USD (USA), simpan flat 1.0
        if (strtoupper($kodeMataUang) === 'USD') {
            $dto = new DtoNilaiTukar($negara->kode_iso, 'USD', 1.0, date('Y-m-d'), 'Sistem');
            return $this->repositoriNilaiTukar->simpan($negara, $dto);
        }

        // Gunakan mode simulasi jika kunci API kosong atau dinonaktifkan
        if (empty($config['kunci_api']) || !$config['aktif'] || $simulasi) {
            $dto = DtoNilaiTukar::dariSimulasi($negara->kode_iso, $kodeMataUang, date('Y-m-d'));
            $catatan = $this->repositoriNilaiTukar->simpan($negara, $dto);

            LogSinkronisasiApi::create([
                'provider'          => 'ExchangeRate (Simulasi)',
                'endpoint'          => 'mock_exchange_rate',
                'status'            => 'Berhasil',
                'jumlah_rekaman'    => 1,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => null,
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            return $catatan;
        }

        try {
            // Panggil API ExchangeRate-API
            // Endpoint format: /v6/{api_key}/latest/USD
            $url = "{$config['url_dasar']}/{$config['kunci_api']}/latest/{$config['mata_uang_dasar']}";
            $response = Http::timeout($config['timeout'])->get($url);

            if ($response->successful()) {
                $json = $response->json();
                $rates = $json['conversion_rates'] ?? [];
                $rate = $rates[strtoupper($kodeMataUang)] ?? null;

                if ($rate !== null) {
                    $dto = new DtoNilaiTukar(
                        kodeIso:        $negara->kode_iso,
                        kodeMataUang:   strtoupper($kodeMataUang),
                        nilaiTukar:     (float) $rate,
                        tanggalBerlaku: date('Y-m-d'),
                        sumberApi:      'ExchangeRate API'
                    );

                    $catatan = $this->repositoriNilaiTukar->simpan($negara, $dto);

                    LogSinkronisasiApi::create([
                        'provider'          => 'ExchangeRate API',
                        'endpoint'          => 'latest',
                        'status'            => 'Berhasil',
                        'jumlah_rekaman'    => 1,
                        'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                        'pesan_error'       => null,
                        'dieksekusi_pada'   => Carbon::now(),
                    ]);

                    return $catatan;
                }

                throw new Exception("Mata uang {$kodeMataUang} tidak ditemukan dalam respons API.");
            }

            throw new Exception("HTTP request gagal dengan status: " . $response->status());
        } catch (Exception $e) {
            Log::error("Gagal melakukan sinkronisasi nilai tukar untuk {$negara->nama} ({$kodeMataUang}): " . $e->getMessage());

            LogSinkronisasiApi::create([
                'provider'          => 'ExchangeRate API',
                'endpoint'          => 'latest',
                'status'            => 'Gagal',
                'jumlah_rekaman'    => 0,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => $e->getMessage(),
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            // Fallback ke data simulasi
            $dto = DtoNilaiTukar::dariSimulasi($negara->kode_iso, $kodeMataUang, date('Y-m-d'));
            return $this->repositoriNilaiTukar->simpan($negara, $dto);
        }
    }

    /**
     * Helper untuk memetakan kode negara ISO-3 ke kode mata uang ISO-4217.
     */
    public static function dapatkanMataUangNegara(string $kodeIso): string
    {
        return match (strtoupper($kodeIso)) {
            'IDN' => 'IDR',
            'SGP' => 'SGD',
            'MYS' => 'MYR',
            'THA' => 'THB',
            'VNM' => 'VND',
            'PHL' => 'PHP',
            'CHN' => 'CNY',
            'JPN' => 'JPY',
            'KOR' => 'KRW',
            'IND' => 'INR',
            'GBR' => 'GBP',
            'FRA', 'DEU', 'NLD' => 'EUR',
            'USA' => 'USD',
            'BRA' => 'BRL',
            'ZAF' => 'ZAR',
            'NGA' => 'NGN',
            'RUS' => 'RUB',
            'PAK' => 'PKR',
            'BGD' => 'BDT',
            'MEX' => 'MXN',
            'TUR' => 'TRY',
            'EGY' => 'EGP',
            'ARG' => 'ARS',
            'UKR' => 'UAH',
            default => 'USD',
        };
    }
}
