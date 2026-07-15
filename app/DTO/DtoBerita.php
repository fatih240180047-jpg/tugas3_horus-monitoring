<?php

namespace App\DTO;

/**
 * DTO Berita (News DTO)
 *
 * Mengisolasi struktur respons NewsAPI dari domain bisnis.
 * Menyertakan hasil analisis sentimen dan klasifikasi keparahan.
 * Immutable setelah dibuat.
 *
 * Pipeline: HTTP Response → DtoBerita → Sentimen → Keparahan → Repository
 */
final class DtoBerita
{
    public function __construct(
        public readonly string  $kodeIso,
        public readonly string  $judul,
        public readonly ?string $ringkasan,
        public readonly ?string $kategori,
        public readonly string  $sentimen,        // positif | netral | negatif
        public readonly string  $keparahan,       // rendah | sedang | tinggi | kritis
        public readonly ?string $sumber,
        public readonly string  $diterbitkanPada,  // Format: Y-m-d H:i:s
        public readonly string  $sumberApi,
        public readonly ?string $urlAsli   = null, // URL artikel asli
        public readonly ?string $dampakScm = null, // Analisis dampak ke SCM
    ) {}

    /**
     * Buat DtoBerita dari respons NewsAPI.
     *
     * @param array<string, mixed> $artikel
     */
    public static function dariResponNewsApi(array $artikel, string $kodeIso): self
    {
        $judul     = $artikel['title']       ?? 'Tanpa Judul';
        $ringkasan = $artikel['description'] ?? $artikel['content'] ?? null;
        $sumber    = $artikel['source']['name'] ?? null;
        $url       = $artikel['url'] ?? null;
        $tanggal   = isset($artikel['publishedAt'])
            ? date('Y-m-d H:i:s', strtotime($artikel['publishedAt']))
            : date('Y-m-d H:i:s');

        [$sentimen, $keparahan] = self::analisisSentimenDanKeparahan($judul . ' ' . ($ringkasan ?? ''));
        $dampakScm = self::analisaDampakScm($judul . ' ' . ($ringkasan ?? ''), $sentimen, $keparahan);

        return new self(
            kodeIso:         strtoupper($kodeIso),
            judul:           substr($judul, 0, 500),
            ringkasan:       $ringkasan ? substr($ringkasan, 0, 2000) : null,
            kategori:        $artikel['category'] ?? 'umum',
            sentimen:        $sentimen,
            keparahan:       $keparahan,
            sumber:          $sumber,
            diterbitkanPada: $tanggal,
            sumberApi:       'NewsAPI',
            urlAsli:         $url,
            dampakScm:       $dampakScm,
        );
    }

    /**
     * Buat DtoBerita dari respons GNews API.
     */
    public static function dariResponGNews(array $artikel, string $kodeIso): self
    {
        $judul     = $artikel['title']       ?? 'Tanpa Judul';
        $ringkasan = $artikel['description'] ?? $artikel['content'] ?? null;
        $sumber    = $artikel['source']['name'] ?? null;
        $url       = $artikel['url'] ?? null;
        $tanggal   = isset($artikel['publishedAt'])
            ? date('Y-m-d H:i:s', strtotime($artikel['publishedAt']))
            : date('Y-m-d H:i:s');

        // Bersihkan ringkasan dari tag [N chars] yang kadang muncul dari GNews
        if ($ringkasan) {
            $ringkasan = preg_replace('/\[\d+ chars\]/', '', $ringkasan);
            $ringkasan = trim(substr($ringkasan, 0, 2000));
        }

        [$sentimen, $keparahan] = self::analisisSentimenDanKeparahan($judul . ' ' . ($ringkasan ?? ''));
        $dampakScm = self::analisaDampakScm($judul . ' ' . ($ringkasan ?? ''), $sentimen, $keparahan);

        return new self(
            kodeIso:         strtoupper($kodeIso),
            judul:           substr($judul, 0, 500),
            ringkasan:       $ringkasan,
            kategori:        'supply_chain',
            sentimen:        $sentimen,
            keparahan:       $keparahan,
            sumber:          $sumber,
            diterbitkanPada: $tanggal,
            sumberApi:       'GNews API',
            urlAsli:         $url,
            dampakScm:       $dampakScm,
        );
    }

    /**
     * Analisis sentimen dan keparahan sederhana berbasis kata kunci.
     *
     * @return array{string, string}  [sentimen, keparahan]
     */
    public static function analisisSentimenDanKeparahan(string $teks): array
    {
        $teksLower = strtolower($teks);

        // Kata kunci negatif berbobot tinggi (SCM-focused)
        $kataNegatifKritis = [
            'war', 'invasion', 'crisis', 'collapse', 'catastrophe', 'sanctions', 'embargo',
            'conflict', 'coup', 'terrorism', 'perang', 'krisis', 'bencana', 'invasi',
            'nuclear', 'blockade', 'martial law', 'civil war', 'mass protest',
        ];
        $kataNegatifTinggi = [
            'recession', 'shortage', 'disruption', 'inflation', 'strike', 'protest',
            'flood', 'earthquake', 'typhoon', 'pandemic', 'resesi', 'gangguan', 'banjir',
            'supply chain disruption', 'port closure', 'border closure', 'default',
            'bankrupt', 'financial crisis', 'supply shortage',
        ];
        $kataNegatifSedang = [
            'concern', 'risk', 'delay', 'problem', 'issue', 'tension', 'slowdown',
            'decline', 'worry', 'kekhawatiran', 'risiko', 'keterlambatan',
            'warning', 'alert', 'downgrade', 'shortage warning',
        ];
        $kataPositif = [
            'growth', 'recovery', 'agreement', 'deal', 'increase', 'partnership',
            'investment', 'stable', 'improved', 'pemulihan', 'pertumbuhan', 'kesepakatan',
            'trade deal', 'new route', 'expansion', 'milestone', 'record high export',
        ];

        foreach ($kataNegatifKritis as $kata) {
            if (str_contains($teksLower, $kata)) return ['negatif', 'kritis'];
        }
        foreach ($kataNegatifTinggi as $kata) {
            if (str_contains($teksLower, $kata)) return ['negatif', 'tinggi'];
        }
        foreach ($kataNegatifSedang as $kata) {
            if (str_contains($teksLower, $kata)) return ['negatif', 'sedang'];
        }
        foreach ($kataPositif as $kata) {
            if (str_contains($teksLower, $kata)) return ['positif', 'rendah'];
        }

        return ['netral', 'rendah'];
    }

    /**
     * Analisis dampak berita ke Rantai Pasok (SCM) berdasarkan konten dan klasifikasi.
     * Menghasilkan teks insight yang actionable untuk analis SCM.
     */
    public static function analisaDampakScm(string $teks, string $sentimen, string $keparahan): string
    {
        $teksLower = strtolower($teks);

        // Deteksi isu spesifik SCM
        $dampak = [];

        if (str_contains($teksLower, 'port') || str_contains($teksLower, 'pelabuhan') || str_contains($teksLower, 'harbour')) {
            $dampak[] = '🚢 Berpotensi mempengaruhi operasi pelabuhan dan jadwal pengiriman laut.';
        }
        if (str_contains($teksLower, 'sanctions') || str_contains($teksLower, 'embargo') || str_contains($teksLower, 'ban')) {
            $dampak[] = '🚫 Sanksi atau embargo dapat menghambat aliran barang — evaluasi segera vendor alternatif dari negara lain.';
        }
        if (str_contains($teksLower, 'oil') || str_contains($teksLower, 'fuel') || str_contains($teksLower, 'energy')) {
            $dampak[] = '⛽ Fluktuasi harga energi akan berdampak langsung pada biaya logistik dan transportasi kargo.';
        }
        if (str_contains($teksLower, 'currency') || str_contains($teksLower, 'exchange rate') || str_contains($teksLower, 'devaluation')) {
            $dampak[] = '💱 Perubahan nilai tukar berpengaruh langsung pada biaya pengadaan impor dari negara ini.';
        }
        if (str_contains($teksLower, 'factory') || str_contains($teksLower, 'manufacturing') || str_contains($teksLower, 'production')) {
            $dampak[] = '🏭 Gangguan kapasitas produksi dapat menyebabkan shortage pasokan komponen/bahan baku.';
        }
        if (str_contains($teksLower, 'flood') || str_contains($teksLower, 'typhoon') || str_contains($teksLower, 'earthquake') || str_contains($teksLower, 'disaster')) {
            $dampak[] = '🌊 Bencana alam berpotensi merusak infrastruktur logistik — diversifikasi rute pengiriman segera.';
        }
        if (str_contains($teksLower, 'trade deal') || str_contains($teksLower, 'agreement') || str_contains($teksLower, 'partnership')) {
            $dampak[] = '🤝 Kesepakatan perdagangan baru dapat membuka peluang pengadaan dengan biaya lebih kompetitif.';
        }

        // Tambahkan analisis umum berdasarkan keparahan
        if (empty($dampak)) {
            $dampak[] = match ($keparahan) {
                'kritis' => '🚨 Situasi kritis — segera lakukan risk assessment menyeluruh dan aktifkan Business Continuity Plan.',
                'tinggi' => '⚠️ Risiko tinggi terdeteksi — monitor intensif dan siapkan rencana mitigasi alternatif.',
                'sedang' => '📌 Risiko sedang — pantau perkembangan situasi dan persiapkan langkah antisipasi jika eskalasi terjadi.',
                default  => $sentimen === 'positif'
                    ? '✅ Berita positif — berpotensi meningkatkan efisiensi dan kelancaran rantai pasok dari negara ini.'
                    : '📋 Dampak terbatas pada SCM — informasi ini bersifat informatif untuk pemantauan geopolitik.',
            };
        }

        return implode("\n", $dampak);
    }

    /**
     * Buat DtoBerita dari data simulasi.
     */
    public static function dariSimulasi(string $kodeIso, int $indeks): self
    {
        $hash = crc32($kodeIso . $indeks);
        mt_srand(abs($hash));

        // Peta nama negara berdasarkan kode ISO untuk judul yang lebih faktual
        $namaNegara = [
            'IDN' => 'Indonesia', 'USA' => 'United States', 'CHN' => 'China',
            'SGP' => 'Singapore', 'JPN' => 'Japan', 'DEU' => 'Germany',
            'GBR' => 'United Kingdom', 'IND' => 'India', 'AUS' => 'Australia',
            'BRA' => 'Brazil', 'CAN' => 'Canada', 'FRA' => 'France',
            'NLD' => 'Netherlands', 'ARE' => 'UAE', 'SAU' => 'Saudi Arabia',
            'MYS' => 'Malaysia', 'THA' => 'Thailand', 'VNM' => 'Vietnam',
            'PHL' => 'Philippines', 'KOR' => 'South Korea',
        ][$kodeIso] ?? $kodeIso;

        $beritaSimulasi = [
            ['judul' => $namaNegara . ' export growth surges amid strong global demand', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
            ['judul' => $namaNegara . ' supply chain disruptions worsen due to extreme weather', 'sentimen' => 'negatif', 'keparahan' => 'tinggi'],
            ['judul' => $namaNegara . ' inflation hits 5-year high, pressuring import costs', 'sentimen' => 'negatif', 'keparahan' => 'sedang'],
            ['judul' => 'Foreign direct investment in ' . $namaNegara . ' surges to record levels', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
            ['judul' => $namaNegara . ' political crisis threatens commodity supply chains', 'sentimen' => 'negatif', 'keparahan' => 'kritis'],
            ['judul' => $namaNegara . ' economic recovery accelerates faster than projected', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
            ['judul' => $namaNegara . ' main port faces severe congestion, shipping delays expected', 'sentimen' => 'negatif', 'keparahan' => 'sedang'],
            ['judul' => $namaNegara . ' signs major new free trade agreement', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
        ];

        $pilihan      = $beritaSimulasi[abs($hash) % count($beritaSimulasi)];
        $judul        = $pilihan['judul'];
        $sumberList   = ['Reuters', 'Bloomberg', 'Financial Times', 'BBC', 'CNBC', 'AP News'];
        $sumberTerpilih = $sumberList[abs($hash) % count($sumberList)];
        $dampakScm    = self::analisaDampakScm($judul, $pilihan['sentimen'], $pilihan['keparahan']);
        
        // Kembalikan URL Google News agar pengguna tidak menemui 404 pada mode simulasi
        $queryGnews = urlencode($namaNegara . ' ' . explode(' ', $judul, 4)[2] ?? $judul);
        $urlBerita  = 'https://news.google.com/search?q=' . $queryGnews . '&hl=en';

        return new self(
            kodeIso:         strtoupper($kodeIso),
            judul:           $judul,
            ringkasan:       'News intelligence for ' . $namaNegara . ' supply chain — Index: ' . $indeks . '. Click the headline to read verified news from global sources.',
            kategori:        'supply_chain',
            sentimen:        $pilihan['sentimen'],
            keparahan:       $pilihan['keparahan'],
            sumber:          $sumberTerpilih,
            diterbitkanPada: date('Y-m-d H:i:s', strtotime('-' . ($indeks * 6) . ' hours')),
            sumberApi:       'Simulasi',
            urlAsli:         $urlBerita,
            dampakScm:       $dampakScm,
        );
    }

    /**
     * Buat DtoBerita dari elemen XML Google News RSS.
     */
    public static function dariItemRss(\SimpleXMLElement $item, string $kodeIso): self
    {
        $judulKasar = (string) $item->title;
        
        // Google News RSS titles are formatted as: "Headline - Source Name"
        $parts = explode(' - ', $judulKasar);
        $sumber = count($parts) > 1 ? array_pop($parts) : 'Global News';
        $judul = implode(' - ', $parts);
        if (empty($judul)) {
            $judul = $judulKasar;
        }

        $url = (string) $item->link;
        $tanggal = isset($item->pubDate)
            ? date('Y-m-d H:i:s', strtotime((string) $item->pubDate))
            : date('Y-m-d H:i:s');

        $ringkasan = "Laporan berita aktual terkait aktivitas rantai pasok, perdagangan, logistik, dan indikator ekonomi makro di kawasan tersebut.";

        [$sentimen, $keparahan] = self::analisisSentimenDanKeparahan($judul);
        $dampakScm = self::analisaDampakScm($judul, $sentimen, $keparahan);

        return new self(
            kodeIso:         strtoupper($kodeIso),
            judul:           substr($judul, 0, 500),
            ringkasan:       $ringkasan,
            kategori:        'supply_chain',
            sentimen:        $sentimen,
            keparahan:       $keparahan,
            sumber:          $sumber,
            diterbitkanPada: $tanggal,
            sumberApi:       'Google News RSS',
            urlAsli:         $url,
            dampakScm:       $dampakScm,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function keArray(): array
    {
        return [
            'judul'            => $this->judul,
            'ringkasan'        => $this->ringkasan,
            'kategori'         => $this->kategori,
            'sentimen'         => $this->sentimen,
            'keparahan'        => $this->keparahan,
            'sumber'           => $this->sumber,
            'diterbitkan_pada' => $this->diterbitkanPada,
            'sumber_api'       => $this->sumberApi,
            'url_asli'         => $this->urlAsli,
            'dampak_scm'       => $this->dampakScm,
        ];
    }
}
