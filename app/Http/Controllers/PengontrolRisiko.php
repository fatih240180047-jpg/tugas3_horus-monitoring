<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Models\RekomendasiRisiko;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * Pengontrol Manajemen Risiko
 */
class PengontrolRisiko extends Controller
{
    /**
     * Tampilkan halaman pengelolaan bobot risiko (Hanya Administrator).
     */
    public function tampilkanBobot()
    {
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->mempunyaiPeran('admin')) {
            abort(403, 'Akses terbatas untuk Administrator saja.');
        }

        $daftarBobot = Pengaturan::where('kategori', 'risiko')
            ->where('kunci', 'like', 'risiko.bobot_%')
            ->get();

        $ambangBatas = Pengaturan::where('kategori', 'risiko')
            ->where('kunci', 'like', 'risiko.ambang_%')
            ->get();

        return view('risiko.bobot', compact('daftarBobot', 'ambangBatas'));
    }

    /**
     * Simpan pembaruan bobot risiko.
     */
    public function simpanBobot(Request $request)
    {
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->mempunyaiPeran('admin')) {
            abort(403);
        }

        $kunciValidasi = [];
        $daftarBobot = Pengaturan::where('kategori', 'risiko')->where('kunci', 'like', 'risiko.bobot_%')->get();

        foreach ($daftarBobot as $bobot) {
            $kunciValidasi[$bobot->kunci] = 'required|integer|min:0|max:100';
        }

        $request->validate($kunciValidasi);

        // Pastikan total bobot adalah 100%
        $total = 0;
        foreach ($daftarBobot as $bobot) {
            $total += (int) $request->input(str_replace('.', '_', $bobot->kunci));
        }

        if ($total !== 100) {
            return back()->withErrors(['total_bobot' => "Jumlah seluruh bobot komponen harus tepat 100%. Total saat ini: {$total}%."])->withInput();
        }

        // Mulai pembaruan
        $nilaiLama = [];
        $nilaiBaru = [];

        foreach ($daftarBobot as $bobot) {
            $inputKey = str_replace('.', '_', $bobot->kunci);
            $nilaiLama[$bobot->kunci] = $bobot->nilai;
            $nilaiBaru[$bobot->kunci] = $request->input($inputKey);

            $bobot->update([
                'nilai' => $request->input($inputKey),
            ]);
        }

        // Catat aktivitas log audit
        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'modul'       => 'Risiko',
            'aksi'        => 'Ubah Bobot Risiko',
            'entitas'     => 'pengaturan',
            'entitas_id'  => 0,
            'nilai_lama'  => $nilaiLama,
            'nilai_baru'  => $nilaiBaru,
            'alamat_ip'   => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'dibuat_pada' => Carbon::now(),
        ]);

        return redirect()->route('risiko.bobot.form')->with('sukses', 'Bobot komponen risiko berhasil diperbarui!');
    }

    /**
     * Tampilkan semua rekomendasi mitigasi.
     */
    public function indeksRekomendasi(Request $request)
    {
        $status = $request->input('status', 'Tertunda');

        $rekomendasi = RekomendasiRisiko::with('penilaianRisiko.negara')
            ->when($status, function ($query, $st) {
                return $query->where('status', $st);
            })
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        return view('risiko.rekomendasi', compact('rekomendasi', 'status'));
    }

    /**
     * Selesaikan atau tangani rekomendasi (Hanya Manajer Pengadaan / Admin).
     */
    public function tanganiRekomendasi(Request $request, int $id)
    {
        $rekomendasi = RekomendasiRisiko::findOrFail($id);

        // Hanya Manajer Pengadaan atau Admin yang boleh menyelesaikan rekomendasi
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->mempunyaiPeran('pengadaan') && !Auth::user()->mempunyaiPeran('admin')) {
            abort(403, 'Akses terbatas untuk Manajer Pengadaan atau Administrator.');
        }

        $request->validate([
            'tindakan_diambil' => 'required|string|min:5|max:1000',
        ]);

        $rekomendasi->update([
            'status'           => 'Diselesaikan',
            'tindakan_diambil' => $request->input('tindakan_diambil'),
            'diselesaikan_oleh'=> Auth::id(),
            'diselesaikan_pada'=> Carbon::now(),
        ]);

        // Catat aktivitas log audit
        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'modul'       => 'Risiko',
            'aksi'        => 'Penyelesaian Rekomendasi',
            'entitas'     => 'rekomendasi_risiko',
            'entitas_id'  => $rekomendasi->id,
            'nilai_lama'  => ['status' => 'Tertunda'],
            'nilai_baru'  => ['status' => 'Diselesaikan', 'tindakan' => $rekomendasi->tindakan_diambil],
            'alamat_ip'   => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'dibuat_pada' => Carbon::now(),
        ]);

        return back()->with('sukses', 'Mitigasi risiko berhasil diselesaikan!');
    }
}
