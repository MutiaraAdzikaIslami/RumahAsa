<?php

use App\Modules\Bantuan\Http\Controllers\BantuanController;
use App\Modules\Bantuan\Http\Controllers\PemantauanController;
use App\Modules\Bantuan\Http\Controllers\PenyaluranController;
use App\Modules\Bantuan\Http\Controllers\ProgresRenovasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BantuanController::class, 'index'])->middleware('role:verifikator|admin|pimpinan')->name('index');
Route::get('/ekspor', [BantuanController::class, 'eksporDaftar'])->middleware('role:verifikator|admin')->name('ekspor');
Route::get('/rekap-dana', [BantuanController::class, 'rekap'])->middleware('role:verifikator|admin|pimpinan')->name('rekap');
Route::get('/rekap-dana/ekspor', [BantuanController::class, 'eksporRekap'])->middleware('role:verifikator|admin|pimpinan')->name('rekap.ekspor');

// Hanya Verifikator yang boleh mencatat penyaluran
Route::middleware('role:verifikator')->group(function () {
    Route::get('/{rumah}/salurkan',  [PenyaluranController::class, 'create'])->name('salurkan.create');
    Route::post('/{rumah}/salurkan', [PenyaluranController::class, 'store'])->name('salurkan.store');
});

// Progres renovasi: dapat dilihat oleh semua peran berwenang
Route::get('/{rumah}/progres', [ProgresRenovasiController::class, 'index'])->name('progres.index');
Route::get('/foto/{foto}',     [ProgresRenovasiController::class, 'showFoto'])->name('progres.foto');

// Perubahan progres renovasi: Surveyor dan Verifikator (FR-22)
Route::middleware('role:surveyor|verifikator')->group(function () {
    Route::get('/{rumah}/progres/create',          [ProgresRenovasiController::class, 'create'])->name('progres.create');
    Route::post('/{rumah}/progres',                [ProgresRenovasiController::class, 'store'])->name('progres.store');
    Route::get('/{rumah}/progres/{progres}/edit',  [ProgresRenovasiController::class, 'edit'])->name('progres.edit');
    Route::put('/{rumah}/progres/{progres}',       [ProgresRenovasiController::class, 'update'])->name('progres.update');
    Route::delete('/{rumah}/progres/{progres}',    [ProgresRenovasiController::class, 'destroy'])->name('progres.destroy');
});

// Halaman Detail Penerima Bantuan (diakses dari Daftar Penerima Bantuan — Mockup Halaman 11 & 10)
Route::get('/{rumah}/detail-penerima', [PemantauanController::class, 'showPenerima'])
    ->middleware('role:verifikator|admin|pimpinan')
    ->name('penerima.show');

// Halaman Review Penyelesaian Renovasi (diakses dari Progres Renovasi — Mockup Halaman 15, 14 & 2)
Route::get('/{rumah}', [PemantauanController::class, 'show'])->name('show');

Route::middleware('role:verifikator')->group(function () {
    Route::post('/{rumah}/selesai', [PemantauanController::class, 'selesai'])->name('selesai');
});
