<?php

namespace App\Services\Implementasi;

use App\Models\Negara;
use App\Models\PenilaianRisiko;
use App\Models\Pengaturan;
use App\Models\NilaiTukar;
use App\Models\ArtikelBerita;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use App\Services\Kontrak\MesinRisikoInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Implementasi Mesin Risiko (Risk Intelligence Engine)
 */
class MesinRisiko implements MesinRisikoInterface
{
    public function __construct(
        protected RepositoriRisikoInterface $repositoriRisiko
    ) {}

    /**
     * Hitung skor risiko total untuk sebuah negara berdasarkan data intelijen terbaru.
     */
    public function hitung(Negara $negara): PenilaianRisiko
    {
        // 1. Ambil bobot risiko dari tabel pengaturan
        $bobot = $this->dapatkanBobotRisiko();

        // 2. Hitung skor per komponen (0 - 100)
        $skorCuaca      = $this->hitungSkorCuaca($negara);
        $skorEkonomi    = $this->hitungSkorEkonomi($negara);
        $skorNilaiTukar = $this->hitungSkorNilaiTukar($negara);
        $skorBerita     = $this->hitungSkorBerita($negara);
        $skorLogistik   = $this->hitungSkorLogistik($negara);
        $skorPolitik    = $this->hitungSkorPolitik($negara);

        // 3. Hitung skor total tertimbang
        $skorTotal = (
            ($skorCuaca      * $bobot['cuaca']) +
            ($skorEkonomi    * $bobot['ekonomi']) +
            ($skorNilaiTukar * $bobot['nilai_tukar']) +
            ($skorBerita     * $bobot['berita']) +
            ($skorLogistik   * $bobot['logistik']) +
            ($skorPolitik    * $bobot['politik'])
        ) / 100;

        $skorTotal = round(min(100, max(0, $skorTotal)), 2);

        // 4. Tentukan level risiko berdasarkan ambang batas
        $levelRisiko = $this->tentukanLevelRisiko($skorTotal);

        // 5. Susun penjelasan/bukti (explainability)
        $penjelasan = $this->susunPenjelasan(
            $skorCuaca, $skorEkonomi, $skorNilaiTukar, $skorBerita, $skorLogistik, $skorPolitik,
            $negara
        );

        // 6. Simpan hasil penilaian risiko
        $penilaian = $this->repositoriRisiko->simpanPenilaian($negara, [
            'skor_cuaca'       => $skorCuaca,
            'skor_ekonomi'     => $skorEkonomi,
            'skor_nilai_tukar' => $skorNilaiTukar,
            'skor_berita'      => $skorBerita,
            'skor_logistik'    => $skorLogistik,
            'skor_politik'     => $skorPolitik,
            'skor_total'       => $skorTotal,
            'level_risiko'     => $levelRisiko,
            'penjelasan'       => $penjelasan,
        ]);

        // 7. Hasilkan rekomendasi otomatis berdasarkan risiko dominan
        $this->buatRekomendasiOtomatis($penilaian, $skorCuaca, $skorEkonomi, $skorNilaiTukar, $skorBerita);

        return $penilaian;
    }

    /**
     * Dapatkan daftar bobot risiko dari pengaturan.
     */
    protected function dapatkanBobotRisiko(): array
    {
        $bobotCuaca      = Pengaturan::where('kunci', 'risiko.bobot_cuaca')->first();
        $bobotEkonomi    = Pengaturan::where('kunci', 'risiko.bobot_ekonomi')->first();
        $bobotNilaiTukar = Pengaturan::where('kunci', 'risiko.bobot_nilai_tukar')->first();
        $bobotBerita     = Pengaturan::where('kunci', 'risiko.bobot_berita')->first();
        $bobotLogistik   = Pengaturan::where('kunci', 'risiko.bobot_logistik')->first();
        $bobotPolitik    = Pengaturan::where('kunci', 'risiko.bobot_politik')->first();

        return [
            'cuaca'       => $bobotCuaca      ? (int)$bobotCuaca->nilai      : 20,
            'ekonomi'     => $bobotEkonomi    ? (int)$bobotEkonomi->nilai    : 25,
            'nilai_tukar' => $bobotNilaiTukar ? (int)$bobotNilaiTukar->nilai : 15,
            'berita'      => $bobotBerita     ? (int)$bobotBerita->nilai     : 20,
            'logistik'    => $bobotLogistik   ? (int)$bobotLogistik->nilai   : 10,
            'politik'     => $bobotPolitik    ? (int)$bobotPolitik->nilai    : 10,
        ];
    }

    /**
     * Hitung skor komponen cuaca (0 - 100).
     */
    protected function hitungSkorCuaca(Negara $negara): float
    {
        $cuaca = $negara->cuacaTerkini();
        if (!$cuaca) return 0.0;

        $skor = 0.0;

        // Curah hujan ekstrem (> 100mm)
        if ($cuaca->curah_hujan > 100) {
            $skor += 40;
        } elseif ($cuaca->curah_hujan > 50) {
            $skor += 20;
        }

        // Angin kencang / badai (> 60 km/jam)
        if ($cuaca->kecepatan_angin > 60) {
            $skor += 30;
        } elseif ($cuaca->kecepatan_angin > 30) {
            $skor += 15;
        }

        // Suhu ekstrem (> 40°C atau < 0°C)
        if ($cuaca->suhu > 40 || $cuaca->suhu < 0) {
            $skor += 30;
        } elseif ($cuaca->suhu > 35 || $cuaca->suhu < 5) {
            $skor += 15;
        }

        return min(100.0, $skor);
    }

    /**
     * Hitung skor komponen ekonomi (0 - 100).
     */
    protected function hitungSkorEkonomi(Negara $negara): float
    {
        $ekonomi = $negara->indikatorEkonomi()->orderBy('tanggal_indikator', 'desc')->first();
        if (!$ekonomi) return 15.0; // Base default risk

        $skor = 0.0;

        // Inflasi tinggi (> 10% kritis, > 5% tinggi)
        if ($ekonomi->tingkat_inflasi > 10) {
            $skor += 40;
        } elseif ($ekonomi->tingkat_inflasi > 5) {
            $skor += 20;
        }

        // Pengangguran tinggi (> 12% kritis, > 8% tinggi)
        if ($ekonomi->tingkat_pengangguran > 12) {
            $skor += 30;
        } elseif ($ekonomi->tingkat_pengangguran > 8) {
            $skor += 15;
        }

        // Neraca perdagangan defisit (impor > ekspor)
        if ($ekonomi->neraca_perdagangan < 0) {
            $skor += 30;
        }

        return min(100.0, max(10.0, $skor));
    }

    /**
     * Hitung skor komponen nilai tukar (0 - 100).
     */
    protected function hitungSkorNilaiTukar(Negara $negara): float
    {
        if ($negara->kode_iso === 'USA') return 0.0; // USD adalah jangkar mata uang

        $mataUang = LayananNilaiTukar::dapatkanMataUangNegara($negara->kode_iso);
        $terkini = $negara->nilaiTukar()->where('kode_mata_uang', $mataUang)->orderBy('tanggal_berlaku', 'desc')->first();

        if (!$terkini) return 15.0;

        // Hitung volatilitas dibanding rata-rata 10 catatan terakhir
        $riwayat = NilaiTukar::where('negara_id', $negara->id)
            ->where('kode_mata_uang', $mataUang)
            ->orderBy('tanggal_berlaku', 'desc')
            ->limit(10)
            ->pluck('nilai_tukar')
            ->toArray();

        if (count($riwayat) < 2) return 15.0;

        $rataRata = array_sum($riwayat) / count($riwayat);
        $nilaiTerkini = $terkini->nilai_tukar;

        // Persentase kenaikan nilai tukar (depresiasi mata uang lokal terhadap USD)
        // Nilai tukar naik = melemah (depresiasi)
        $perubahan = 0.0;
        if ($rataRata > 0) {
            $perubahan = (($nilaiTerkini - $rataRata) / $rataRata) * 100;
        }

        $skor = 15.0; // Base risk

        if ($perubahan > 10) { // Depresiasi sangat tajam (>10%)
            $skor += 75;
        } elseif ($perubahan > 5) { // Depresiasi sedang (>5%)
            $skor += 40;
        } elseif ($perubahan > 2) { // Depresiasi ringan (>2%)
            $skor += 20;
        } elseif ($perubahan < -5) { // Apresiasi tajam (menguat)
            $skor -= 10;
        }

        return min(100.0, max(0.0, $skor));
    }

    /**
     * Hitung skor komponen berita (0 - 100).
     */
    protected function hitungSkorBerita(Negara $negara): float
    {
        // Ambil berita dalam 7 hari terakhir
        $beritas = $negara->artikelBerita()
            ->where('diterbitkan_pada', '>=', Carbon::now()->subDays(7))
            ->get();

        if ($beritas->isEmpty()) return 10.0;

        $skor = 10.0;

        foreach ($beritas as $berita) {
            $bobotKeparahan = match ($berita->keparahan) {
                'kritis' => 35,
                'tinggi' => 20,
                'sedang' => 10,
                default  => 0,
            };

            $bobotSentimen = match ($berita->sentimen) {
                'negatif' => 5,
                'positif' => -5,
                default   => 0,
            };

            $skor += ($bobotKeparahan + $bobotSentimen);
        }

        return min(100.0, max(0.0, $skor));
    }

    /**
     * Hitung skor logistik (0 - 100).
     */
    protected function hitungSkorLogistik(Negara $negara): float
    {
        // Deteksi kemacetan/pemogokan dari judul berita
        $beritas = $negara->artikelBerita()
            ->where('diterbitkan_pada', '>=', Carbon::now()->subDays(14))
            ->get();

        $skor = 15.0; // Base default risk (aman)

        foreach ($beritas as $berita) {
            $teks = strtolower($berita->judul . ' ' . $berita->ringkasan);
            if (str_contains($teks, 'strike') || str_contains($teks, 'port congestion') || str_contains($teks, 'logistics disruption') || str_contains($teks, 'blockade')) {
                $skor += 35;
            }
        }

        return min(100.0, $skor);
    }

    /**
     * Hitung skor politik (0 - 100).
     */
    protected function hitungSkorPolitik(Negara $negara): float
    {
        // Deteksi ketegangan politik, kudeta, sanksi
        $beritas = $negara->artikelBerita()
            ->where('diterbitkan_pada', '>=', Carbon::now()->subDays(14))
            ->get();

        $skor = 15.0; // Base default risk

        foreach ($beritas as $berita) {
            $teks = strtolower($berita->judul . ' ' . $berita->ringkasan);
            if (str_contains($teks, 'sanctions') || str_contains($teks, 'protest') || str_contains($teks, 'riot') || str_contains($teks, 'geopolitical') || str_contains($teks, 'conflict')) {
                $skor += 30;
            }
        }

        // Contoh status negara yang tidak dipantau karena konflik militer tinggi
        if ($negara->status_pemantauan === false) {
            $skor += 70;
        }

        return min(100.0, $skor);
    }

    /**
     * Tentukan klasifikasi level risiko berdasarkan skor total.
     */
    protected function tentukanLevelRisiko(float $skorTotal): string
    {
        $ambangRendah = Pengaturan::where('kunci', 'risiko.ambang_rendah')->first();
        $ambangSedang = Pengaturan::where('kunci', 'risiko.ambang_sedang')->first();
        $ambangTinggi = Pengaturan::where('kunci', 'risiko.ambang_tinggi')->first();

        $r = $ambangRendah ? (int)$ambangRendah->nilai : 25;
        $s = $ambangSedang ? (int)$ambangSedang->nilai : 50;
        $t = $ambangTinggi ? (int)$ambangTinggi->nilai : 75;

        if ($skorTotal <= $r) return 'Rendah';
        if ($skorTotal <= $s) return 'Sedang';
        if ($skorTotal <= $t) return 'Tinggi';
        return 'Kritis';
    }

    /**
     * Susun bukti pendukung keputusan (Explainable Evidence).
     */
    protected function susunPenjelasan(
        float $cuaca, float $ekonomi, float $nilaiTukar, float $berita, float $logistik, float $politik,
        Negara $negara
    ): array {
        $bukti = [];

        if ($cuaca > 50) {
            $cuacaModel = $negara->cuacaTerkini();
            $bukti[] = "Cuaca buruk: Suhu {$cuacaModel?->suhu}°C, curah hujan {$cuacaModel?->curah_hujan}mm, kondisi '{$cuacaModel?->kondisi_cuaca}'.";
        }
        if ($ekonomi > 50) {
            $ekoModel = $negara->indikatorEkonomi()->orderBy('tanggal_indikator', 'desc')->first();
            $bukti[] = "Indikator ekonomi melemah: Inflasi {$ekoModel?->tingkat_inflasi}%, pengangguran {$ekoModel?->tingkat_pengangguran}%.";
        }
        if ($nilaiTukar > 50) {
            $bukti[] = "Depresiasi mata uang lokal yang signifikan terhadap USD.";
        }
        if ($berita > 50) {
            $bukti[] = "Sentimen berita negatif mendominasi media dalam 7 hari terakhir.";
        }
        if ($logistik > 50) {
            $bukti[] = "Kemacetan logistik terdeteksi di pelabuhan/hub utama.";
        }
        if ($politik > 50) {
            $bukti[] = "Instabilitas sosial politik atau sanksi internasional terdeteksi.";
        }

        if (empty($bukti)) {
            $bukti[] = "Semua parameter stabil dan terpantau aman.";
        }

        return $bukti;
    }

    /**
     * Buat rekomendasi mitigasi otomatis berdasarkan parameter risiko tertinggi.
     */
    protected function buatRekomendasiOtomatis(
        PenilaianRisiko $penilaian, float $cuaca, float $ekonomi, float $nilaiTukar, float $berita
    ): void {
        // Cari skor tertinggi dari parameter-parameter utama
        $skorArray = [
            'cuaca'       => $cuaca,
            'ekonomi'     => $ekonomi,
            'nilai_tukar' => $nilaiTukar,
            'berita'      => $berita,
        ];

        arsort($skorArray);
        $tertinggi = key($skorArray);
        $skorTertinggi = current($skorArray);

        // Jika level risiko Rendah, berikan rekomendasi umum
        if ($penilaian->level_risiko === 'Rendah') {
            $this->repositoriRisiko->simpanRekomendasi($penilaian, [
                'rekomendasi' => 'Pertahankan volume pengadaan standar dan pantau berkala.',
                'prioritas'   => 'Rendah',
                'status'      => 'Tertunda',
            ]);
            return;
        }

        // Hasilkan rekomendasi spesifik jika risiko Sedang/Tinggi/Kritis
        $rekomendasiTeks = '';
        $prioritas = $penilaian->level_risiko;

        switch ($tertinggi) {
            case 'cuaca':
                if ($skorTertinggi > 70) {
                    $rekomendasiTeks = 'Segera alihkan rute pengiriman utama untuk menghindari pelabuhan terdampak badai/cuaca ekstrem.';
                } else {
                    $rekomendasiTeks = 'Siapkan penyimpanan ekstra di pelabuhan transit untuk mengantisipasi keterlambatan cuaca.';
                }
                break;
            case 'ekonomi':
                if ($skorTertinggi > 70) {
                    $rekomendasiTeks = 'Kurangi eksposur pembelian jangka pendek, lakukan re-negosiasi kontrak dengan jaminan harga tetap.';
                } else {
                    $rekomendasiTeks = 'Diversifikasi pemasok ke wilayah dengan tingkat inflasi yang lebih stabil.';
                }
                break;
            case 'nilai_tukar':
                if ($skorTertinggi > 70) {
                    $rekomendasiTeks = 'Gunakan kontrak valuta asing berjangka (forward hedging) untuk mengunci kurs transaksi pengadaan.';
                } else {
                    $rekomendasiTeks = 'Alihkan mata uang transaksi ke mata uang lokal atau gunakan mekanisme barter jika memungkinkan.';
                }
                break;
            case 'berita':
                if ($skorTertinggi > 70) {
                    $rekomendasiTeks = 'Aktifkan rencana darurat (Contingency Plan) rantai pasok cadangan untuk mengantisipasi penutupan operasional mendadak.';
                } else {
                    $rekomendasiTeks = 'Tingkatkan frekuensi pemantauan berita harian untuk mengamati eskalasi situasi.';
                }
                break;
        }

        $this->repositoriRisiko->simpanRekomendasi($penilaian, [
            'rekomendasi' => $rekomendasiTeks,
            'prioritas'   => $prioritas,
            'status'      => 'Tertunda',
        ]);
    }
}
