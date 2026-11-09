<?php

use App\Modules\Users\Http\Controllers\RoleController;
use App\Modules\Users\Http\Controllers\UserController;
use App\Modules\Users\Http\Controllers\UserFormDataController;
use Illuminate\Support\Facades\Route;

// scrum-15: Endpoint untuk form user (roles + wilayah tree)
Route::get('/form-data',  [UserFormDataController::class, 'index'])->name('form-data');
Route::get('/roles',      [RoleController::class, 'index'])->name('roles');

// scrum-14: CRUD user
Route::get('/',                  [UserController::class, 'index'])->name('index');
Route::get('/{user}',             [UserController::class, 'show'])->name('show');
Route::post('/',                  [UserController::class, 'store'])->name('store');
Route::put('/{user}',              [UserController::class, 'update'])->name('update');
Route::patch('/{user}/toggle-aktif', [UserController::class, 'toggleAktif'])->name('toggle-aktif');
Route::delete('/{user}',           [UserController::class, 'destroy'])->name('destroy');
Route::post('/{userId}/restore',   [UserController::class, 'restore'])->name('restore');