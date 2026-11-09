@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Selamat datang, {{ $user->nama }}</h2>
                <p class="text-sm text-slate-500">Anda login sebagai {{ $user->role->nama }}</p>
            </div>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @can('input-rumah')
        <a href="{{ route('rumah.create') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition border border-slate-100">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 mb-3">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            </div>
            <h3 class="font-semibold text-slate-900">Input Rumah Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Daftarkan rumah warga untuk disurvei.</p>
        </a>
        @endcan

        <a href="{{ route('rumah.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition border border-slate-100">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 mb-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
            </div>
            <h3 class="font-semibold text-slate-900">Daftar Rumah</h3>
            <p class="text-xs text-slate-500 mt-1">Lihat dan cari data rumah yang sudah diinput.</p>
        </a>

        @can('view-audit-log')
        <a href="{{ route('audit.view') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition border border-slate-100">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 mb-3">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <h3 class="font-semibold text-slate-900">Audit Log</h3>
            <p class="text-xs text-slate-500 mt-1">Pantau riwayat aktivitas sistem.</p>
        </a>
        @endcan
    </div>
</div>
@endsection