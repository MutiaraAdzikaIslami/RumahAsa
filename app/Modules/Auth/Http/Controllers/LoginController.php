<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    private function maxAttempts(): int
    {
        return (int) config('rumahasa.login_max_attempts', 5);
    }

    private function lockMinutes(): int
    {
        return (int) config('rumahasa.login_lock_minutes', 15);
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * FR-01: Handle login attempt.
     */
    public function login(LoginRequest $request)
    {
        $username    = $request->getUsername();
        $password    = $request->getPassword();
        $maxAttempts = $this->maxAttempts();
        $lockMinutes = $this->lockMinutes();

        // Cek throttle by username
        $attempts = (int) Cache::get($request->throttleKey(), 0);
        if ($attempts >= $maxAttempts) {
            $this->auditLogger->catatLoginGagal($username);
            throw ValidationException::withMessages([
                'username' => "Akun terkunci sementara setelah {$maxAttempts} percobaan gagal. Coba lagi dalam {$lockMinutes} menit.",
            ]);
        }

        $user = User::where('username', $username)->first();

        // Validasi: user ada + password cocok + is_aktif + tidak terkunci + tidak soft-deleted
        if (
            !$user
            || !Hash::check($password, $user->password)
            || !$user->is_aktif
            || $user->deleted_at !== null
            || $user->isLocked()
        ) {
            // Increment attempts
            Cache::put($request->throttleKey(), $attempts + 1, now()->addMinutes($lockMinutes));

            $this->auditLogger->catatLoginGagal($username);

            // Jika sudah mencapai max-1 ? kunci akun tsb (jika ada)
            if ($user && $attempts + 1 >= $maxAttempts) {
                $user->update(['locked_until' => now()->addMinutes($lockMinutes)]);
            }

            $sisa = $maxAttempts - ($attempts + 1);
            $msg = $sisa > 0
                ? "Username atau password salah. Sisa percobaan: {$sisa}."
                : "Akun terkunci. Coba lagi dalam {$lockMinutes} menit.";

            throw ValidationException::withMessages([
                'username' => $msg,
            ]);
        }

        // Sukses: reset attempt counter & locked status
        Cache::forget($request->throttleKey());
        $user->forceFill([
            'failed_login_count' => 0,
            'locked_until'        => null,
        ])->save();

        // Login + regenerate session (anti session fixation)
        auth()->login($user, $request->remember());
        $request->session()->regenerate(true);

        // Audit login success
        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['LOGIN_SUCCESS'],
            entitas:   'users',
            entitasId: $user->id,
        );

        // FR-03 (akan diaktifkan di scrum-12): redirect ke ganti password jika wajib
        if ($user->must_change_password) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda menggunakan password sementara. Wajib ganti password sebelum dapat mengakses fitur lain.');
        }

        return redirect()->intended(route('dashboard'));
    }
}
