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
 * Implementasi Layanan Nilai Tukar - Versi 2.0
 *
 * Menggunakan Open.er-api.com (FREE, tanpa API key) untuk nilai tukar live.
 * Setiap sinkronisasi menyimpan 1 record ke DB + menghasilkan insight SCM
 * berdasarkan perbandingan dengan data historis 30 hari.
 */
class LayananNilaiTukar implements LayananNilaiTukarInterface
{
    // Open Exchange Rates - Endpoint publik gratis (update per jam)
    // Alternatif: https://open.er-api.com/v6/latest/USD (100% gratis, tanpa key)
    private const ER_API_FREE_URL = 'https://open.er-api.com/v6/latest/USD';

    public function __construct(
        protected RepositoriNilaiTukarInterface $repositoriNilaiTukar
    ) {}

    /**
     * Sinkronisasikan nilai tukar mata uang terkini untuk sebuah negara.
     */
    public function sinkronkan(Negara $negara, string $kodeMataUang): NilaiTukar
    {
        $start = microtime(true);
        $config = config('intelijen.nilai_tukar');

        // Jika negara menggunakan USD, simpan flat 1.0
        if (strtoupper($kodeMataUang) === 'USD') {
            $dto = new DtoNilaiTukar($negara->kode_iso, 'USD', 1.0, date('Y-m-d'), 'Sistem');
            return $this->repositoriNilaiTukar->simpan($negara, $dto);
        }

        // Coba gunakan kunci API premium jika tersedia
        $kunciApi = $config['kunci_api'] ?? '';
        $urlApi = (!empty($kunciApi))
            ? "{$config['url_dasar']}/{$kunciApi}/latest/{$config['mata_uang_dasar']}"
            : self::ER_API_FREE_URL;

        try {
            $response = Http::timeout(30)->get($urlApi);

            if ($response->successful()) {
                $json  = $response->json();
                $rates = $json['conversion_rates'] ?? $json['rates'] ?? [];
                $rate  = $rates[strtoupper($kodeMataUang)] ?? null;

                if ($rate !== null) {
                    $nilaiTukarFloat = (float) $rate;

                    // Hitung insight SCM berdasarkan data historis
                    $insight = $this->analisaInsightScm($negara, $kodeMataUang, $nilaiTukarFloat);

                    $dto = new DtoNilaiTukar(
                        kodeIso:        $negara->kode_iso,
                        kodeMataUang:   strtoupper($kodeMataUang),
                        nilaiTukar:     $nilaiTukarFloat,
                        tanggalBerlaku: date('Y-m-d'),
                        sumberApi:      empty($kunciApi) ? 'open.er-api.com (Gratis)' : 'ExchangeRate API (Premium)',
                        insightScm:     $insight,
                    );

                    $catatan = $this->repositoriNilaiTukar->simpan($negara, $dto);

                    $this->catatLog(
                        empty($kunciApi) ? 'open.er-api.com' : 'ExchangeRate API',
                        'latest',
                        'Berhasil',
                        1,
                        $start
                    );

                    return $catatan;
                }

                throw new Exception("Kode mata uang {$kodeMataUang} tidak ada dalam respons API.");
            }

            throw new Exception("HTTP {$response->status()}: {$response->body()}");
        } catch (Exception $e) {
            Log::error("Gagal sinkronisasi nilai tukar {$kodeMataUang} untuk {$negara->nama}: " . $e->getMessage());
            $this->catatLog('ExchangeRate API', 'latest', 'Gagal', 0, $start, $e->getMessage());

            // Fallback ke data simulasi
            $dto = DtoNilaiTukar::dariSimulasi($negara->kode_iso, $kodeMataUang, date('Y-m-d'));
            return $this->repositoriNilaiTukar->simpan($negara, $dto);
        }
    }

    /**
     * Analisis insight SCM berdasarkan perbandingan nilai tukar vs historis 30 hari.
     */
    private function analisaInsightScm(Negara $negara, string $kodeMataUang, float $nilaiSaatIni): string
    {
        // Ambil data 30 hari terakhir dari DB
        $historis = NilaiTukar::where('negara_id', $negara->id)
            ->where('kode_mata_uang', strtoupper($kodeMataUang))
            ->where('tanggal_berlaku', '>=', date('Y-m-d', strtotime('-30 days')))
            ->orderBy('tanggal_berlaku', 'asc')
            ->pluck('nilai_tukar');

        if ($historis->isEmpty()) {
            return "📊 Data historis belum tersedia untuk analisis fluktuasi. Sinkronkan secara rutin untuk mendapatkan tren.";
        }

        $nilaiLama = $historis->first();
        $selisih   = $nilaiSaatIni - $nilaiLama;
        $persenPerubahan = $nilaiLama > 0 ? round(($selisih / $nilaiLama) * 100, 2) : 0;

        if (abs($persenPerubahan) < 0.5) {
            return "✅ Nilai tukar {$kodeMataUang}/USD stabil dalam 30 hari terakhir (perubahan: " . sprintf('%+.2f', $persenPerubahan) . "%). Tidak ada risiko valuta asing signifikan pada SCM.";
        }

        if ($persenPerubahan > 10) {
            return "🚨 Depresiasi signifikan {$kodeMataUang} sebesar " . sprintf('%+.2f', $persenPerubahan) . "% dalam 30 hari. Biaya impor melonjak drastis — pertimbangkan hedging valuta asing dan evaluasi kontrak pembelian bahan baku.";
        }

        if ($persenPerubahan > 3) {
            return "⚠️ Pelemahan {$kodeMataUang} sebesar " . sprintf('%+.2f', $persenPerubahan) . "% dalam 30 hari. Daya beli impor melemah — monitor ketat dan pertimbangkan re-negosiasi kontrak jika tren berlanjut.";
        }

        if ($persenPerubahan < -5) {
            return "📈 Apresiasi kuat {$kodeMataUang} sebesar " . sprintf('%+.2f', $persenPerubahan) . "% dalam 30 hari. Sangat menguntungkan untuk aktivitas ekspor dari negara ini — kondisi favorable untuk ekspansi pasar.";
        }

        if ($persenPerubahan < -1) {
            return "💹 Penguatan {$kodeMataUang} sebesar " . sprintf('%+.2f', $persenPerubahan) . "% dalam 30 hari. Harga impor lebih kompetitif — pertimbangkan percepatan pengadaan stok strategic.";
        }

        return "📊 Fluktuasi {$kodeMataUang} moderat (" . sprintf('%+.2f', $persenPerubahan) . "% dalam 30 hari). Pantau secara berkala untuk antisipasi risiko SCM.";
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
            'FRA' => 'EUR',
            'DEU' => 'EUR',
            'NLD' => 'EUR',
            'ITA' => 'EUR',
            'ESP' => 'EUR',
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
            'SAU' => 'SAR',
            'ARE' => 'AED',
            'AUS' => 'AUD',
            'CAN' => 'CAD',
            'CHE' => 'CHF',
            'VUT' => 'VUV',
            default => 'USD',
        };
    }

    /**
     * Catat log sinkronisasi API ke database.
     */
    private function catatLog(
        string $provider,
        string $endpoint,
        string $status,
        int $jumlah,
        float $start,
        ?string $pesan = null
    ): void {
        LogSinkronisasiApi::create([
            'provider'          => $provider,
            'endpoint'          => $endpoint,
            'status'            => $status,
            'jumlah_rekaman'    => $jumlah,
            'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
            'pesan_error'       => $pesan,
            'dieksekusi_pada'   => Carbon::now(),
        ]);
    }
}
