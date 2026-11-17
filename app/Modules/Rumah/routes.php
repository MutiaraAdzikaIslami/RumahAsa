<?php

use App\Modules\Rumah\Http\Controllers\RumahController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RumahController::class, 'index'])->name('index');

Route::middleware('role:admin')->group(function () {
    Route::get('/create',       [RumahController::class, 'create'])->name('create');
    Route::post('/',            [RumahController::class, 'store'])->name('store');
    Route::get('/{rumah}/edit', [RumahController::class, 'edit'])->name('edit');
    Route::put('/{rumah}',      [RumahController::class, 'update'])->name('update');
});

Route::get('/{rumah}', [RumahController::class, 'show'])->name('show');