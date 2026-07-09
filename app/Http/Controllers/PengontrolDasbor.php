<?php

namespace App\Http\Controllers;

use App\Repositories\Kontrak\RepositoriNegaraInterface;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use App\Repositories\Kontrak\RepositoriBeritaInterface;
use App\Models\ArtikelBerita;
use App\Models\Negara;
use Illuminate\Http\Request;

/**
 * Pengontrol Dasbor Utama
 */
class PengontrolDasbor extends Controller
{
    public function __construct(
        protected RepositoriNegaraInterface $repositoriNegara,
        protected RepositoriRisikoInterface $repositoriRisiko
    ) {}

    /**
     * Tampilkan halaman dasbor pemantauan utama.
     */
    public function indeks(Request $request)
    {
        // 1. Ambil semua negara aktif dipantau
        $negaraList = $this->repositoriNegara->semuaYangDipantau();

        // 2. Ambil penilaian risiko terbaru dari setiap negara, sertakan relasi cuaca & ekonomi terbaru
        $risikoTerkini = $this->repositoriRisiko->semuaTerkini()->load([
            'negara.catatanCuaca' => function ($q) { $q->latest('tanggal_observasi')->limit(1); },
            'negara.indikatorEkonomi' => function ($q) { $q->latest('tanggal_indikator')->limit(1); },
            'negara.artikelBerita' => function ($q) { $q->latest('diterbitkan_pada')->limit(1); }
        ]);

        // 3. Hitung ringkasan statistik status risiko
        $statistik = [
            'kritis' => $risikoTerkini->where('level_risiko', 'Kritis')->count(),
            'tinggi' => $risikoTerkini->where('level_risiko', 'Tinggi')->count(),
            'sedang' => $risikoTerkini->where('level_risiko', 'Sedang')->count(),
            'rendah' => $risikoTerkini->where('level_risiko', 'Rendah')->count(),
            'total'  => $risikoTerkini->count(),
        ];

        // 4. Ambil 5 berita global terbaru yang kritis/tinggi
        $beritaTerbaru = ArtikelBerita::with('negara')
            ->orderBy('diterbitkan_pada', 'desc')
            ->limit(5)
            ->get();

        // 5. Siapkan data untuk peta SIG Leaflet dengan SCM metrics
        $dataPeta = $risikoTerkini->map(function ($item) {
            $cuaca = $item->negara->catatanCuaca->first();
            $ekonomi = $item->negara->indikatorEkonomi->first();
            $berita = $item->negara->artikelBerita->first();

            return [
                'id'            => $item->negara->id,
                'nama'          => $item->negara->nama,
                'kode_iso'      => $item->negara->kode_iso,
                'lintang'       => $item->negara->lintang,
                'bujur'         => $item->negara->bujur,
                'skor_total'    => $item->skor_total,
                'level_risiko'  => $item->level_risiko,
                'warna'         => match ($item->level_risiko) {
                    'Kritis' => '#dc2626', // Crimson Red
                    'Tinggi' => '#f97316', // Orange
                    'Sedang' => '#eab308', // Yellow
                    default  => '#16a34a', // Green
                },
                'scm' => [
                    'cuaca' => $cuaca ? "{$cuaca->suhu}°C, " . ucfirst($cuaca->kondisi_cuaca) : 'Data N/A',
                    'inflasi' => $ekonomi ? "{$ekonomi->tingkat_inflasi}%" : 'Data N/A',
                    'berita' => $berita ? ucfirst($berita->sentimen) . " ({$berita->keparahan})" : 'Data N/A'
                ]
            ];
        });

        // 6. Buat Jaringan Rute Ekspedisi (Algoritmik)
        $ruteEkspedisi = [];
        $titikPeta = $dataPeta->keyBy('kode_iso');
        
        // Definisikan hub maritim dunia utama
        $hubUtama = ['SGP', 'CHN', 'USA', 'NLD', 'ARE'];
        
        foreach ($dataPeta as $titik) {
            if (!in_array($titik['kode_iso'], $hubUtama)) {
                // Hubungkan negara non-hub ke hub terdekat (simulasi rute)
                // Di sini kita random hub untuk simulasi perdagangan global
                $hubTujuan = $hubUtama[array_rand($hubUtama)];
                if (isset($titikPeta[$hubTujuan])) {
                    $ruteEkspedisi[] = [
                        'asal' => [$titik['lintang'], $titik['bujur']],
                        'tujuan' => [$titikPeta[$hubTujuan]['lintang'], $titikPeta[$hubTujuan]['bujur']],
                        'kode_asal' => $titik['kode_iso'],
                        'kode_tujuan' => $hubTujuan
                    ];
                }
            }
        }
        
        // Hubungkan antar Hub Utama
        for ($i=0; $i < count($hubUtama)-1; $i++) {
            if (isset($titikPeta[$hubUtama[$i]]) && isset($titikPeta[$hubUtama[$i+1]])) {
                $ruteEkspedisi[] = [
                    'asal' => [$titikPeta[$hubUtama[$i]]['lintang'], $titikPeta[$hubUtama[$i]]['bujur']],
                    'tujuan' => [$titikPeta[$hubUtama[$i+1]]['lintang'], $titikPeta[$hubUtama[$i+1]]['bujur']],
                    'kode_asal' => $hubUtama[$i],
                    'kode_tujuan' => $hubUtama[$i+1]
                ];
            }
        }

        return view('dasbor.indeks', compact('statistik', 'dataPeta', 'beritaTerbaru', 'risikoTerkini', 'ruteEkspedisi'));
    }
}
