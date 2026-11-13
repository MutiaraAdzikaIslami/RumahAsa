<?php

use App\Modules\Verifikator\Http\Controllers\VerifikasiSurveiController;
use Illuminate\Support\Facades\Route;

Route::get('/survei',                             [VerifikasiSurveiController::class, 'index'])->name('survei.index');
Route::get('/survei/{rumah}',                      [VerifikasiSurveiController::class, 'show'])->name('survei.show');
Route::post('/survei/{rumah}/minta-perbaikan',     [VerifikasiSurveiController::class, 'mintaPerbaikan'])->name('survei.minta-perbaikan');
Route::post('/survei/{rumah}/tetapkan-kategori',   [VerifikasiSurveiController::class, 'tetapkanKategori'])->name('survei.tetapkan-kategori');
