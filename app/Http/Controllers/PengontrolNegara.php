<?php

namespace App\Http\Controllers;

use App\Models\Negara;
use App\Models\LogAktivitas;
use App\Repositories\Kontrak\RepositoriNegaraInterface;
use App\Repositories\Kontrak\RepositoriRisikoInterface;
use App\Services\Kontrak\LayananCuacaInterface;
use App\Services\Kontrak\LayananEkonomiInterface;
use App\Services\Kontrak\LayananNilaiTukarInterface;
use App\Services\Kontrak\LayananBeritaInterface;
use App\Services\Kontrak\MesinRisikoInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * Pengontrol Detail Negara
 */
class PengontrolNegara extends Controller
{
    public function __construct(
        protected RepositoriNegaraInterface $repositoriNegara,
        protected RepositoriRisikoInterface $repositoriRisiko,
        protected LayananCuacaInterface $layananCuaca,
        protected LayananEkonomiInterface $layananEkonomi,
        protected LayananNilaiTukarInterface $layananNilaiTukar,
        protected LayananBeritaInterface $layananBerita,
        protected MesinRisikoInterface $mesinRisiko
    ) {}

    /**
     * Tampilkan detail pemantauan satu negara.
     */
    public function tampilkan(string $kodeIso)
    {
        $negara = $this->repositoriNegara->temukanBerdasarkanKodeIso($kodeIso);

        if (!$negara) {
            abort(404, 'Negara tidak ditemukan dalam radar pemantauan platform.');
        }

        // Ambil data terbaru
        $cuacaTerkini   = $negara->cuacaTerkini();
        $ekonomiTerkini = $negara->indikatorEkonomi()->orderBy('tanggal_indikator', 'desc')->first();
        $nilaiTukarList = $negara->nilaiTukar()->orderBy('tanggal_berlaku', 'desc')->limit(10)->get();
        $nilaiTukarTerkini = $nilaiTukarList->first(); // Untuk insight SCM
        $beritaList     = $negara->artikelBerita()->orderBy('diterbitkan_pada', 'desc')->limit(10)->get();
        $risikoTerkini  = $this->repositoriRisiko->terkini($negara);
        $riwayatRisiko  = $this->repositoriRisiko->riwayat($negara, 15);

        // Prakiraan cuaca 7 hari ke depan (hari ini + 6 hari)
        $prakiraan7Hari = $negara->catatanCuaca()
            ->where('tanggal_observasi', '>=', date('Y-m-d'))
            ->orderBy('tanggal_observasi', 'asc')
            ->limit(7)
            ->get();

        return view('negara.tampilkan', compact(
            'negara',
            'cuacaTerkini',
            'ekonomiTerkini',
            'nilaiTukarList',
            'nilaiTukarTerkini',
            'beritaList',
            'risikoTerkini',
            'riwayatRisiko',
            'prakiraan7Hari'
        ));
    }

    /**
     * Sinkronisasi ulang manual intelijen negara & kalkulasi ulang risiko.
     */
    public function sinkronkan(Request $request, string $kodeIso)
    {
        $negara = $this->repositoriNegara->temukanBerdasarkanKodeIso($kodeIso);

        if (!$negara) {
            return back()->with('error', 'Negara tidak ditemukan.');
        }

        // Hanya Administrator dan Analis Risiko yang bisa trigger sinkronisasi
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->mempunyaiPeran('admin') && !Auth::user()->mempunyaiPeran('analis')) {
            abort(403, 'Tindakan ini membutuhkan otorisasi Administrator atau Analis.');
        }

        $tahunIni = (int) date('Y');

        // Jalankan semua sinkronisasi
        $this->layananCuaca->sinkronkan($negara);
        $this->layananEkonomi->sinkronkan($negara, $tahunIni);

        $mataUang = \App\Services\Implementasi\LayananNilaiTukar::dapatkanMataUangNegara($negara->kode_iso);
        $this->layananNilaiTukar->sinkronkan($negara, $mataUang);
        $this->layananBerita->sinkronkan($negara);

        // Kalkulasi ulang risiko menggunakan Risk Engine
        $penilaian = $this->mesinRisiko->hitung($negara);

        // Catat aktivitas log audit
        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'modul'       => 'Intelijen',
            'aksi'        => 'Sinkronisasi Manual',
            'entitas'     => 'negara',
            'entitas_id'  => $negara->id,
            'nilai_lama'  => null,
            'nilai_baru'  => ['skor_baru' => $penilaian->skor_total, 'level_baru' => $penilaian->level_risiko],
            'alamat_ip'   => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'dibuat_pada' => Carbon::now(),
        ]);

        return back()->with('sukses', "Intelijen untuk negara {$negara->nama} berhasil diperbarui! Skor Risiko saat ini: {$penilaian->skor_total} ({$penilaian->level_risiko}).");
    }
}
