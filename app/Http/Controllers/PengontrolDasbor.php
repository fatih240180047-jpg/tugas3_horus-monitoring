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

        // 2. Ambil penilaian risiko terbaru dari setiap negara
        $risikoTerkini = $this->repositoriRisiko->semuaTerkini();

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

        // 5. Siapkan data untuk peta SIG Leaflet
        $dataPeta = $risikoTerkini->map(function ($item) {
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
                }
            ];
        });

        return view('dasbor.indeks', compact('statistik', 'dataPeta', 'beritaTerbaru', 'risikoTerkini'));
    }
}
