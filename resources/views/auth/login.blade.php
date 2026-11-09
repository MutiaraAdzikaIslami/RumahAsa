@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-200" x-data="{ showPassword: false }">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">{{ config('app.name') }}</h1>
        <p class="text-sm text-slate-500 mt-1">Sistem Informasi Pendataan RTLH</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-700">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                required
                autofocus
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                placeholder="Masukkan username"
            >
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <div class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                    placeholder="Masukkan password"
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

        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="ml-2 text-sm text-slate-600">Ingat saya</span>
            </label>
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
        >
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-slate-400">
        Akun dibuat oleh Admin. Hubungi Admin jika belum punya akun.
    </p>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection
