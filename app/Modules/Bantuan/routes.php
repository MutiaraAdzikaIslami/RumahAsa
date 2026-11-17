<?php

use App\Modules\Bantuan\Http\Controllers\BantuanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BantuanController::class, 'index'])->middleware('role:verifikator|admin|pimpinan')->name('index');

// Hanya Verifikator yang boleh mencatat penyaluran
Route::middleware('role:verifikator')->group(function () {
    Route::get('/{rumah}/salurkan',  [\App\Modules\Bantuan\Http\Controllers\PenyaluranController::class, 'create'])->name('salurkan.create');
    Route::post('/{rumah}/salurkan', [\App\Modules\Bantuan\Http\Controllers\PenyaluranController::class, 'store'])->name('salurkan.store');
});

// Progres renovasi: dapat dilihat oleh semua peran berwenang
Route::get('/{rumah}/progres', [\App\Modules\Bantuan\Http\Controllers\ProgresRenovasiController::class, 'index'])->name('progres.index');
Route::get('/foto/{foto}',     [\App\Modules\Bantuan\Http\Controllers\ProgresRenovasiController::class, 'showFoto'])->name('progres.foto');

// Perubahan progres renovasi: Surveyor dan Verifikator (FR-22)
Route::middleware('role:surveyor|verifikator')->group(function () {
    $c = \App\Modules\Bantuan\Http\Controllers\ProgresRenovasiController::class;
    Route::get('/{rumah}/progres/create',          [$c, 'create'])->name('progres.create');
    Route::post('/{rumah}/progres',                [$c, 'store'])->name('progres.store');
    Route::get('/{rumah}/progres/{progres}/edit',  [$c, 'edit'])->name('progres.edit');
    Route::put('/{rumah}/progres/{progres}',       [$c, 'update'])->name('progres.update');
    Route::delete('/{rumah}/progres/{progres}',    [$c, 'destroy'])->name('progres.destroy');
});
