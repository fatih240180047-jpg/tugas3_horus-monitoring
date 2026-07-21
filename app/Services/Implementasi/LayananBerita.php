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
        $simulasi = config('intelijen.simulasi.aktif');

        if ($simulasi) {
            Log::info("Mode simulasi aktif untuk berita {$negara->nama}");
            $hasilKoleksi = new Collection();
            for ($i = 1; $i <= 5; $i++) {
                $dto = DtoBerita::dariSimulasi($negara->kode_iso, $i);
                $artikel = $this->repositoriBerita->simpan($negara, $dto);
                if ($artikel) {
                    $hasilKoleksi->push($artikel);
                }
            }
            return $hasilKoleksi;
        }

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

        // Fallback: Ambil secara gratis dari Google News RSS (100% data riil & tanpa API key)
        try {
            return $this->ambilDariGoogleNewsRss($negara, $start);
        } catch (Exception $e) {
            Log::error("Google News RSS gagal untuk {$negara->nama}: " . $e->getMessage());
            return new Collection(); // Selalu kembalikan koleksi kosong jika gagal, bukan data fiktif
        }
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
     * Ambil berita riil secara gratis dari Google News RSS (tanpa API key).
     */
    private function ambilDariGoogleNewsRss(Negara $negara, float $start): Collection
    {
        $hasilKoleksi = new Collection();
        
        // Buat query pencarian yang relevan dengan SCM dan nama negara
        $query = urlencode('"' . $negara->nama . '" AND (supply chain OR logistics OR shipping OR trade OR economy)');
        $url = "https://news.google.com/rss/search?q={$query}&hl=en-US&gl=US&ceid=US:en";

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new Exception("Google News RSS HTTP {$response->status()}");
        }

        $xml = simplexml_load_string($response->body());
        if ($xml === false) {
            throw new Exception("Gagal melakukan parsing XML RSS Google News");
        }

        $items = $xml->channel->item ?? [];
        $jumlah = 0;

        foreach ($items as $item) {
            if ($jumlah >= 5) { // Ambil 5 berita teratas
                break;
            }
            $dto = DtoBerita::dariItemRss($item, $negara->kode_iso);
            $artikel = $this->repositoriBerita->simpan($negara, $dto);
            if ($artikel) {
                $hasilKoleksi->push($artikel);
                $jumlah++;
            }
        }

        $this->catatLog('Google News RSS', 'rss/search', 'Berhasil', $jumlah, $start);
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
