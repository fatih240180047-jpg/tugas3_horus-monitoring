<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * Pengontrol Autentikasi Kustom
 *
 * Mengelola alur login dan logout pengguna platform.
 */
class PengontrolAutentikasi extends Controller
{
    /**
     * Tampilkan halaman form masuk (login).
     */
    public function tampilkanFormMasuk()
    {
        if (Auth::check()) {
            return redirect()->route('dasbor.indeks');
        }
        return view('autentikasi.masuk');
    }

    /**
     * Proses masuk (login) pengguna.
     */
    public function masuk(Request $request)
    {
        $kredensial = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Coba autentikasi
        if (Auth::attempt($kredensial, $request->boolean('ingat_saya'))) {
            $pengguna = Auth::user();

            // Periksa status keaktifan akun
            if (!$pengguna->status) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
                ]);
            }

            // Perbarui data login terakhir
            $pengguna->update([
                'last_login_at' => Carbon::now(),
            ]);

            // Catat log aktivitas audit log
            LogAktivitas::create([
                'pengguna_id'     => $pengguna->id,
                'modul'           => 'Autentikasi',
                'aksi'            => 'Masuk',
                'entitas'         => 'pengguna',
                'entitas_id'      => $pengguna->id,
                'nilai_lama'      => null,
                'nilai_baru'      => ['email' => $pengguna->email, 'last_login_at' => Carbon::now()->toIso8601String()],
                'alamat_ip'       => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'dibuat_pada'     => Carbon::now(),
            ]);

            $request->session()->regenerate();

            return redirect()->intended(route('dasbor.indeks'));
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    /**
     * Proses keluar (logout) pengguna.
     */
    public function keluar(Request $request)
    {
        $pengguna = Auth::user();

        if ($pengguna) {
            // Catat log audit sebelum logout
            LogAktivitas::create([
                'pengguna_id'     => $pengguna->id,
                'modul'           => 'Autentikasi',
                'aksi'            => 'Keluar',
                'entitas'         => 'pengguna',
                'entitas_id'      => $pengguna->id,
                'nilai_lama'      => null,
                'nilai_baru'      => null,
                'alamat_ip'       => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'dibuat_pada'     => Carbon::now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk.form')->with('sukses', 'Anda berhasil keluar dari sistem.');
    }
}
