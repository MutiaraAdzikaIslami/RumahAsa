@extends('layouts.app')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="p-6 md:p-8 bg-gray-50 min-h-screen font-sans">
        
    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Dashboard Admin</h1>
            <p class="text-sm text-gray-500">Kelola pengguna, wilayah, data, dan laporan RumahAsa.</p>
        </div>
        
        <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <div>
                <p class="text-xs font-bold text-gray-800">Sistem berjalan normal</p>
                <p class="text-[10px] text-gray-400">Sinkronisasi terakhir {{ date('H:i') }} WIB</p>
            </div>
        </div>
    </div>

    <!-- 6 Kartu Ringkasan (Grid 3x2) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
            <div><p class="text-xs font-medium text-gray-500">Total User</p><h3 class="text-2xl font-bold text-gray-900">86</h3></div>
        </div>
        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-700 rounded-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg></div>
            <div><p class="text-xs font-medium text-gray-500">Surveyor</p><h3 class="text-2xl font-bold text-gray-900">52</h3></div>
        </div>
        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-orange-50 text-orange-600 rounded-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <div><p class="text-xs font-medium text-gray-500">Verifikator</p><h3 class="text-2xl font-bold text-gray-900">18</h3></div>
        </div>
        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-gray-100 text-gray-600 rounded-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg></div>
            <div><p class="text-xs font-medium text-gray-500">Total Rumah</p><h3 class="text-2xl font-bold text-gray-900">1.284</h3></div>
        </div>
        <!-- Card 5 -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
            <div><p class="text-xs font-medium text-gray-500">Belum Tersurvei</p><h3 class="text-2xl font-bold text-gray-900">119</h3></div>
        </div>
        <!-- Card 6 -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-green-50 text-green-600 rounded-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
            <div><p class="text-xs font-medium text-gray-500">Selesai</p><h3 class="text-2xl font-bold text-gray-900">864</h3></div>
        </div>
    </div>

    <!-- Layout Bawah (2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Aktivitas Sistem Terbaru -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Aktivitas Sistem Terbaru</h3>
                    <span class="text-sm font-semibold text-teal-800">Audit Log</span>
                </div>
                
                <div class="space-y-6">
                    <div class="flex gap-4 items-start">
                        <div class="p-2 bg-emerald-50 text-teal-800 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Keputusan verifikasi ditetapkan</p>
                            <p class="text-xs text-gray-500 mt-1">Oleh Lestari (Verifikator) pada data Widi Hidayat</p>
                            <p class="text-[10px] text-gray-400 mt-1">Hari ini, 14:12 WIB</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg></div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">User baru ditambahkan</p>
                            <p class="text-xs text-gray-500 mt-1">Admin menambahkan akun Surveyor atas nama Nanda Pratama.</p>
                            <p class="text-[10px] text-gray-400 mt-1">Kemarin, 10:24 WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Akses Cepat (Tombol Manajemen User Diperbarui) -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Akses Cepat</h3>
                
                <div class="space-y-3">
                    <!-- Tombol Manajemen User yang benar mengarah ke rute admin.users.index -->
                    <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:border-teal-800 hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-teal-50 text-teal-800 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Manajemen User</p>
                                <p class="text-xs text-gray-500">Kelola akun dan role</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-teal-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <a href="#" class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:border-teal-800 hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-50 text-emerald-700 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Kelola Wilayah</p>
                                <p class="text-xs text-gray-500">Data wilayah tugas</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-teal-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection