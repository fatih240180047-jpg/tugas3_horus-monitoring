<?php

namespace App\Http\Controllers;

use App\Repositories\Kontrak\RepositoriNegaraInterface;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use App\Repositories\Kontrak\RepositoriBeritaInterface;
use App\Models\ArtikelBerita;
use App\Models\Negara;
use App\Models\Pelabuhan;
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

        // Data pelabuhan untuk marker peta
        $dataPelabuhan = Pelabuhan::with('negara')
            ->where('aktif', true)
            ->get()
            ->map(fn($p) => [
                'nama'             => $p->nama,
                'kode_locode'      => $p->kode_locode,
                'negara'           => $p->negara?->nama,
                'kode_iso'         => $p->negara?->kode_iso,
                'lintang'          => $p->lintang,
                'bujur'            => $p->bujur,
                'jenis'            => $p->jenis,
                'kapasitas_teu'    => $p->kapasitas_teu ? number_format($p->kapasitas_teu) : 'N/A',
                'tingkat_kepadatan'=> $p->tingkat_kepadatan,
                'label_kepadatan'  => $p->labelKepadatan(),
                'warna'            => $p->warnaMarker(),
                'operator'         => $p->operator,
                'skor_risiko'      => $p->skor_risiko,
            ]);

        // Ambil ID negara yang difavoritkan oleh user yang login
        $favoritIds = auth()->user()
            ? auth()->user()->negaraFavorit()->pluck('negara.id')->toArray()
            : [];

        return view('dasbor.indeks', compact('statistik', 'dataPeta', 'beritaTerbaru', 'risikoTerkini', 'ruteEkspedisi', 'negaraList', 'dataPelabuhan', 'favoritIds'));
    }

    /**
     * API Internal untuk menyuplai data ke Dashboard SPA (Sidebar Kanan).
     */
    public function apiDetailNegara(string $kodeIso)
    {
        $negara = Negara::where('kode_iso', strtoupper($kodeIso))->firstOrFail();
        
        $risikoTerkini  = $this->repositoriRisiko->terkini($negara);
        $cuacaTerkini   = $negara->cuacaTerkini();
        $ekonomiTerkini = $negara->indikatorEkonomi()->orderBy('tanggal_indikator', 'desc')->first();
        
        // 7 Hari Cuaca
        $prakiraan7Hari = $negara->catatanCuaca()
            ->where('tanggal_observasi', '>=', date('Y-m-d'))
            ->orderBy('tanggal_observasi', 'asc')
            ->limit(7)
            ->get();
            
        // Nilai Tukar 30 hari untuk chart
        $nilaiTukarList = $negara->nilaiTukar()->orderBy('tanggal_berlaku', 'desc')->limit(30)->get();
        $nilaiTukarTerkini = $nilaiTukarList->first();
        
        // 5 Berita Teratas
        $beritaList = $negara->artikelBerita()->orderBy('diterbitkan_pada', 'desc')->limit(5)->get();

        return response()->json([
            'negara' => [
                'id'       => $negara->id,
                'nama'     => $negara->nama,
                'kode_iso' => $negara->kode_iso,
                'bendera'  => $negara->bendera,
            ],
            'risiko' => $risikoTerkini ? [
                'skor' => $risikoTerkini->skor_total,
                'level' => $risikoTerkini->level_risiko
            ] : null,
            'cuaca' => [
                'terkini' => $cuacaTerkini,
                'prakiraan' => $prakiraan7Hari,
                'insight' => $cuacaTerkini?->insight_scm
            ],
            'ekonomi' => $ekonomiTerkini,
            'forex' => [
                'mata_uang' => \App\Services\Implementasi\LayananNilaiTukar::dapatkanMataUangNegara($negara->kode_iso),
                'terkini' => $nilaiTukarTerkini,
                'insight' => $nilaiTukarTerkini?->insight_scm,
                'history' => $nilaiTukarList->reverse()->values() // Untuk chart (lama -> baru)
            ],
            'berita' => $beritaList
        ]);
    }
}
