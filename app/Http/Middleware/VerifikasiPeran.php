<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware VerifikasiPeran
 *
 * Memastikan pengguna yang terautentikasi memiliki setidaknya
 * salah satu peran yang diizinkan untuk mengakses rute.
 */
class VerifikasiPeran
{
    /**
     * Jalankan middleware.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$peranSlug  Daftar slug peran yang diperbolehkan
     */
    public function handle(Request $request, Closure $next, ...$peranSlug): Response
    {
        if (!Auth::check()) {
            return redirect()->route('masuk.form');
        }

        $pengguna = Auth::user();

        // Super Admin selalu memiliki akses penuh ke semua rute
        if ($pengguna->adalahSuperAdmin()) {
            return $next($request);
        }

        // Cek kecocokan peran
        foreach ($peranSlug as $slug) {
            if ($pengguna->mempunyaiPeran($slug)) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki wewenang peran yang sesuai untuk membuka halaman ini.');
    }
}
