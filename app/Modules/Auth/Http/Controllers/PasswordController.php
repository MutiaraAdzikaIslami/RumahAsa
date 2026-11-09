<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Models\AuditLog;
use App\Modules\Auth\Http\Requests\ChangePasswordRequest;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * FR-03: Tampilkan form ganti password.
     */
    public function edit(Request $request)
    {
        $mustChange = (bool) $request->user()->must_change_password;

        return view('auth.password-change', [
            'mustChange' => $mustChange,
        ]);
    }

    /**
     * FR-03: Proses ganti password.
     * - Validasi current password cocok
     * - Hash password baru
     * - Clear must_change_password flag
     * - Update password_changed_at
     * - Audit PASSWORD_CHANGE
     */
    public function update(ChangePasswordRequest $request)
    {
        $user = $request->user();

        // Validasi password lama cocok
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak cocok.',
            ])->withInput();
        }

        $user->forceFill([
            'password'             => Hash::make($request->input('password')),
            'must_change_password' => false,
            'password_changed_at'  => now(),
        ])->save();

        // Audit
        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['PASSWORD_CHANGE'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  ['password_changed_at' => now()->toDateTimeString()],
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Password berhasil diganti.');
    }
}