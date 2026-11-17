<?php

use App\Modules\Survei\Http\Controllers\SurveiController;
use App\Modules\Survei\Http\Controllers\SurveiFotoController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:surveyor')->group(function () {
    Route::get('/rumah/{rumah}/survei',             [SurveiController::class, 'create'])->name('create');
    Route::put('/rumah/{rumah}/survei/{survei}',     [SurveiController::class, 'update'])->name('update');

    // Foto routes (upload & hapus hanya surveyor)
    Route::post('/survei-komponen/{komponen}/foto', [SurveiFotoController::class, 'store'])->name('foto.store');
    Route::delete('/survei-foto/{foto}',             [SurveiFotoController::class, 'destroy'])->name('foto.destroy');
});

// Sajikan foto (view) bisa diakses terautentikasi (verifikator review foto, surveyor cek foto)
Route::get('/survei-foto/{foto}',                   [SurveiFotoController::class, 'show'])->name('foto.show');