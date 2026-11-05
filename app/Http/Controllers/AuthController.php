<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 1. Tampilkan Halaman Login
    public function showLogin()
    {
        $kontakAdmin = env('ADMIN_CONTACT', '0812-3456-7890');
        
        return view('auth.login', compact('kontakAdmin'));
    }

    // 2. Proses Autentikasi Login (Sesuai FR-01)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Proteksi Rate Limiting: 3x gagal, kunci 15 menit (900 detik)
        $throttleKey = Str::lower($request->email) . '|' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit.",
            ])->onlyInput('email');
        }

        // Percobaan Login
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        // Jika Gagal
        RateLimiter::hit($throttleKey, 900);
        
        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    // 3. Tampilkan Halaman Ganti Password (Force Reset)
    public function showChangePassword()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $pesanNotifikasi = is_null($user->password_changed_at) 
            ? 'Anda terdeteksi sebagai pengguna baru, silakan mengganti kata sandi Anda demi keamanan akun.'
            : 'Anda terdeteksi sebagai pengguna pasca-reset kata sandi oleh admin, silakan mengganti kata sandi Anda demi keamanan akun.';

        return view('auth.change-password', compact('pesanNotifikasi'));
    }

    // 4. Proses Simpan Password Baru (Sesuai FR-03)
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Validasi password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        // Simpan hash baru, matikan penanda, catat waktu
        $user->password = Hash::make($request->password_baru);
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        // Logout otomatis demi keamanan pasca ubah password
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('status', 'Password diperbarui. Silakan masuk kembali.');
    }

    // 5. Proses Logout (Sesuai FR-02)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}