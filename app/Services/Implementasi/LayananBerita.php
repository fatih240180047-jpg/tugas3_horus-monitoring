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
 * Implementasi Layanan Berita
 */
class LayananBerita implements LayananBeritaInterface
{
    public function __construct(
        protected RepositoriBeritaInterface $repositoriBerita
    ) {}

    /**
     * Sinkronisasikan artikel berita terkini yang relevan untuk sebuah negara.
     *
     * @return Collection
     */
    public function sinkronkan(Negara $negara): Collection
    {
        $start = microtime(true);
        $config = config('intelijen.berita');
        $simulasi = config('intelijen.simulasi.aktif');
        $hasilKoleksi = new Collection();

        // Gunakan mode simulasi jika kunci API kosong atau dinonaktifkan
        if (empty($config['kunci_api']) || !$config['aktif'] || $simulasi) {
            $jumlahDisimpan = 0;
            // Buat 3 berita simulasi untuk negara ini
            for ($i = 1; $i <= 3; $i++) {
                $dto = DtoBerita::dariSimulasi($negara->kode_iso, $i);
                $artikel = $this->repositoriBerita->simpan($negara, $dto);
                if ($artikel) {
                    $hasilKoleksi->push($artikel);
                    $jumlahDisimpan++;
                }
            }

            LogSinkronisasiApi::create([
                'provider'          => 'NewsAPI (Simulasi)',
                'endpoint'          => 'mock_news_data',
                'status'            => 'Berhasil',
                'jumlah_rekaman'    => $jumlahDisimpan,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => null,
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            return $hasilKoleksi;
        }

        try {
            // Gabungkan query pencarian: nama negara + kata kunci supply chain
            $query = '"' . $negara->nama . '" AND (supply chain OR logistics OR shipping OR trade OR "nilai tukar" OR inflasi)';

            $response = Http::timeout($config['timeout'])
                ->withHeaders(['X-Api-Key' => $config['kunci_api']])
                ->get($config['url_dasar'] . '/everything', [
                    'q'        => $query,
                    'language' => $config['bahasa'],
                    'sortBy'   => 'publishedAt',
                    'pageSize' => 5, // Cukup 5 artikel teratas per negara
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $artikels = $json['articles'] ?? [];
                $jumlahDisimpan = 0;

                foreach ($artikels as $art) {
                    $dto = DtoBerita::dariResponNewsApi($art, $negara->kode_iso);
                    $artikel = $this->repositoriBerita->simpan($negara, $dto);

                    if ($artikel) {
                        $hasilKoleksi->push($artikel);
                        $jumlahDisimpan++;
                    }
                }

                LogSinkronisasiApi::create([
                    'provider'          => 'NewsAPI',
                    'endpoint'          => 'everything',
                    'status'            => 'Berhasil',
                    'jumlah_rekaman'    => $jumlahDisimpan,
                    'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                    'pesan_error'       => null,
                    'dieksekusi_pada'   => Carbon::now(),
                ]);

                return $hasilKoleksi;
            }

            throw new Exception("HTTP request gagal dengan status: " . $response->status());
        } catch (Exception $e) {
            Log::error("Gagal melakukan sinkronisasi berita untuk negara {$negara->nama}: " . $e->getMessage());

            LogSinkronisasiApi::create([
                'provider'          => 'NewsAPI',
                'endpoint'          => 'everything',
                'status'            => 'Gagal',
                'jumlah_rekaman'    => 0,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => $e->getMessage(),
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            // Fallback ke data simulasi
            $jumlahDisimpan = 0;
            for ($i = 1; $i <= 3; $i++) {
                $dto = DtoBerita::dariSimulasi($negara->kode_iso, $i);
                $artikel = $this->repositoriBerita->simpan($negara, $dto);
                if ($artikel) {
                    $hasilKoleksi->push($artikel);
                    $jumlahDisimpan++;
                }
            }
            return $hasilKoleksi;
        }
    }
}
