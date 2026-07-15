<?php

namespace App\Http\Controllers;

use App\Models\Negara;
use App\Models\FavoritNegara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pengontrol Favorit Monitoring List
 *
 * Mengelola daftar bookmark negara pilihan pengguna.
 */
class PengontrolFavorit extends Controller
{
    /**
     * Tampilkan daftar negara yang difavoritkan beserta status real-time.
     */
    public function indeks(Request $request)
    {
        $pengguna = Auth::user();

        // Tarik negara favorit dengan data intelijen terbaru
        $favoritList = $pengguna->negaraFavorit()
            ->with([
                'catatanCuaca' => function ($q) { $q->latest('tanggal_observasi')->limit(1); },
                'indikatorEkonomi' => function ($q) { $q->latest('tanggal_indikator')->limit(1); },
                'nilaiTukar' => function ($q) { $q->latest('tanggal_berlaku')->limit(1); },
                'artikelBerita' => function ($q) { $q->latest('diterbitkan_pada')->limit(1); },
                'penilaianRisiko' => function ($q) { $q->latest('dihitung_pada')->limit(1); }
            ])
            ->get();

        return view('favorit.indeks', compact('favoritList'));
    }

    /**
     * Tambah atau hapus negara dari daftar favorit (Toggle via AJAX).
     */
    public function toggle(Request $request, Negara $negara)
    {
        $pengguna = Auth::user();

        $favorit = FavoritNegara::where('pengguna_id', $pengguna->id)
            ->where('negara_id', $negara->id)
            ->first();

        if ($favorit) {
            $favorit->delete();
            $action = 'removed';
            $message = 'Negara berhasil dihapus dari daftar pantauan favorit.';
        } else {
            FavoritNegara::create([
                'pengguna_id' => $pengguna->id,
                'negara_id' => $negara->id,
            ]);
            $action = 'added';
            $message = 'Negara berhasil ditambahkan ke daftar pantauan favorit.';
        }

        $totalFavorit = FavoritNegara::where('pengguna_id', $pengguna->id)->count();

        return response()->json([
            'status' => 'success',
            'action' => $action,
            'message' => $message,
            'total' => $totalFavorit
        ]);
    }
}
