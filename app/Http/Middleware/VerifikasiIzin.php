<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware VerifikasiIzin
 *
 * Memastikan pengguna memiliki izin tertentu sebelum mengakses rute.
 */
class VerifikasiIzin
{
    /**
     * Jalankan middleware.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $izinSlug  Slug izin spesifik
     */
    public function handle(Request $request, Closure $next, string $izinSlug): Response
    {
        if (!Auth::check()) {
            return redirect()->route('masuk.form');
        }

        $pengguna = Auth::user();

        // Super Admin bebas akses
        if ($pengguna->adalahSuperAdmin()) {
            return $next($request);
        }

        // Cek izin
        if ($pengguna->mempunyaiIzin($izinSlug)) {
            return $next($request);
        }

        abort(403, "Akses ditolak. Anda tidak memiliki izin [{$izinSlug}] untuk melakukan tindakan ini.");
    }
}
