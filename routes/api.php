<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengontrolApiSistem;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// SCM REST API Endpoints (Dilindungi Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/countries', [PengontrolApiSistem::class, 'ambilSemuaNegara']);
    Route::get('/risk', [PengontrolApiSistem::class, 'ambilSemuaRisiko']);
    Route::get('/ports', [PengontrolApiSistem::class, 'ambilSemuaPelabuhan']);
    Route::get('/news', [PengontrolApiSistem::class, 'ambilSemuaBerita']);
    Route::get('/currency', [PengontrolApiSistem::class, 'ambilSemuaKurs']);
});
