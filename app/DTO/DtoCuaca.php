<?php

namespace App\DTO;

/**
 * DTO Cuaca (Weather DTO) - Versi 2.0
 *
 * Mengisolasi struktur respons Open-Meteo API dari domain bisnis.
 * Mendukung data harian prakiraan 7 hari ke depan.
 * Immutable setelah dibuat — tidak boleh ada mutasi setelah konstruksi.
 *
 * Pipeline: HTTP Response → DtoCuaca → Validasi → Normalisasi → Repository
 */
final class DtoCuaca
{
    public function __construct(
        public readonly string  $kodeIso,
        public readonly string  $tanggalObservasi,    // Format: Y-m-d
        public readonly ?float  $suhu,                // Celsius (rata-rata harian)
        public readonly ?float  $suhuMin,             // Celsius (minimum harian)
        public readonly ?float  $suhuMax,             // Celsius (maksimum harian)
        public readonly ?float  $kelembaban,          // Persen (%)
        public readonly ?float  $curahHujan,          // Milimeter
        public readonly ?float  $kecepatanAngin,      // km/jam
        public readonly ?string $kondisiCuaca,        // Deskripsi teks (dari kode WMO)
        public readonly ?string $insightScm,          // Analisis dampak ke SCM
        public readonly string  $sumberApi,           // Nama provider
    ) {}

    /**
     * Terjemahkan kode cuaca WMO menjadi deskripsi teks Bahasa Indonesia.
     * Referensi: https://open-meteo.com/en/docs#weathervariables
     */
    public static function terjemahkanKodeWmo(int $kode): string
    {
        return match (true) {
            $kode === 0 => 'Cerah Sempurna',
            $kode === 1 => 'Umumnya Cerah',
            $kode === 2 => 'Berawan Sebagian',
            $kode === 3 => 'Mendung',
            in_array($kode, [45, 48]) => 'Berkabut',
            in_array($kode, [51, 53, 55]) => 'Gerimis',
            in_array($kode, [61, 63, 65]) => 'Hujan',
            in_array($kode, [71, 73, 75]) => 'Bersalju',
            in_array($kode, [77]) => 'Butiran Salju',
            in_array($kode, [80, 81, 82]) => 'Hujan Deras',
            in_array($kode, [85, 86]) => 'Badai Salju',
            in_array($kode, [95]) => 'Badai Petir',
            in_array($kode, [96, 99]) => 'Badai Petir Disertai Es',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Hasilkan insight SCM berbasis aturan (rule-based) dari data cuaca.
     * Ini adalah "kecerdasan" sistem dalam menganalisa dampak cuaca ke rantai pasok.
     */
    public static function analisaInsightScm(
        float $curahHujan,
        float $kecepatanAngin,
        float $suhuMax,
        float $suhuMin,
        string $kondisiCuaca
    ): string {
        $temuan = [];

        // Analisa curah hujan
        if ($curahHujan >= 50) {
            $temuan[] = '🌊 Curah hujan sangat tinggi (' . $curahHujan . 'mm). Risiko banjir di jalur darat sangat tinggi — rekomendasikan diversifikasi rute pengiriman.';
        } elseif ($curahHujan >= 20) {
            $temuan[] = '🌧️ Curah hujan moderat (' . $curahHujan . 'mm). Keterlambatan pengiriman darat berpotensi terjadi, monitor rute secara berkala.';
        }

        // Analisa kecepatan angin
        if ($kecepatanAngin >= 80) {
            $temuan[] = '💨 Kecepatan angin sangat tinggi (' . $kecepatanAngin . ' km/jam). Operasi pelabuhan dan penerbangan kargo berisiko gangguan serius.';
        } elseif ($kecepatanAngin >= 50) {
            $temuan[] = '💨 Angin kencang (' . $kecepatanAngin . ' km/jam). Pertimbangkan penundaan pengiriman kargo udara dan laut.';
        }

        // Analisa suhu ekstrem
        if ($suhuMax >= 40) {
            $temuan[] = '🔥 Suhu panas ekstrem (' . $suhuMax . '°C). Integritas cold chain terancam — pengawasan ketat diperlukan untuk komoditas perishable.';
        } elseif ($suhuMin <= 0) {
            $temuan[] = '🧊 Suhu beku (' . $suhuMin . '°C). Risiko kerusakan produk sensitif suhu dan pembekuan jalur distribusi.';
        }

        // Analisa kondisi cuaca kritis
        if (str_contains(strtolower($kondisiCuaca), 'badai') || str_contains(strtolower($kondisiCuaca), 'petir')) {
            $temuan[] = '⚡ Kondisi cuaca ekstrem terdeteksi (' . $kondisiCuaca . '). Hentikan operasi outdoor dan evakuasi stok dari area berisiko.';
        }

        // Jika tidak ada temuan kritis
        if (empty($temuan)) {
            $temuan[] = '✅ Kondisi cuaca mendukung kelancaran rantai pasok. Tidak ada risiko meteorologi signifikan terdeteksi untuk periode ini.';
        }

        return implode("\n", $temuan);
    }

    /**
     * Buat DtoCuaca dari respons Open-Meteo Forecast API (data harian per index).
     *
     * @param array<string, mixed> $daily   Objek 'daily' dari respons Open-Meteo
     * @param int                  $indeks  Indeks hari (0 = hari ini, 1 = besok, dst.)
     */
    public static function dariResponOpenMeteo(array $daily, int $indeks, string $kodeIso): self
    {
        $tanggal      = $daily['time'][$indeks] ?? date('Y-m-d');
        $suhuMax      = isset($daily['temperature_2m_max'][$indeks]) ? round($daily['temperature_2m_max'][$indeks], 2) : null;
        $suhuMin      = isset($daily['temperature_2m_min'][$indeks]) ? round($daily['temperature_2m_min'][$indeks], 2) : null;
        $suhuRataRata = ($suhuMax !== null && $suhuMin !== null) ? round(($suhuMax + $suhuMin) / 2, 2) : null;
        $curahHujan   = isset($daily['precipitation_sum'][$indeks]) ? round($daily['precipitation_sum'][$indeks], 2) : 0.0;
        $anginMax     = isset($daily['windspeed_10m_max'][$indeks]) ? round($daily['windspeed_10m_max'][$indeks], 2) : null;
        $kodeWmo      = isset($daily['weathercode'][$indeks]) ? (int) $daily['weathercode'][$indeks] : 0;
        $kelembaban   = isset($daily['precipitation_hours'][$indeks]) ? round(min(100, $daily['precipitation_hours'][$indeks] / 24 * 100), 2) : null;

        $kondisi      = self::terjemahkanKodeWmo($kodeWmo);

        $insight = self::analisaInsightScm(
            curahHujan:    $curahHujan ?? 0,
            kecepatanAngin: $anginMax ?? 0,
            suhuMax:       $suhuMax ?? 25,
            suhuMin:       $suhuMin ?? 15,
            kondisiCuaca:  $kondisi
        );

        return new self(
            kodeIso:          strtoupper($kodeIso),
            tanggalObservasi: $tanggal,
            suhu:             $suhuRataRata,
            suhuMin:          $suhuMin,
            suhuMax:          $suhuMax,
            kelembaban:       $kelembaban,
            curahHujan:       $curahHujan,
            kecepatanAngin:   $anginMax,
            kondisiCuaca:     $kondisi,
            insightScm:       $insight,
            sumberApi:        'Open-Meteo',
        );
    }

    /**
     * Buat DtoCuaca dari data simulasi (Mock Mode).
     */
    public static function dariSimulasi(string $kodeIso, string $tanggal): self
    {
        $hash = crc32($kodeIso . $tanggal);
        mt_srand(abs($hash));

        $kondisiList = ['Cerah Sempurna', 'Berawan Sebagian', 'Hujan', 'Hujan Deras', 'Badai Petir', 'Berkabut', 'Gerimis'];
        $suhuMax = round(mt_rand(20, 42) + mt_rand(0, 99) / 100, 2);
        $suhuMin = round($suhuMax - mt_rand(5, 12), 2);
        $curahHujan = round(mt_rand(0, 60) + mt_rand(0, 99) / 100, 2);
        $angin = round(mt_rand(5, 90) + mt_rand(0, 99) / 100, 2);
        $kondisi = $kondisiList[abs($hash) % count($kondisiList)];

        $insight = self::analisaInsightScm($curahHujan, $angin, $suhuMax, $suhuMin, $kondisi);

        return new self(
            kodeIso:          strtoupper($kodeIso),
            tanggalObservasi: $tanggal,
            suhu:             round(($suhuMax + $suhuMin) / 2, 2),
            suhuMin:          $suhuMin,
            suhuMax:          $suhuMax,
            kelembaban:       round(mt_rand(30, 95) + mt_rand(0, 99) / 100, 2),
            curahHujan:       $curahHujan,
            kecepatanAngin:   $angin,
            kondisiCuaca:     $kondisi,
            insightScm:       $insight,
            sumberApi:        'Simulasi',
        );
    }

    /**
     * Konversi ke array untuk disimpan oleh repository.
     *
     * @return array<string, mixed>
     */
    public function keArray(): array
    {
        return [
            'tanggal_observasi' => $this->tanggalObservasi,
            'suhu'              => $this->suhu,
            'suhu_min'          => $this->suhuMin,
            'suhu_max'          => $this->suhuMax,
            'kelembaban'        => $this->kelembaban,
            'curah_hujan'       => $this->curahHujan,
            'kecepatan_angin'   => $this->kecepatanAngin,
            'kondisi_cuaca'     => $this->kondisiCuaca,
            'insight_scm'       => $this->insightScm,
            'sumber_api'        => $this->sumberApi,
        ];
    }
}
