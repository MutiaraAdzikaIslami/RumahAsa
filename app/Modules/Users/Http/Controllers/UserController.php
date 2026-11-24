<?php

namespace App\Modules\Users\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Users\Http\Requests\StoreUserRequest;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $query = User::with(['role', 'wilayah']);

        if ($search = $request->input('q')) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(username) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', (int) $roleId);
        }

        $users = $query->orderBy('id')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        $roles = collect($this->userService->listRoles());

        return view('users.index', compact('users', 'roles'));
    }

    public function show(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json($user->load(['role', 'wilayah']));
        }

        return redirect()->route('users.edit', $user);
    }

    public function create(): View
    {
        $roles = collect($this->userService->listRoles());

        $kabupatenList = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        $kecamatanList = Wilayah::where('tingkat', Wilayah::TINGKAT_KECAMATAN)
            ->orderBy('nama')
            ->get(['id', 'parent_id', 'kode', 'nama']);

        return view('users.create', compact('roles', 'kabupatenList', 'kecamatanList'));
    }

    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->userService->createUser($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message'            => "User '{$result['user']->nama}' berhasil dibuat.",
                'user'               => $result['user']->load(['role', 'wilayah']),
                'password_sementara' => $result['password_plaintext'],
            ], 201);
        }

        return redirect()
            ->route('users.index')
            ->with('success', "User '{$result['user']->nama}' berhasil dibuat.")
            ->with('generated_password', $result['password_plaintext'])
            ->with('generated_username', $result['user']->username);
    }

    public function edit(User $user): View
    {
        $roles = collect($this->userService->listRoles());
        $kabupatenList = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        $kecamatanList = Wilayah::where('tingkat', Wilayah::TINGKAT_KECAMATAN)
            ->orderBy('nama')
            ->get(['id', 'parent_id', 'kode', 'nama']);

        return view('users.create', [
            'user'          => $user,
            'roles'         => $roles,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $updated = $this->userService->updateUser($user, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "User '{$updated->nama}' berhasil diperbarui.",
                'user'    => $updated->load(['role', 'wilayah']),
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', "User '{$updated->nama}' berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse|Response
    {
        if ($user->id === Auth::id()) {
            $msg = 'Anda tidak dapat menghapus akun sendiri.';
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $adminRole = Role::where('kode', 'admin')->first();
        if ($adminRole && $user->role_id === $adminRole->id) {
            $adminCount = User::where('role_id', $adminRole->id)->where('is_aktif', true)->count();
            if ($adminCount <= 1) {
                $msg = 'Tidak dapat menghapus admin terakhir.';
                return $request->wantsJson()
                    ? response()->json(['message' => $msg], 422)
                    : back()->with('error', $msg);
            }
        }

        $this->userService->deleteUser($user);

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()
            ->route('users.index')
            ->with('success', "User '{$user->nama}' telah dihapus (soft-delete).");
    }

    public function restore(Request $request, ?int $userId = null): JsonResponse|RedirectResponse
    {
        $targetId = $userId ?? (int) $request->input('user_id');
        $user = $this->userService->restoreUser($targetId);

        if (! $user) {
            return $request->wantsJson()
                ? response()->json(['message' => 'User tidak ditemukan.'], 404)
                : back()->with('error', 'User tidak ditemukan.');
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => "User '{$user->nama}' berhasil dipulihkan.", 'user' => $user->load(['role', 'wilayah'])]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', "User '{$user->nama}' berhasil dipulihkan.");
    }

    public function resetPassword(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === Auth::id()) {
            $msg = 'Anda tidak dapat mereset password akun sendiri.';
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $result = $this->userService->resetPassword($user);

        if ($request->wantsJson()) {
            return response()->json([
                'message'            => "Password user '{$user->nama}' telah di-reset.",
                'user'               => $result['user']->load(['role', 'wilayah']),
                'password_sementara' => $result['password_plaintext'],
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', "Password user '{$user->nama}' telah di-reset.")
            ->with('generated_password', $result['password_plaintext'])
            ->with('generated_username', $user->username);
    }

    public function toggleAktif(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === Auth::id()) {
            $msg = 'Anda tidak dapat menonaktifkan akun sendiri.';
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->with('error', $msg);
        }

        try {
            $updated = $this->userService->toggleAktif($user);
        } catch (\RuntimeException $e) {
            return $request->wantsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => "Status aktif user '{$updated->nama}' diubah.",
                'is_aktif' => $updated->is_aktif,
                'user'     => $updated->load(['role', 'wilayah']),
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', "Status aktif user '{$updated->nama}' diubah.");
    }
}