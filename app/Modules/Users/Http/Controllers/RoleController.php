<?php

namespace App\Modules\Users\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * FR-06: Endpoint untuk list role (dropdown di form user).
 */
class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::orderBy('nama')->get(['id', 'kode', 'nama', 'deskripsi']);

        return response()->json([
            'data' => $roles,
            // Helper: role mana yang wajib punya wilayah
            'roles_wajib_wilayah' => ['surveyor'],
        ]);
    }
}