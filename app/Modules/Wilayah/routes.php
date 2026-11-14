<?php

use App\Modules\Wilayah\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

Route::get('/',                    [WilayahController::class, 'index'])->name('index');
Route::get('/tree',                 [WilayahController::class, 'tree'])->name('tree');
Route::get('/{wilayah}',            [WilayahController::class, 'show'])->name('show');
Route::post('/',                     [WilayahController::class, 'store'])->name('store');
Route::put('/{wilayah}',             [WilayahController::class, 'update'])->name('update');
Route::delete('/{wilayah}',          [WilayahController::class, 'destroy'])->name('destroy');
Route::get('/{wilayah}/descendants', [WilayahController::class, 'descendants'])->name('descendants');