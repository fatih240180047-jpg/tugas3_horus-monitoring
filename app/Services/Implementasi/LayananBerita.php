<?php

namespace App\Services\Implementasi;

use App\DTO\DtoBerita;
use App\Models\Negara;
use App\Models\LogSinkronisasiApi;
use App\Repositories\Kontrak\RepositoriBeritaInterface;
use App\Services\Kontrak\LayananBeritaInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

/**
 * Implementasi Layanan Berita - Versi 2.0 (GNews API)
 *
 * Menggunakan GNews API (Gratis 100 req/hari) untuk mengambil berita
 * internasional yang relevan per negara. Bila API key tidak tersedia,
 * sistem fallback ke mode simulasi yang tetap menyimpan data ke DB.
 *
 * Setiap artikel dianalisis secara otomatis untuk menentukan:
 * - Sentimen (positif/netral/negatif)
 * - Keparahan (rendah/sedang/tinggi/kritis)
 * - Dampak ke Rantai Pasok SCM (insight teks)
 */
class LayananBerita implements LayananBeritaInterface
{
    // GNews API endpoint - https://gnews.io/
    private const GNEWS_URL = 'https://gnews.io/api/v4/search';

    public function __construct(
        protected RepositoriBeritaInterface $repositoriBerita
    ) {}

    /**
     * Sinkronisasikan artikel berita terkini yang relevan untuk sebuah negara.
     */
    public function sinkronkan(Negara $negara): Collection
    {
        $start  = microtime(true);
        $config = config('intelijen.berita');
        $gnewsKey = config('intelijen.berita.gnews_kunci_api', '');
        $hasilKoleksi = new Collection();

        // Jika API key GNews tersedia, gunakan API
        if (!empty($gnewsKey)) {
            try {
                $hasil = $this->ambilDariGNews($negara, $gnewsKey, $start);
                if ($hasil->isNotEmpty()) {
                    return $hasil;
                }
            } catch (Exception $e) {
                Log::error("GNews gagal untuk {$negara->nama}: " . $e->getMessage());
            }
        }

        // Coba NewsAPI jika kunci tersedia
        $newsApiKey = $config['kunci_api'] ?? '';
        if (!empty($newsApiKey) && $config['aktif']) {
            try {
                $hasil = $this->ambilDariNewsApi($negara, $newsApiKey, $start);
                if ($hasil->isNotEmpty()) {
                    return $hasil;
                }
            } catch (Exception $e) {
                Log::error("NewsAPI gagal untuk {$negara->nama}: " . $e->getMessage());
            }
        }

        // Fallback: Data simulasi
        return $this->jalankanSimulasi($negara, $start);
    }

    /**
     * Ambil berita dari GNews API.
     */
    private function ambilDariGNews(Negara $negara, string $apiKey, float $start): Collection
    {
        $hasilKoleksi = new Collection();

        // Query cerdas: nama negara + kata kunci SCM dalam Bahasa Inggris
        $query = '"' . $negara->nama . '" OR "' . $negara->kode_iso . '" supply chain OR logistics OR trade OR shipping OR port OR sanctions OR economy';

        $response = Http::timeout(30)->get(self::GNEWS_URL, [
            'q'        => $query,
            'lang'     => 'en',
            'country'  => 'any',
            'max'      => 10,
            'apikey'   => $apiKey,
            'sortby'   => 'publishedAt',
        ]);

        if (!$response->successful()) {
            throw new Exception("GNews HTTP {$response->status()}: {$response->body()}");
        }

        $json     = $response->json();
        $artikels = $json['articles'] ?? [];
        $jumlah   = 0;

        foreach ($artikels as $art) {
            $dto     = DtoBerita::dariResponGNews($art, $negara->kode_iso);
            $artikel = $this->repositoriBerita->simpan($negara, $dto);
            if ($artikel) {
                $hasilKoleksi->push($artikel);
                $jumlah++;
            }
        }

        $this->catatLog('GNews API', 'search', 'Berhasil', $jumlah, $start);
        return $hasilKoleksi;
    }

    /**
     * Ambil berita dari NewsAPI (legacy support).
     */
    private function ambilDariNewsApi(Negara $negara, string $apiKey, float $start): Collection
    {
        $hasilKoleksi = new Collection();
        $config = config('intelijen.berita');

        $query = '"' . $negara->nama . '" AND (supply chain OR logistics OR shipping OR trade)';

        $response = Http::timeout($config['timeout'])
            ->withHeaders(['X-Api-Key' => $apiKey])
            ->get($config['url_dasar'] . '/everything', [
                'q'        => $query,
                'language' => $config['bahasa'],
                'sortBy'   => 'publishedAt',
                'pageSize' => 8,
            ]);

        if (!$response->successful()) {
            throw new Exception("NewsAPI HTTP {$response->status()}");
        }

        $json     = $response->json();
        $artikels = $json['articles'] ?? [];
        $jumlah   = 0;

        foreach ($artikels as $art) {
            $dto     = DtoBerita::dariResponNewsApi($art, $negara->kode_iso);
            $artikel = $this->repositoriBerita->simpan($negara, $dto);
            if ($artikel) {
                $hasilKoleksi->push($artikel);
                $jumlah++;
            }
        }

        $this->catatLog('NewsAPI', 'everything', 'Berhasil', $jumlah, $start);
        return $hasilKoleksi;
    }

    /**
     * Jalankan mode simulasi — simpan berita simulasi ke database.
     */
    private function jalankanSimulasi(Negara $negara, float $start): Collection
    {
        $hasilKoleksi = new Collection();
        $jumlah = 0;

        for ($i = 1; $i <= 5; $i++) {
            $dto     = DtoBerita::dariSimulasi($negara->kode_iso, $i);
            $artikel = $this->repositoriBerita->simpan($negara, $dto);
            if ($artikel) {
                $hasilKoleksi->push($artikel);
                $jumlah++;
            }
        }

        $this->catatLog('Simulasi', 'mock_news', 'Berhasil', $jumlah, $start);
        return $hasilKoleksi;
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
