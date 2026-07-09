<?php

use App\Http\Controllers\PengontrolAutentikasi;
use App\Http\Controllers\PengontrolDasbor;
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
    
    // API Interaktif Dasbor (AJAX)
    Route::get('/api/negara/{kode_iso}', [PengontrolDasbor::class, 'apiDetailNegara'])->name('api.negara.detail');

    // Detail & Sinkronisasi Negara
    Route::get('/negara/{kode_iso}', [PengontrolNegara::class, 'tampilkan'])->name('negara.tampilkan');
    
    Route::post('/negara/{kode_iso}/sinkronkan', [PengontrolNegara::class, 'sinkronkan'])
        ->name('negara.sinkronkan')
        ->middleware('verifikasi_peran:admin,analis');

    // Pengaturan Parameter Bobot Risiko (Akses Khusus Admin)
    Route::get('/risiko/bobot', [PengontrolRisiko::class, 'tampilkanBobot'])
        ->name('risiko.bobot.form')
        ->middleware('verifikasi_peran:admin');
        
    Route::post('/risiko/bobot', [PengontrolRisiko::class, 'simpanBobot'])
        ->name('risiko.bobot.simpan')
        ->middleware('verifikasi_peran:admin');

    // Manajemen Tindakan Mitigasi
    Route::get('/risiko/rekomendasi', [PengontrolRisiko::class, 'indeksRekomendasi'])
        ->name('risiko.rekomendasi.indeks');
        
    Route::post('/risiko/rekomendasi/{id}/tangani', [PengontrolRisiko::class, 'tanganiRekomendasi'])
        ->name('risiko.rekomendasi.tangani')
        ->middleware('verifikasi_peran:admin,pengadaan');
});
