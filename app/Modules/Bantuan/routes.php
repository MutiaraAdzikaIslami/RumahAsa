<?php

use App\Modules\Bantuan\Http\Controllers\BantuanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BantuanController::class, 'index'])->name('index');

// Hanya Verifikator yang boleh mencatat penyaluran
Route::middleware('role:verifikator')->group(function () {
    Route::get('/{rumah}/salurkan',  [\App\Modules\Bantuan\Http\Controllers\PenyaluranController::class, 'create'])->name('salurkan.create');
    Route::post('/{rumah}/salurkan', [\App\Modules\Bantuan\Http\Controllers\PenyaluranController::class, 'store'])->name('salurkan.store');
});

// Progres renovasi: semua peran bantuan boleh melihat, hanya Verifikator yang mengubah
Route::get('/{rumah}/progres', [\App\Modules\Bantuan\Http\Controllers\ProgresRenovasiController::class, 'index'])->name('progres.index');

Route::middleware('role:verifikator')->group(function () {
    $c = \App\Modules\Bantuan\Http\Controllers\ProgresRenovasiController::class;
    Route::get('/{rumah}/progres/create',          [$c, 'create'])->name('progres.create');
    Route::post('/{rumah}/progres',                [$c, 'store'])->name('progres.store');
    Route::get('/{rumah}/progres/{progres}/edit',  [$c, 'edit'])->name('progres.edit');
    Route::put('/{rumah}/progres/{progres}',       [$c, 'update'])->name('progres.update');
    Route::delete('/{rumah}/progres/{progres}',    [$c, 'destroy'])->name('progres.destroy');
});

// Pemantauan progres: semua peran bantuan boleh melihat, hanya Verifikator yang menetapkan Selesai
Route::get('/{rumah}', [\App\Modules\Bantuan\Http\Controllers\PemantauanController::class, 'show'])->name('show');

Route::middleware('role:verifikator')->group(function () {
    Route::post('/{rumah}/selesai', [\App\Modules\Bantuan\Http\Controllers\PemantauanController::class, 'selesai'])->name('selesai');
});
