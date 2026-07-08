<?php

namespace App\DTO;

/**
 * DTO Cuaca (Weather DTO)
 *
 * Mengisolasi struktur respons OpenWeather API dari domain bisnis.
 * Immutable setelah dibuat — tidak boleh ada mutasi setelah konstruksi.
 *
 * Pipeline: HTTP Response → DtoCuaca → Validasi → Normalisasi → Repository
 */
final class DtoCuaca
{
    public function __construct(
        public readonly string  $kodeIso,
        public readonly string  $tanggalObservasi,    // Format: Y-m-d
        public readonly ?float  $suhu,                // Celsius
        public readonly ?float  $kelembaban,          // Persen (%)
        public readonly ?float  $curahHujan,          // Milimeter
        public readonly ?float  $kecepatanAngin,      // km/jam
        public readonly ?string $kondisiCuaca,        // Deskripsi teks
        public readonly string  $sumberApi,           // Nama provider
    ) {}

    /**
     * Buat DtoCuaca dari respons OpenWeather API.
     *
     * @param array<string, mixed> $data Respons JSON dari API
     */
    public static function dariResponOpenWeather(array $data, string $kodeIso): self
    {
        $cuaca = $data['weather'][0]['description'] ?? null;
        $angin = isset($data['wind']['speed'])
            ? round($data['wind']['speed'] * 3.6, 2) // m/s → km/jam
            : null;

        return new self(
            kodeIso:          strtoupper($kodeIso),
            tanggalObservasi: date('Y-m-d'),
            suhu:             isset($data['main']['temp'])     ? round($data['main']['temp'], 2)     : null,
            kelembaban:       isset($data['main']['humidity']) ? (float) $data['main']['humidity']   : null,
            curahHujan:       isset($data['rain']['1h'])       ? round($data['rain']['1h'], 2)        : 0.0,
            kecepatanAngin:   $angin,
            kondisiCuaca:     $cuaca,
            sumberApi:        'OpenWeather',
        );
    }

    /**
     * Buat DtoCuaca dari data simulasi (Mock Mode).
     */
    public static function dariSimulasi(string $kodeIso, string $tanggal): self
    {
        $hash = crc32($kodeIso . $tanggal);
        mt_srand(abs($hash));

        $kondisi = ['Cerah', 'Berawan', 'Hujan Ringan', 'Hujan Lebat', 'Badai', 'Berkabut', 'Berangin'];

        return new self(
            kodeIso:          strtoupper($kodeIso),
            tanggalObservasi: $tanggal,
            suhu:             round(mt_rand(10, 40) + mt_rand(0, 99) / 100, 2),
            kelembaban:       round(mt_rand(30, 95) + mt_rand(0, 99) / 100, 2),
            curahHujan:       round(mt_rand(0, 200) + mt_rand(0, 99) / 100, 2),
            kecepatanAngin:   round(mt_rand(0, 120) + mt_rand(0, 99) / 100, 2),
            kondisiCuaca:     $kondisi[abs($hash) % count($kondisi)],
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
            'kelembaban'        => $this->kelembaban,
            'curah_hujan'       => $this->curahHujan,
            'kecepatan_angin'   => $this->kecepatanAngin,
            'kondisi_cuaca'     => $this->kondisiCuaca,
            'sumber_api'        => $this->sumberApi,
        ];
    }
}
