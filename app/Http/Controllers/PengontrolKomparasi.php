<?php

namespace App\Http\Controllers;

use App\Models\Negara;
use App\Models\Pelabuhan;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use App\Services\Implementasi\LayananNilaiTukar;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Country Comparison Engine — Membandingkan indikator SCM dua negara secara paralel.
 */
class PengontrolKomparasi extends Controller
{
    public function __construct(
        protected RepositoriRisikoInterface $repositoriRisiko
    ) {}

    /**
     * Tampilkan halaman Mesin Komparasi Negara.
     */
    public function indeks()
    {
        $negaraList = Negara::where('status_pemantauan', true)
            ->orderBy('nama')
            ->get(['id', 'kode_iso', 'nama', 'kawasan']);

        return view('dasbor.komparasi', compact('negaraList'));
    }

    /**
     * API: Ambil data perbandingan dua negara (JSON).
     */
    public function apiBandingkan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'negara_a' => 'required|string|size:3',
            'negara_b' => 'required|string|size:3',
        ]);

        $negaraA = Negara::where('kode_iso', strtoupper($validated['negara_a']))->firstOrFail();
        $negaraB = Negara::where('kode_iso', strtoupper($validated['negara_b']))->firstOrFail();

        return response()->json([
            'negara_a' => $this->kumpulkanDataNegara($negaraA),
            'negara_b' => $this->kumpulkanDataNegara($negaraB),
        ]);
    }

    /**
     * Kumpulkan semua data intelijen SCM untuk satu negara.
     *
     * @return array<string, mixed>
     */
    private function kumpulkanDataNegara(Negara $negara): array
    {
        $risikoTerkini  = $this->repositoriRisiko->terkini($negara);
        $cuacaTerkini   = $negara->cuacaTerkini();
        $ekonomiTerkini = $negara->indikatorEkonomi()->orderBy('tanggal_indikator', 'desc')->first();
        $nilaiTukar     = $negara->nilaiTukar()->orderBy('tanggal_berlaku', 'desc')->limit(30)->get();
        $berita         = $negara->artikelBerita()->orderBy('diterbitkan_pada', 'desc')->limit(5)->get();
        $pelabuhans     = $negara->pelabuhans()->orderBy('tingkat_kepadatan', 'desc')->get();

        // Hitung metrik agregat
        $jmlBeritaNegatif = $berita->where('sentimen', 'negatif')->count();
        $rerataSuhu30H    = $negara->catatanCuaca()
            ->where('tanggal_observasi', '>=', now()->subDays(30)->toDateString())
            ->avg('suhu');
        $kepadatanPelabuhan = $pelabuhans->avg('tingkat_kepadatan');

        return [
            'profil' => [
                'nama'     => $negara->nama,
                'kode_iso' => $negara->kode_iso,
                'bendera'  => $negara->bendera,
                'kawasan'  => $negara->kawasan,
            ],
            'risiko' => $risikoTerkini ? [
                'skor'           => $risikoTerkini->skor_total,
                'level'          => $risikoTerkini->level_risiko,
                'skor_cuaca'     => $risikoTerkini->skor_cuaca     ?? 0,
                'skor_ekonomi'   => $risikoTerkini->skor_ekonomi   ?? 0,
                'skor_geopolitik'=> $risikoTerkini->skor_geopolitik ?? 0,
                'skor_operasional'=> $risikoTerkini->skor_operasional ?? 0,
            ] : null,
            'cuaca' => [
                'suhu'          => $cuacaTerkini?->suhu,
                'kondisi'       => $cuacaTerkini?->kondisi_cuaca,
                'angin'         => $cuacaTerkini?->kecepatan_angin,
                'rerata_30h'    => round((float)$rerataSuhu30H, 1),
                'insight'       => $cuacaTerkini?->insight_scm,
            ],
            'ekonomi' => [
                'inflasi'          => $ekonomiTerkini?->tingkat_inflasi,
                'pdb_miliar'       => $ekonomiTerkini?->pdb ? round($ekonomiTerkini->pdb / 1_000_000_000, 1) : null,
                'pengangguran'     => $ekonomiTerkini?->tingkat_pengangguran,
                'neraca_juta'      => $ekonomiTerkini?->neraca_perdagangan ? round($ekonomiTerkini->neraca_perdagangan / 1_000_000, 1) : null,
            ],
            'forex' => [
                'mata_uang'  => LayananNilaiTukar::dapatkanMataUangNegara($negara->kode_iso),
                'terkini'    => $nilaiTukar->first()?->nilai_tukar,
                'historis'   => $nilaiTukar->reverse()->values(),
                'insight'    => $nilaiTukar->first()?->insight_scm,
            ],
            'berita' => [
                'total'          => $berita->count(),
                'negatif'        => $jmlBeritaNegatif,
                'positif'        => $berita->where('sentimen', 'positif')->count(),
                'kritis'         => $berita->whereIn('keparahan', ['kritis', 'tinggi'])->count(),
                'daftar'         => $berita,
            ],
            'pelabuhan' => [
                'total'              => $pelabuhans->count(),
                'rerata_kepadatan'   => round((float)$kepadatanPelabuhan, 1),
                'pelabuhan_kritis'   => $pelabuhans->where('tingkat_kepadatan', '>=', 80)->count(),
                'daftar'             => $pelabuhans->take(5),
            ],
        ];
    }
}
