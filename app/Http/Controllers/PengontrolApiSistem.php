<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Negara;
use App\Models\PenilaianRisiko;
use App\Models\Pelabuhan;
use App\Models\ArtikelBerita;
use App\Models\NilaiTukar;

/**
 * Controller untuk menangani endpoint REST API (JSON).
 * Dilindungi oleh Laravel Sanctum Token.
 */
class PengontrolApiSistem extends Controller
{
    /**
     * GET /api/countries
     * Menampilkan profil semua negara beserta data makronya.
     */
    public function ambilSemuaNegara()
    {
        $negaraList = Negara::with(['indikatorEkonomi' => function($q) {
            $q->orderBy('tanggal_indikator', 'desc')->limit(1);
        }])->get();

        return response()->json([
            'status' => 'success',
            'total' => $negaraList->count(),
            'data' => $negaraList
        ]);
    }

    /**
     * GET /api/risk
     * Menampilkan daftar skor risiko terbaru (Supply Chain Risk Prediction).
     */
    public function ambilSemuaRisiko()
    {
        // Ambil penilaian risiko terakhir untuk tiap negara
        $risikoList = PenilaianRisiko::with('negara:id,nama,kode_iso')
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                      ->from('penilaian_risiko')
                      ->groupBy('negara_id');
            })->get();

        return response()->json([
            'status' => 'success',
            'total' => $risikoList->count(),
            'data' => $risikoList
        ]);
    }

    /**
     * GET /api/ports
     * Menampilkan daftar pelabuhan hub maritim global.
     */
    public function ambilSemuaPelabuhan()
    {
        $pelabuhanList = Pelabuhan::with('negara:id,nama,kode_iso')->get();

        return response()->json([
            'status' => 'success',
            'total' => $pelabuhanList->count(),
            'data' => $pelabuhanList
        ]);
    }

    /**
     * GET /api/news
     * Menampilkan data base / cache dari berita yang telah diambil dari GNews.
     * Sudah termasuk hasil Sentiment Analysis secara real-time.
     */
    public function ambilSemuaBerita(Request $request)
    {
        $limit = $request->query('limit', 50);

        $beritaList = ArtikelBerita::with('negara:id,nama,kode_iso')
            ->orderBy('diterbitkan_pada', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'total' => $beritaList->count(),
            'data' => $beritaList
        ]);
    }

    /**
     * GET /api/currency
     * Menampilkan riwayat nilai tukar mata uang global ke USD.
     */
    public function ambilSemuaKurs(Request $request)
    {
        $limit = $request->query('limit', 100);

        $kursList = NilaiTukar::with('negara:id,nama,kode_iso')
            ->orderBy('tanggal_berlaku', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'total' => $kursList->count(),
            'data' => $kursList
        ]);
    }
}
