<?php

namespace App\Http\Controllers;

use App\Models\ArtikelBerita;
use App\Models\AnalisisArtikel;
use App\Models\Negara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pengontrol Analisis Artikel (Intelligence Analysis)
 *
 * Memungkinkan Analis Risiko & Manajer Pengadaan untuk memberikan
 * anotasi intelijen terhadap berita SCM yang telah diklasifikasikan AI.
 * Eksekutif & Admin dapat mereview dan menyetujui analisis.
 */
class PengontrolAnalisisArtikel extends Controller
{
    /**
     * Daftar berita dengan status analisis.
     */
    public function indeks(Request $request)
    {
        $query = ArtikelBerita::with(['negara', 'analisis.pengguna'])
            ->orderByDesc('diterbitkan_pada');

        // Filter berdasarkan status analisis
        if ($request->filled('status_analisis')) {
            if ($request->status_analisis === 'belum') {
                $query->doesntHave('analisis');
            } elseif ($request->status_analisis === 'draft') {
                $query->whereHas('analisis', fn($q) => $q->where('status', 'draft'));
            } elseif ($request->status_analisis === 'menunggu') {
                $query->whereHas('analisis', fn($q) => $q->where('status', 'menunggu_review'));
            } elseif ($request->status_analisis === 'disetujui') {
                $query->whereHas('analisis', fn($q) => $q->where('status', 'disetujui'));
            }
        }

        // Filter berdasarkan negara
        if ($request->filled('negara_id')) {
            $query->where('negara_id', $request->negara_id);
        }

        // Filter berdasarkan keparahan
        if ($request->filled('keparahan')) {
            $query->where('keparahan', $request->keparahan);
        }

        $artikelList = $query->paginate(20)->withQueryString();
        $semuaNegara = Negara::orderBy('nama')->pluck('nama', 'id');

        // Cek apakah user bisa membuat analisis
        $bisaAnalisis = Auth::user()->adalahSuperAdmin()
            || Auth::user()->adalahAdmin()
            || Auth::user()->adalahAnalisRisiko()
            || Auth::user()->adalahManajerPengadaan();

        // Cek apakah user bisa mereview/approve
        $bisaReview = Auth::user()->adalahSuperAdmin()
            || Auth::user()->adalahAdmin()
            || Auth::user()->adalahEksekutif();

        return view('analisis.indeks', compact('artikelList', 'semuaNegara', 'bisaAnalisis', 'bisaReview'));
    }

    /**
     * Tampilkan detail artikel + form analisis.
     */
    public function detail(ArtikelBerita $artikel)
    {
        $artikel->load(['negara', 'analisis.pengguna', 'analisis.penyetuju']);

        // Analisis milik user yang login (jika ada)
        $analisisSaya = $artikel->analisis->where('pengguna_id', Auth::id())->first();

        // Semua analisis oleh orang lain (visible ke reviewer)
        $semuaAnalisis = $artikel->analisis;

        $bisaAnalisis = Auth::user()->adalahSuperAdmin()
            || Auth::user()->adalahAdmin()
            || Auth::user()->adalahAnalisRisiko()
            || Auth::user()->adalahManajerPengadaan();

        $bisaReview = Auth::user()->adalahSuperAdmin()
            || Auth::user()->adalahAdmin()
            || Auth::user()->adalahEksekutif();

        return view('analisis.detail', compact(
            'artikel', 'analisisSaya', 'semuaAnalisis', 'bisaAnalisis', 'bisaReview'
        ));
    }

    /**
     * Simpan analisis baru atau update draft.
     */
    public function simpan(Request $request, ArtikelBerita $artikel)
    {
        $pengguna = Auth::user();

        if (!$pengguna->adalahAnalisRisiko() && !$pengguna->adalahManajerPengadaan()
            && !$pengguna->adalahAdmin() && !$pengguna->adalahSuperAdmin()) {
            abort(403, 'Hanya Analis Risiko dan Manajer Pengadaan yang dapat membuat analisis.');
        }

        $validated = $request->validate([
            'komentar_analis'       => 'required|string|min:50',
            'rekomendasi_tindakan'  => 'nullable|string',
            'tingkat_kepercayaan'   => 'required|in:rendah,sedang,tinggi',
            'dampak_scm'            => 'required|in:tidak_berdampak,minor,signifikan,kritis',
            'kirim_review'          => 'sometimes|boolean',
        ], [
            'komentar_analis.required' => 'Komentar analisis wajib diisi.',
            'komentar_analis.min'      => 'Komentar analisis minimal 50 karakter.',
        ]);

        $status = $request->boolean('kirim_review') ? 'menunggu_review' : 'draft';

        AnalisisArtikel::updateOrCreate(
            ['artikel_berita_id' => $artikel->id, 'pengguna_id' => $pengguna->id],
            [
                'komentar_analis'      => $validated['komentar_analis'],
                'rekomendasi_tindakan' => $validated['rekomendasi_tindakan'] ?? null,
                'tingkat_kepercayaan'  => $validated['tingkat_kepercayaan'],
                'dampak_scm'           => $validated['dampak_scm'],
                'status'               => $status,
            ]
        );

        $pesan = $status === 'menunggu_review'
            ? 'Analisis berhasil dikirim untuk review oleh Eksekutif/Admin.'
            : 'Analisis berhasil disimpan sebagai draft.';

        return redirect()->route('analisis.detail', $artikel->id)->with('sukses', $pesan);
    }

    /**
     * Setujui analisis (Eksekutif / Admin).
     */
    public function setujui(AnalisisArtikel $analisis)
    {
        if (!Auth::user()->adalahEksekutif() && !Auth::user()->adalahAdmin() && !Auth::user()->adalahSuperAdmin()) {
            abort(403, 'Hanya Eksekutif dan Administrator yang dapat menyetujui analisis.');
        }

        $analisis->update([
            'status'         => 'disetujui',
            'disetujui_oleh' => Auth::id(),
            'disetujui_pada' => now(),
        ]);

        return redirect()->route('analisis.detail', $analisis->artikel_berita_id)
            ->with('sukses', 'Analisis berhasil disetujui dan akan masuk ke laporan intelijen formal.');
    }

    /**
     * Tolak analisis dengan catatan reviewer.
     */
    public function tolak(Request $request, AnalisisArtikel $analisis)
    {
        if (!Auth::user()->adalahEksekutif() && !Auth::user()->adalahAdmin() && !Auth::user()->adalahSuperAdmin()) {
            abort(403, 'Hanya Eksekutif dan Administrator yang dapat menolak analisis.');
        }

        $request->validate(['catatan_reviewer' => 'required|string|min:10']);

        $analisis->update([
            'status'           => 'ditolak',
            'disetujui_oleh'   => Auth::id(),
            'disetujui_pada'   => now(),
            'catatan_reviewer' => $request->catatan_reviewer,
        ]);

        return redirect()->route('analisis.detail', $analisis->artikel_berita_id)
            ->with('sukses', 'Analisis telah ditolak. Analis dapat merevisi dan mengirim ulang.');
    }
}
