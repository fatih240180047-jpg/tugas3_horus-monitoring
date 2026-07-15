<?php

use App\Http\Controllers\PengontrolAutentikasi;
use App\Http\Controllers\PengontrolDasbor;
use App\Http\Controllers\PengontrolKomparasi;
use App\Http\Controllers\PengontrolNegara;
use App\Http\Controllers\PengontrolRisiko;
use Illuminate\Support\Facades\Route;

// ============================================================
// Rute Tamu (Guest)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [PengontrolAutentikasi::class, 'tampilkanFormMasuk'])->name('masuk.form');
    Route::post('/masuk', [PengontrolAutentikasi::class, 'masuk'])->name('masuk.submit');
});

// Redirect root ke dasbor jika masuk, atau form login jika belum
Route::get('/', function () {
    return redirect()->route('dasbor.indeks');
});

// ============================================================
// Rute Autentikasi (Terlindungi)
// ============================================================
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [PengontrolAutentikasi::class, 'keluar'])->name('keluar');

    // Dasbor Utama
    Route::get('/dasbor', [PengontrolDasbor::class, 'indeks'])->name('dasbor.indeks');

    // Favorite Monitoring List (Bookmark Negara)
    Route::get('/favorit', [\App\Http\Controllers\PengontrolFavorit::class, 'indeks'])->name('favorit.indeks');
    Route::post('/favorit/{negara}/toggle', [\App\Http\Controllers\PengontrolFavorit::class, 'toggle'])->name('favorit.toggle');
    
    // API Interaktif Dasbor (AJAX)
    Route::get('/api/negara/{kode_iso}', [PengontrolDasbor::class, 'apiDetailNegara'])->name('api.negara.detail');

    // Country Comparison Engine
    Route::get('/komparasi', [PengontrolKomparasi::class, 'indeks'])->name('komparasi.indeks');
    Route::post('/api/komparasi', [PengontrolKomparasi::class, 'apiBandingkan'])->name('api.komparasi');

    // Detail & Sinkronisasi Negara
    Route::get('/negara/{kode_iso}', [PengontrolNegara::class, 'tampilkan'])->name('negara.tampilkan');
    
    Route::post('/negara/{kode_iso}/sinkronkan', [PengontrolNegara::class, 'sinkronkan'])
        ->name('negara.sinkronkan')
        ->middleware('verifikasi_peran:administrator,analis-risiko');

    // Pengaturan Parameter Bobot Risiko (Akses Khusus Admin)
    Route::get('/risiko/bobot', [PengontrolRisiko::class, 'tampilkanBobot'])
        ->name('risiko.bobot.form')
        ->middleware('verifikasi_peran:administrator');
        
    Route::post('/risiko/bobot', [PengontrolRisiko::class, 'simpanBobot'])
        ->name('risiko.bobot.simpan')
        ->middleware('verifikasi_peran:administrator');

    // Manajemen Tindakan Mitigasi
    Route::get('/risiko/rekomendasi', [PengontrolRisiko::class, 'indeksRekomendasi'])
        ->name('risiko.rekomendasi.indeks');
        
    Route::post('/risiko/rekomendasi/{id}/tangani', [PengontrolRisiko::class, 'tanganiRekomendasi'])
        ->name('risiko.rekomendasi.tangani')
        ->middleware('verifikasi_peran:administrator,manajer-pengadaan');

    // ================================================================
    // ANALISIS ARTIKEL — Semua peran analitis dapat mengakses
    // ================================================================
    Route::get('/analisis', [\App\Http\Controllers\PengontrolAnalisisArtikel::class, 'indeks'])->name('analisis.indeks');
    Route::get('/analisis/{artikel}', [\App\Http\Controllers\PengontrolAnalisisArtikel::class, 'detail'])->name('analisis.detail');
    Route::post('/analisis/{artikel}/simpan', [\App\Http\Controllers\PengontrolAnalisisArtikel::class, 'simpan'])->name('analisis.simpan');
    Route::post('/analisis/{analisis}/setujui', [\App\Http\Controllers\PengontrolAnalisisArtikel::class, 'setujui'])->name('analisis.setujui');
    Route::post('/analisis/{analisis}/tolak', [\App\Http\Controllers\PengontrolAnalisisArtikel::class, 'tolak'])->name('analisis.tolak');

    // ================================================================
    // ADMIN — CRUD Manajemen Pengguna (Admin & Super Admin saja)
    // ================================================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/pengguna', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'indeks'])->name('pengguna.indeks');
        Route::get('/pengguna/buat', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'buat'])->name('pengguna.buat');
        Route::post('/pengguna', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'simpan'])->name('pengguna.simpan');
        Route::get('/pengguna/{id}/edit', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{id}', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'perbarui'])->name('pengguna.perbarui');
        Route::delete('/pengguna/{id}', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'hapus'])->name('pengguna.hapus');
        Route::post('/pengguna/{id}/pulihkan', [\App\Http\Controllers\PengontrolAdminPengguna::class, 'pulihkan'])->name('pengguna.pulihkan');
    });
});
