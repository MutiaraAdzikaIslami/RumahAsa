<?php

namespace App\Modules\Users\Http\Controllers;

use App\Models\Role;
use App\Modules\Wilayah\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * FR-06: Endpoint untuk hydrate form user (roles + wilayah tree).
 *
 * Frontend panggil endpoint ini sekali saat buka form create/edit user,
 * dapat semua data yang dibutuhkan untuk dropdown role + cascading wilayah.
 */
class UserFormDataController extends Controller
{
    public function __construct(private readonly WilayahService $wilayahService)
    {
    }

    public function index(): JsonResponse
    {
        $roles = Role::orderBy('nama')->get(['id', 'kode', 'nama']);
        $wilayahTree = $this->wilayahService->getTree();

        return response()->json([
            'roles' => $roles,
            'wilayah_tree' => $wilayahTree,
            // Aturan bisnis untuk frontend:
            // - Surveyor wajib pilih wilayah (di tingkat mana pun)
            // - Verifikator/Admin/Pimpinan wilayah harus null (lihat semua)
            'rules' => [
                'roles_wajib_wilayah' => ['surveyor'],
                'roles_lihat_semua_wilayah' => ['verifikator', 'admin', 'pimpinan'],
            ],
        ]);
    }
}