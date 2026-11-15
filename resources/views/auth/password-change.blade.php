@extends('layouts.guest')

@section('title', 'Ganti Password')

@section('content')
<div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-200" x-data="{ showCurrent: false, showPassword: false, showConfirm: false }">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Ganti Password</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui password akun Anda untuk menjaga keamanan data.</p>
    </div>

    @if ($mustChange || session('warning'))
        <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <p class="font-semibold text-amber-900">Perhatian: Wajib Mengganti Password</p>
                <p class="mt-0.5 text-xs text-amber-700">
                    {{ session('warning') ?? 'Anda menggunakan password sementara. Anda wajib mengganti password sebelum dapat mengakses fitur sistem lainnya.' }}
                </p>
            </div>
        </div>
    @endif

    @if (session('status') || session('success'))
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('status') ?? session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
            <p class="font-semibold mb-1 text-red-800">Gagal memperbarui password:</p>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Password Lama --}}
        <div>
            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">
                Password Lama / Sementara <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    :type="showCurrent ? 'text' : 'password'"
                    id="current_password"
                    name="current_password"
                    required
                    class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition text-slate-800"
                    placeholder="Masukkan password saat ini"
                >
                <button
                    type="button"
                    @click="showCurrent = !showCurrent"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                    tabindex="-1"
                >
                    <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showCurrent" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Password Baru --}}
        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                Password Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition text-slate-800"
                    placeholder="Minimal 8 karakter alfanumerik"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                    tabindex="-1"
                >
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                Konfirmasi Password Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    :type="showConfirm ? 'text' : 'password'"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition text-slate-800"
                    placeholder="Ulangi password baru"
                >
                <button
                    type="button"
                    @click="showConfirm = !showConfirm"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                    tabindex="-1"
                >
                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Ketentuan Kebijakan Password --}}
        <div class="rounded-lg bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600 space-y-1.5">
            <p class="font-semibold text-slate-800">Ketentuan Password:</p>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Panjang 8 sampai 64 karakter</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Hanya kombinasi huruf dan angka (alfanumerik)</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Tidak boleh mengandung spasi atau simbol khusus (@, #, $, dll)</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Tidak boleh sama dengan password lama</span>
            </div>
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
        >
            Simpan Password Baru
        </button>
    </form>

    <div class="mt-6 pt-5 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
        @if (!$mustChange)
            <a href="{{ route('dashboard') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                &larr; Kembali ke Dashboard
            </a>
        @else
            <span class="text-slate-400">Ganti password wajib diselesaikan</span>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-red-600 hover:text-red-700 font-medium">
                Keluar (Logout)
            </button>
        </form>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection
