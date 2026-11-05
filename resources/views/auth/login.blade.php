@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Selamat Datang Kembali</p>
    <h2 class="text-3xl font-bold text-gray-900 mb-2">Masuk ke RumahAsa</h2>
    <p class="text-gray-500 text-sm mb-8">Gunakan akun Anda untuk melanjutkan.</p>

    <!-- Notifikasi Sukses Logout/Ganti Password -->
    @if (session('status'))
        <div class="bg-green-50 text-green-700 p-4 rounded-md mb-6 text-sm border border-green-200">
            {{ session('status') }}
        </div>
    @endif

    <!-- Error Login -->
    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-md mb-6 text-sm border border-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        
        <!-- Input Email -->
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#1B4D3E] focus:border-[#1B4D3E] text-sm"
                    placeholder="nama@rumahasa.go.id">
            </div>
        </div>

        <!-- Input Password (menggunakan Alpine.js untuk toggle mata) -->
        <div class="mb-5" x-data="{ show: false }">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <input x-bind:type="show ? 'text' : 'password'" name="password" required
                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#1B4D3E] focus:border-[#1B4D3E] text-sm"
                    placeholder="Masukkan password">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="show = !show">
                    <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Ingat Saya & Lupa Password -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-[#1B4D3E] focus:ring-[#1B4D3E] border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
            </div>
            
            <!-- Modal Alpine.js untuk Kontak Admin -->
            <div x-data="{ open: false }">
                <button type="button" @click="open = true" class="text-sm font-semibold text-[#1B4D3E] hover:text-[#12362b]">Kontak Admin?</button>
                
                <!-- Modal Box -->
                <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-transition x-cloak>
                    <div @click.away="open = false" class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full">
                        <h3 class="text-lg font-bold mb-2">Butuh Bantuan?</h3>
                        <p class="text-sm text-gray-600 mb-4">Jika Anda belum memiliki akun atau lupa kata sandi, silakan hubungi Administrator sistem:</p>
                        <div class="bg-gray-100 p-3 rounded font-mono text-center mb-4 font-bold text-[#1B4D3E]">
                            {{ $kontakAdmin ?? 'Hubungi Atasan Anda' }}
                        </div>
                        <button type="button" @click="open = false" class="w-full bg-gray-200 text-gray-800 py-2 rounded-md hover:bg-gray-300 font-semibold text-sm transition">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Masuk -->
        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-[#1B4D3E] hover:bg-[#153e31] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1B4D3E]">
            Masuk
        </button>
        
        <div class="mt-6 text-center flex items-center justify-center gap-1 text-xs text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <span>Masuk hanya melalui perangkat dan jaringan yang tepercaya.</span>
        </div>
    </form>
@endsection