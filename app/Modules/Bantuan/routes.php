<?php

use App\Modules\Bantuan\Http\Controllers\BantuanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BantuanController::class, 'index'])->name('index');

// Hanya Verifikator yang boleh mencatat penyaluran
Route::middleware('role:verifikator')->group(function () {
    Route::get('/{rumah}/salurkan',  [\App\Modules\Bantuan\Http\Controllers\PenyaluranController::class, 'create'])->name('salurkan.create');
    Route::post('/{rumah}/salurkan', [\App\Modules\Bantuan\Http\Controllers\PenyaluranController::class, 'store'])->name('salurkan.store');
});
