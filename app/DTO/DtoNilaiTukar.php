<?php

namespace App\DTO;

/**
 * DTO Nilai Tukar (Exchange Rate DTO)
 *
 * Mengisolasi struktur respons ExchangeRate API dari domain bisnis.
 * Immutable setelah dibuat.
 */
final class DtoNilaiTukar
{
    public function __construct(
        public readonly string $kodeIso,
        public readonly string $kodeMataUang,   // ISO 4217, contoh: IDR
        public readonly float  $nilaiTukar,     // Terhadap USD
        public readonly string $tanggalBerlaku, // Format: Y-m-d
        public readonly string $sumberApi,
    ) {}

    /**
     * Buat koleksi DtoNilaiTukar dari respons ExchangeRate API.
     * Satu respons API mengandung banyak mata uang sekaligus.
     *
     * @param array<string, mixed> $data
     * @return DtoNilaiTukar[]
     */
    public static function koleksiDariRespon(array $data, string $kodeIso): array
    {
        $tanggal = date('Y-m-d');
        $hasil   = [];

        $rates = $data['conversion_rates'] ?? $data['rates'] ?? [];
        foreach ($rates as $kodeMataUang => $nilai) {
            $hasil[] = new self(
                kodeIso:        strtoupper($kodeIso),
                kodeMataUang:   strtoupper((string) $kodeMataUang),
                nilaiTukar:     (float) $nilai,
                tanggalBerlaku: $tanggal,
                sumberApi:      'ExchangeRate API',
            );
        }

        return $hasil;
    }

    /**
     * Buat DtoNilaiTukar tunggal dari simulasi.
     */
    public static function dariSimulasi(string $kodeIso, string $kodeMataUang, string $tanggal): self
    {
        $hash = crc32($kodeIso . $kodeMataUang . $tanggal);
        mt_srand(abs($hash));

        // Rentang nilai tukar realistis per mata uang
        $rentang = match (strtoupper($kodeMataUang)) {
            'IDR'   => [14000, 16500],
            'JPY'   => [130, 160],
            'EUR'   => [0.88, 0.95],
            'GBP'   => [0.75, 0.85],
            'CNY'   => [6.8, 7.3],
            'SGD'   => [1.30, 1.40],
            'MYR'   => [4.3, 4.8],
            'THB'   => [33, 37],
            'BRL'   => [4.8, 5.5],
            'INR'   => [82, 87],
            default => [0.5, 5.0],
        };

        $nilai = $rentang[0] + (mt_rand() / mt_getrandmax()) * ($rentang[1] - $rentang[0]);

        return new self(
            kodeIso:        strtoupper($kodeIso),
            kodeMataUang:   strtoupper($kodeMataUang),
            nilaiTukar:     round($nilai, 6),
            tanggalBerlaku: $tanggal,
            sumberApi:      'Simulasi',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function keArray(): array
    {
        return [
            'kode_mata_uang'  => $this->kodeMataUang,
            'nilai_tukar'     => $this->nilaiTukar,
            'tanggal_berlaku' => $this->tanggalBerlaku,
            'sumber_api'      => $this->sumberApi,
        ];
    }
}
