<?php

namespace App\Modules\Users\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Users\Http\Requests\StoreUserRequest;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * List user dengan filter & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->with(['role:id,kode,nama', 'wilayah:id,kode,nama,tingkat']);

        if ($roleId = $request->query('role_id')) {
            $query->where('role_id', $roleId);
        }
        if ($request->has('is_aktif')) {
            $query->where('is_aktif', $request->boolean('is_aktif'));
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        return response()->json(
            $query->orderBy('nama')->paginate(20)
        );
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(
            $user->load(['role:id,kode,nama', 'wilayah:id,kode,nama,tingkat'])
        );
    }

    /**
     * Create user baru. Return password sementara (sekali tampil).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $result = $this->service->create($request->validated());

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['USER_CREATE'],
                entitas:   'users',
                entitasId: $result['user']->id,
                dataBaru:  ['username' => $result['user']->username, 'role_id' => $result['user']->role_id],
            );

            return response()->json([
                'message'            => 'User berhasil dibuat. Password sementara ditampilkan sekali.',
                'user'               => $result['user']->load('role', 'wilayah'),
                'password_sementara' => $result['password_sementara'],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $dataLama = $user->only(['nama', 'username', 'email', 'role_id', 'wilayah_id', 'is_aktif']);
            $updated = $this->service->update($user, $request->validated());
            $dataBaru = $updated->only(['nama', 'username', 'email', 'role_id', 'wilayah_id', 'is_aktif']);

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['USER_UPDATE'],
                entitas:   'users',
                entitasId: $user->id,
                dataBaru:  $dataBaru,
                dataLama:  $dataLama,
            );

            return response()->json($updated->load('role', 'wilayah'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Nonaktifkan / aktifkan kembali user.
     */
    public function toggleAktif(User $user): JsonResponse
    {
        try {
            $updated = $this->service->toggleAktif($user);

            $this->auditLogger->catat(
                aksi:      $updated->is_aktif ? AuditLog::AKSI['USER_ACTIVATE'] : AuditLog::AKSI['USER_DEACTIVATE'],
                entitas:   'users',
                entitasId: $user->id,
                dataBaru:  ['is_aktif' => $updated->is_aktif],
            );

            return response()->json($updated);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['USER_DELETE'],
                entitas:   'users',
                entitasId: $user->id,
                dataLama:  $user->only(['username', 'nama', 'role_id']),
            );

            $this->service->delete($user);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function restore(int $userId): JsonResponse
    {
        $user = $this->service->restore($userId);

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['USER_RESTORE'],
            entitas:   'users',
            entitasId: $user->id,
        );

        return response()->json($user->load('role', 'wilayah'));
    }
}