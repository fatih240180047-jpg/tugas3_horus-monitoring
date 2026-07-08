<?php

namespace App\DTO;

/**
 * DTO Ekonomi (Economy DTO)
 *
 * Mengisolasi struktur respons World Bank API dari domain bisnis.
 * Immutable setelah dibuat.
 *
 * Pipeline: HTTP Response → DtoEkonomi → Validasi → Normalisasi → Repository
 */
final class DtoEkonomi
{
    public function __construct(
        public readonly string $kodeIso,
        public readonly string $tanggalIndikator,   // Format: Y-m-d (biasanya Y-01-01)
        public readonly ?float $pdb,                // USD
        public readonly ?float $tingkatInflasi,     // Persen (%)
        public readonly ?float $tingkatPengangguran,// Persen (%)
        public readonly ?float $tingkatBunga,       // Persen (%)
        public readonly ?float $neracaPerdagangan,  // USD
        public readonly string $sumberApi,
    ) {}

    /**
     * Buat DtoEkonomi dari respons World Bank API.
     * World Bank mengembalikan array dua elemen: [metadata, data]
     *
     * @param array<int, mixed> $respons
     */
    public static function dariResponWorldBank(array $respons, string $kodeIso, int $tahun): self
    {
        // World Bank mengembalikan data dalam format: [[metadata], [data_array]]
        $data = $respons[1][0] ?? [];

        return new self(
            kodeIso:              strtoupper($kodeIso),
            tanggalIndikator:     $tahun . '-01-01',
            pdb:                  isset($data['value']) && $data['value'] !== null ? (float) $data['value'] : null,
            tingkatInflasi:       null, // Diisi terpisah per indikator
            tingkatPengangguran:  null,
            tingkatBunga:         null,
            neracaPerdagangan:    null,
            sumberApi:            'World Bank',
        );
    }

    /**
     * Buat DtoEkonomi dari data simulasi (Mock Mode).
     */
    public static function dariSimulasi(string $kodeIso, int $tahun): self
    {
        $hash = crc32($kodeIso . $tahun);
        mt_srand(abs($hash));

        return new self(
            kodeIso:              strtoupper($kodeIso),
            tanggalIndikator:     $tahun . '-01-01',
            pdb:                  round(mt_rand(50_000_000_000, 20_000_000_000_000) + mt_rand(0, 99) / 100, 2),
            tingkatInflasi:       round(mt_rand(0, 30) + mt_rand(0, 99) / 100, 2),
            tingkatPengangguran:  round(mt_rand(2, 25) + mt_rand(0, 99) / 100, 2),
            tingkatBunga:         round(mt_rand(0, 20) + mt_rand(0, 99) / 100, 2),
            neracaPerdagangan:    round((mt_rand(-500, 500) * 1_000_000_000) + mt_rand(0, 99) / 100, 2),
            sumberApi:            'Simulasi',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function keArray(): array
    {
        return [
            'tanggal_indikator'     => $this->tanggalIndikator,
            'pdb'                   => $this->pdb,
            'tingkat_inflasi'       => $this->tingkatInflasi,
            'tingkat_pengangguran'  => $this->tingkatPengangguran,
            'tingkat_bunga'         => $this->tingkatBunga,
            'neraca_perdagangan'    => $this->neracaPerdagangan,
        ];
    }
}
