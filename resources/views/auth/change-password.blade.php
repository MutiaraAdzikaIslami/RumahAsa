@extends('layouts.auth')

@section('title', 'Amankan Akun Anda')

@section('content')
    <p class="text-xs font-bold text-red-600 uppercase tracking-widest mb-1">Tindakan Diperlukan</p>
    <h2 class="text-3xl font-bold text-gray-900 mb-2">Amankan Akun Anda</h2>
    
    <!-- Pesan Peringatan (Baru/Reset) -->
    <div class="bg-orange-50 border-l-4 border-orange-500 text-orange-700 p-4 mb-6 text-sm">
        {{ session('warning') ?? $pesanNotifikasi }}
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-md mb-6 text-sm border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password Lama / Sementara</label>
            <input type="password" name="password_lama" required
                class="block w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#1B4D3E] focus:border-[#1B4D3E] text-sm"
                placeholder="Masukkan password saat ini">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
            <input type="password" name="password_baru" required minlength="8"
                class="block w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#1B4D3E] focus:border-[#1B4D3E] text-sm"
                placeholder="Minimal 8 karakter">
        </div>

        <div class="mb-8">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password Baru</label>
            <input type="password" name="password_baru_confirmation" required minlength="8"
                class="block w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#1B4D3E] focus:border-[#1B4D3E] text-sm"
                placeholder="Ketik ulang password baru">
        </div>

        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-[#1B4D3E] hover:bg-[#153e31] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1B4D3E] mb-4">
            Simpan & Masuk Ulang
        </button>
    </form>

    <!-- Tombol Logout Batal -->
    <form action="{{ route('logout') }}" method="POST" class="text-center">
        @csrf
        <button type="submit" class="text-sm font-semibold text-gray-500 hover:text-red-600 flex items-center justify-center gap-1 w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Batal dan Keluar Sistem
        </button>
    </form>
@endsection