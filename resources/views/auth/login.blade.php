@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="flex min-h-screen">
    
    {{-- ===== PANEL KIRI (Branding) — 54% ===== --}}
    <div class="hidden lg:flex w-[54%] relative overflow-hidden bg-forest text-white flex-col justify-between p-16 lg:px-24 py-20">
        
        <div class="absolute -top-40 -right-32 h-[800px] w-[800px] rounded-full bg-white/5"></div>
        <div class="absolute -bottom-40 -left-32 h-[600px] w-[600px] rounded-full bg-forest-light/40"></div>
        
        <div class="relative z-10 flex items-center gap-3">
            <img src="{{ asset('logo.svg') }}" alt="RumahAsa" class="w-10 h-10">
            <span class="text-2xl font-bold">Rumah<span class="text-terracotta">Asa</span></span>
        </div>
        
        <div class="relative z-10">
            <p class="text-sm font-semibold tracking-[0.2em] text-[#A8C5B8] uppercase mb-6">
                Sistem Layanan Perumahan Terpadu
            </p>
            <h1 class="text-5xl xl:text-6xl font-bold leading-tight mb-8">
                Pendataan Rumah,<br>Lebih Terarah.
            </h1>
            <p class="text-xl text-[#C9DCD2] leading-relaxed max-w-lg">
                Kelola pendataan, verifikasi, dan pemantauan renovasi rumah warga dalam satu sistem terintegrasi.
            </p>
        </div>
        
        <div class="relative z-10 flex items-end justify-center gap-4 opacity-90">
            <svg viewBox="0 0 200 200" class="w-32 h-32"><polygon points="20,80 100,30 180,80" fill="#D5DCD5"/><rect x="40" y="80" width="120" height="100" fill="#D5DCD5"/><rect x="55" y="95" width="25" height="25" fill="#7BAF9A"/><rect x="100" y="95" width="25" height="25" fill="#7BAF9A"/><rect x="75" y="135" width="30" height="45" fill="#A0784A"/></svg>
            <svg viewBox="0 0 200 200" class="w-44 h-44"><polygon points="10,70 100,20 190,70" fill="#F0EBE0"/><rect x="30" y="70" width="140" height="120" fill="#F0EBE0"/><rect x="45" y="85" width="30" height="30" fill="#7BAF9A"/><rect x="95" y="85" width="30" height="30" fill="#7BAF9A"/><rect x="70" y="135" width="40" height="55" fill="#A0784A"/><rect x="135" y="130" width="25" height="25" fill="#7BAF9A"/></svg>
            <svg viewBox="0 0 200 200" class="w-32 h-32"><polygon points="20,80 100,30 180,80" fill="#D5DCD5"/><rect x="40" y="80" width="120" height="100" fill="#D5DCD5"/><rect x="55" y="95" width="25" height="25" fill="#7BAF9A"/><rect x="100" y="95" width="25" height="25" fill="#7BAF9A"/><rect x="75" y="135" width="30" height="45" fill="#A0784A"/></svg>
        </div>
        
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl border border-white/20 flex items-center justify-center bg-white/5">
                <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
            </div>
            <div>
                <p class="font-bold text-lg">Data terlindungi</p>
                <p class="text-[#B8CCC0] text-sm">Akses aman sesuai peran petugas</p>
            </div>
        </div>
    </div>
    
    {{-- ===== PANEL KANAN (Form) — 46% ===== --}}
    <div class="w-full lg:w-[46%] bg-cream flex flex-col items-center justify-center px-8 sm:px-16 lg:px-20 xl:px-24 py-12 relative">
        
        <div class="lg:hidden flex items-center gap-2 mb-8">
            <img src="{{ asset('logo.svg') }}" alt="RumahAsa" class="w-8 h-8">
            <span class="text-xl font-bold text-forest">Rumah<span class="text-terracotta">Asa</span></span>
        </div>
        
        @if (session('status'))
            <div class="mb-6 w-full max-w-md p-4 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('status') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-6 w-full max-w-md p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-700 flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                {{ session('warning') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 w-full max-w-md p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif
        
        <div class="w-full max-w-md">
            <div class="mb-10">
                <p class="text-sm font-bold tracking-[0.15em] text-forest uppercase mb-3">
                    Selamat Datang Kembali
                </p>
                <h2 class="text-4xl font-semibold text-[#2B3B33] mb-3">
                    Masuk ke RumahAsa
                </h2>
                <p class="text-lg text-[#6B7A72]">
                    Gunakan akun Anda untuk melanjutkan.
                </p>
            </div>
            
            <form method="POST" action="{{ route('login') }}" class="space-y-6" x-data="{ showPassword: false }">
                @csrf
                
                <div>
                    <label for="username" class="block text-base font-semibold text-[#2B3B33] mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-[#9CA89F]"></i>
                        </div>
                        <input
                            type="text" id="username" name="username" value="{{ old('username') }}"
                            required autofocus
                            class="w-full h-16 pl-14 pr-5 rounded-xl bg-white border border-[#D8D4C8] text-lg text-[#2B3B33] placeholder-[#A9B3AC] focus:border-forest focus:ring-2 focus:ring-forest/20 outline-none transition"
                            placeholder="Masukkan username"
                        >
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-base font-semibold text-[#2B3B33] mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i data-lucide="lock-keyhole" class="w-5 h-5 text-[#9CA89F]"></i>
                        </div>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password" name="password" required
                            class="w-full h-16 pl-14 pr-14 rounded-xl bg-white border border-[#D8D4C8] text-lg text-[#2B3B33] placeholder-[#A9B3AC] focus:border-forest focus:ring-2 focus:ring-forest/20 outline-none transition"
                            placeholder="Masukkan password"
                        >
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-5 flex items-center text-[#9CA89F] hover:text-[#2B3B33] transition" tabindex="-1">
                            <i x-show="!showPassword" data-lucide="eye" class="w-5 h-5"></i>
                            <i x-show="showPassword" x-cloak data-lucide="eye-off" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-5 h-5 rounded border-[#D8D4C8] text-forest focus:ring-forest/20">
                        <span class="text-base text-[#4A574F]">Ingat saya</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full h-16 bg-forest hover:bg-forest-dark text-white font-bold text-lg rounded-xl transition focus:outline-none focus:ring-2 focus:ring-forest/30 focus:ring-offset-2 shadow-lg shadow-forest/20 flex items-center justify-center gap-2">
                    Masuk
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
            </form>
            
            <div class="mt-8 flex items-center justify-center gap-2 text-sm text-[#7A867E]">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Masuk hanya melalui perangkat dan jaringan yang tepercaya.</span>
            </div>
            
            <p class="mt-6 text-center text-sm text-[#8A948C]">
                Akun dibuat oleh Admin. Hubungi Admin jika belum punya akun.
            </p>
        </div>
        
        <p class="absolute bottom-6 text-center text-sm text-[#8A948C]">
            © 2026 RumahAsa · Pemerintah Kabupaten Sukamaju
        </p>
    </div>
</div>

{{-- Script untuk re-render Lucide Icons setelah Alpine update --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
    document.addEventListener('alpine:initialized', () => {
        lucide.createIcons();
    });
</script>
@endsection