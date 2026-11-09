<?php

use App\Modules\Survei\Http\Controllers\SurveiController;
use App\Modules\Survei\Http\Controllers\SurveiFotoController;
use Illuminate\Support\Facades\Route;

Route::get('/rumah/{rumah}/survei',           [SurveiController::class, 'create'])->name('create');
Route::put('/rumah/{rumah}/survei/{survei}',   [SurveiController::class, 'update'])->name('update');

// Foto routes
Route::post('/survei-komponen/{komponen}/foto', [SurveiFotoController::class, 'store'])->name('foto.store');
Route::get('/survei-foto/{foto}',               [SurveiFotoController::class, 'show'])->name('foto.show');
Route::delete('/survei-foto/{foto}',             [SurveiFotoController::class, 'destroy'])->name('foto.destroy');