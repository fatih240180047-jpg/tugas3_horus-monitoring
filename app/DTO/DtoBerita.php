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
        public readonly string  $sentimen,       // positif | netral | negatif
        public readonly string  $keparahan,      // rendah | sedang | tinggi | kritis
        public readonly ?string $sumber,
        public readonly string  $diterbitkanPada, // Format: Y-m-d H:i:s
        public readonly string  $sumberApi,
    ) {}

    /**
     * Buat DtoBerita dari respons NewsAPI.
     *
     * @param array<string, mixed> $artikel
     */
    public static function dariResponNewsApi(array $artikel, string $kodeIso): self
    {
        $judul    = $artikel['title']       ?? 'Tanpa Judul';
        $ringkasan= $artikel['description'] ?? $artikel['content'] ?? null;
        $sumber   = $artikel['source']['name'] ?? null;
        $tanggal  = isset($artikel['publishedAt'])
            ? date('Y-m-d H:i:s', strtotime($artikel['publishedAt']))
            : date('Y-m-d H:i:s');

        [$sentimen, $keparahan] = self::analisisSentimenDanKeparahan($judul . ' ' . ($ringkasan ?? ''));

        return new self(
            kodeIso:          strtoupper($kodeIso),
            judul:            substr($judul, 0, 500),
            ringkasan:        $ringkasan ? substr($ringkasan, 0, 2000) : null,
            kategori:         $artikel['category'] ?? 'umum',
            sentimen:         $sentimen,
            keparahan:        $keparahan,
            sumber:           $sumber,
            diterbitkanPada:  $tanggal,
            sumberApi:        'NewsAPI',
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

        // --- Kata kunci negatif berbobot ---
        $kataNegatifKritis = ['war', 'invasion', 'crisis', 'collapse', 'catastrophe', 'sanctions', 'embargo',
                               'conflict', 'coup', 'terrorism', 'perang', 'krisis', 'bencana', 'invasi'];
        $kataNegatifTinggi = ['recession', 'shortage', 'disruption', 'inflation', 'strike', 'protest',
                               'flood', 'earthquake', 'typhoon', 'pandemic', 'resesi', 'gangguan', 'banjir'];
        $kataNegatifSedang = ['concern', 'risk', 'delay', 'problem', 'issue', 'tension', 'slowdown',
                               'decline', 'worry', 'kekhawatiran', 'risiko', 'keterlambatan'];
        $kataPositif       = ['growth', 'recovery', 'agreement', 'deal', 'increase', 'partnership',
                               'investment', 'stable', 'improved', 'pemulihan', 'pertumbuhan', 'kesepakatan'];

        foreach ($kataNegatifKritis as $kata) {
            if (str_contains($teksLower, $kata)) {
                return ['negatif', 'kritis'];
            }
        }
        foreach ($kataNegatifTinggi as $kata) {
            if (str_contains($teksLower, $kata)) {
                return ['negatif', 'tinggi'];
            }
        }
        foreach ($kataNegatifSedang as $kata) {
            if (str_contains($teksLower, $kata)) {
                return ['negatif', 'sedang'];
            }
        }
        foreach ($kataPositif as $kata) {
            if (str_contains($teksLower, $kata)) {
                return ['positif', 'rendah'];
            }
        }

        return ['netral', 'rendah'];
    }

    /**
     * Buat DtoBerita dari data simulasi.
     */
    public static function dariSimulasi(string $kodeIso, int $indeks): self
    {
        $hash = crc32($kodeIso . $indeks);
        mt_srand(abs($hash));

        $beritaSimulasi = [
            ['judul' => 'Pertumbuhan ekspor {NEGARA} meningkat pesat kuartal ini', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
            ['judul' => 'Gangguan rantai pasok di {NEGARA} akibat cuaca ekstrem', 'sentimen' => 'negatif', 'keparahan' => 'tinggi'],
            ['judul' => 'Inflasi di {NEGARA} capai rekor tertinggi 5 tahun', 'sentimen' => 'negatif', 'keparahan' => 'sedang'],
            ['judul' => 'Investasi asing di {NEGARA} melonjak signifikan', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
            ['judul' => 'Krisis politik di {NEGARA} pengaruhi pasokan komoditas', 'sentimen' => 'negatif', 'keparahan' => 'kritis'],
            ['judul' => 'Pemulihan ekonomi {NEGARA} berjalan lebih cepat dari perkiraan', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
            ['judul' => 'Pelabuhan utama {NEGARA} alami kemacetan parah', 'sentimen' => 'negatif', 'keparahan' => 'sedang'],
            ['judul' => '{NEGARA} tandatangani perjanjian perdagangan baru', 'sentimen' => 'positif', 'keparahan' => 'rendah'],
        ];

        $pilihan = $beritaSimulasi[abs($hash) % count($beritaSimulasi)];
        $judul   = str_replace('{NEGARA}', $kodeIso, $pilihan['judul']);

        $sumber  = ['Reuters', 'Bloomberg', 'Financial Times', 'BBC', 'CNBC', 'AP News'];

        return new self(
            kodeIso:          strtoupper($kodeIso),
            judul:            $judul,
            ringkasan:        'Ringkasan berita simulasi untuk ' . $kodeIso . ' — Indeks: ' . $indeks,
            kategori:         'supply_chain',
            sentimen:         $pilihan['sentimen'],
            keparahan:        $pilihan['keparahan'],
            sumber:           $sumber[abs($hash) % count($sumber)],
            diterbitkanPada:  date('Y-m-d H:i:s', strtotime('-' . ($indeks * 6) . ' hours')),
            sumberApi:        'Simulasi',
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
        ];
    }
}
