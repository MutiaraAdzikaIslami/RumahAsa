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

    public function login(LoginRequest $request)
    {
        $username    = $request->getUsername();
        $password    = $request->getPassword();
        $maxAttempts = $this->maxAttempts();
        $lockMinutes = $this->lockMinutes();

        $attempts = (int) Cache::get($request->throttleKey(), 0);
        if ($attempts >= $maxAttempts) {
            $this->auditLogger->catatLoginGagal($username);
            throw ValidationException::withMessages([
                'username' => "Akun terkunci sementara setelah {$maxAttempts} percobaan gagal. Coba lagi dalam {$lockMinutes} menit.",
            ]);
        }

        $user = User::where('username', $username)->first();

        if (
            !$user
            || !Hash::check($password, $user->password)
            || !$user->is_aktif
            || $user->deleted_at !== null
            || $user->isLocked()
        ) {
            Cache::put($request->throttleKey(), $attempts + 1, now()->addMinutes($lockMinutes));
            $this->auditLogger->catatLoginGagal($username);

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

        Cache::forget($request->throttleKey());
        $user->forceFill([
            'failed_login_count' => 0,
            'locked_until'        => null,
        ])->save();

        auth()->login($user, $request->remember());
        $request->session()->regenerate(true);

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['LOGIN_SUCCESS'],
            entitas:   'users',
            entitasId: $user->id,
        );

        if ($user->must_change_password) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda menggunakan password sementara. Wajib ganti password sebelum dapat mengakses fitur lain.');
        }

        return redirect()->intended(route('dashboard'));
    }
}
