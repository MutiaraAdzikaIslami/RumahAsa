<?php

use App\Modules\Survei\Http\Controllers\SurveiController;
use App\Modules\Survei\Http\Controllers\SurveiFotoController;
use Illuminate\Support\Facades\Route;

// RBAC: Hanya Surveyor yang boleh mengisi survei (FR-11a)
Route::middleware('role:surveyor')->group(function () {
    Route::get('/rumah/{rumah}/survei',         [SurveiController::class, 'create'])->name('create');
    Route::put('/rumah/{rumah}/survei/{survei}', [SurveiController::class, 'update'])->name('update');

    // Foto survei komponen
    Route::post('/survei-komponen/{komponen}/foto', [SurveiFotoController::class, 'store'])->name('foto.store');
    Route::delete('/survei-foto/{foto}',             [SurveiFotoController::class, 'destroy'])->name('foto.destroy');
});

// Lihat foto: semua role yang sudah auth boleh melihat bukti foto survei
Route::get('/survei-foto/{foto}', [SurveiFotoController::class, 'show'])->name('foto.show');