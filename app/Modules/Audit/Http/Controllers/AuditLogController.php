<?php

namespace App\Modules\Audit\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with('user:id,nama,username');

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($aksi = $request->query('aksi')) {
            $query->where('aksi', $aksi);
        }
        if ($entitas = $request->query('entitas')) {
            $query->where('entitas', $entitas);
        }
        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('data_baru', 'like', "%{$search}%")
                  ->orWhere('data_lama', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return response()->json(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        return response()->json(
            $auditLog->load('user:id,nama,username')
        );
    }

    public function aksiList(): JsonResponse
    {
        $aksiList = AuditLog::select('aksi')
            ->distinct()
            ->orderBy('aksi')
            ->pluck('aksi');

        return response()->json(['data' => $aksiList]);
    }

    public function entitasList(): JsonResponse
    {
        $entitasList = AuditLog::select('entitas')
            ->distinct()
            ->orderBy('entitas')
            ->pluck('entitas');

        return response()->json(['data' => $entitasList]);
    }
}