<?php

namespace App\Services\Implementasi;

use App\DTO\DtoEkonomi;
use App\Models\IndikatorEkonomi;
use App\Models\Negara;
use App\Models\LogSinkronisasiApi;
use App\Repositories\Kontrak\RepositoriEkonomiInterface;
use App\Services\Kontrak\LayananEkonomiInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

/**
 * Implementasi Layanan Ekonomi
 */
class LayananEkonomi implements LayananEkonomiInterface
{
    public function __construct(
        protected RepositoriEkonomiInterface $repositoriEkonomi
    ) {}

    /**
     * Sinkronisasikan indikator makroekonomi tahunan untuk sebuah negara.
     */
    public function sinkronkan(Negara $negara, int $tahun): IndikatorEkonomi
    {
        $start = microtime(true);
        $config = config('intelijen.ekonomi');
        $simulasi = config('intelijen.simulasi.aktif');

        if ($simulasi) {
            Log::info("Mode simulasi aktif untuk ekonomi {$negara->nama}");
            $dto = DtoEkonomi::dariSimulasi($negara->kode_iso, $tahun);
            return $this->repositoriEkonomi->simpan($negara, $dto);
        }

        try {
            $pdb = null;
            $inflasi = null;
            $pengangguran = null;
            $bunga = null;
            $neraca = null;

            // Fetch each indicator from World Bank API
            foreach ($config['indikator'] as $kunci => $code) {
                // Contoh endpoint: country/IDN/indicator/NY.GDP.MKTP.CD?mrnev=1&format=json
                $url = "{$config['url_dasar']}/country/{$negara->kode_iso}/indicator/{$code}";
                $response = Http::timeout(5)
                    ->get($url, [
                        'mrnev'  => 1,
                        'format' => $config['format'],
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $val = $json[1][0]['value'] ?? null;

                    switch ($kunci) {
                        case 'pdb':
                            $pdb = $val !== null ? (float) $val : null;
                            break;
                        case 'tingkat_inflasi':
                            $inflasi = $val !== null ? (float) $val : null;
                            break;
                        case 'tingkat_pengangguran':
                            $pengangguran = $val !== null ? (float) $val : null;
                            break;
                        case 'tingkat_bunga':
                            $bunga = $val !== null ? (float) $val : null;
                            break;
                        case 'neraca_perdagangan':
                            $neraca = $val !== null ? (float) $val : null;
                            break;
                    }
                }
            }

            // Create combined DTO
            $dto = new DtoEkonomi(
                kodeIso:           $negara->kode_iso,
                tanggalIndikator:  $tahun . '-01-01',
                pdb:               $pdb,
                tingkatInflasi:    $inflasi,
                tingkatPengangguran:$pengangguran,
                tingkatBunga:      $bunga,
                neracaPerdagangan: $neraca,
                sumberApi:         'World Bank'
            );

            $catatan = $this->repositoriEkonomi->simpan($negara, $dto);

            LogSinkronisasiApi::create([
                'provider'          => 'World Bank',
                'endpoint'          => 'indicators',
                'status'            => 'Berhasil',
                'jumlah_rekaman'    => 1,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => null,
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            return $catatan;

        } catch (Exception $e) {
            Log::error("Gagal melakukan sinkronisasi ekonomi untuk negara {$negara->nama}: " . $e->getMessage());

            LogSinkronisasiApi::create([
                'provider'          => 'World Bank',
                'endpoint'          => 'indicators',
                'status'            => 'Gagal',
                'jumlah_rekaman'    => 0,
                'waktu_eksekusi_ms' => (int) ((microtime(true) - $start) * 1000),
                'pesan_error'       => $e->getMessage(),
                'dieksekusi_pada'   => Carbon::now(),
            ]);

            // Fallback ke data riil terakhir yang tersimpan di DB
            $latest = IndikatorEkonomi::where('negara_id', $negara->id)
                ->whereYear('tanggal_indikator', $tahun)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($latest) {
                Log::warning("Koneksi API World Bank gagal. Menggunakan cache data riil dari database untuk {$negara->nama}.");
                return $latest;
            }

            Log::info("Menghasilkan data ekonomi tiruan untuk {$negara->nama}");
            $dto = DtoEkonomi::dariSimulasi($negara->kode_iso, $tahun);
            return $this->repositoriEkonomi->simpan($negara, $dto);
        }
    }
}
