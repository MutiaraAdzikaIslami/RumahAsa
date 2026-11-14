<?php

use App\Modules\Audit\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

// scrum-18: Audit Log (FR-09) — read-only, admin only
Route::get('/',              [AuditLogController::class, 'index'])->name('index');
Route::get('/aksi-list',     [AuditLogController::class, 'aksiList'])->name('aksi-list');
Route::get('/entitas-list',   [AuditLogController::class, 'entitasList'])->name('entitas-list');
Route::get('/{auditLog}',     [AuditLogController::class, 'show'])->name('show');