<?php

use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/',                  [UserController::class, 'index'])->name('index');
Route::get('/{user}',             [UserController::class, 'show'])->name('show');
Route::post('/',                  [UserController::class, 'store'])->name('store');
Route::put('/{user}',              [UserController::class, 'update'])->name('update');
Route::patch('/{user}/toggle-aktif', [UserController::class, 'toggleAktif'])->name('toggle-aktif');
Route::delete('/{user}',           [UserController::class, 'destroy'])->name('destroy');
Route::post('/{userId}/restore',   [UserController::class, 'restore'])->name('restore');