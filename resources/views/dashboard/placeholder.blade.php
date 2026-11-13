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
        @can('access-verifikator')
        @php
            $antreanCount = \App\Models\RumahWarga::where('status', \App\Models\RumahWarga::STATUS_DIAJUKAN)->count();
        @endphp
        <a href="{{ route('verifikator.survei.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition border border-slate-100 relative overflow-hidden">
            @if ($antreanCount > 0)
                <span class="absolute top-4 right-4 bg-amber-500 text-slate-900 text-xs font-bold px-2.5 py-0.5 rounded-full">
                    {{ $antreanCount }} Menunggu
                </span>
            @endif
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 mb-3">
                <i data-lucide="clipboard-check" class="w-5 h-5 text-emerald-700"></i>
            </div>
            <h3 class="font-semibold text-slate-900">Antrean Survei Diajukan</h3>
            <p class="text-xs text-slate-500 mt-1">Tinjau kelengkapan survei dan tetapkan status kelayakan rumah.</p>
        </a>
        @endcan

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

    @can('access-verifikator')
    @php
        $antreanTerbaru = \App\Models\RumahWarga::with(['wilayah', 'surveyor', 'survei.komponenSurvei.foto'])
            ->where('status', \App\Models\RumahWarga::STATUS_DIAJUKAN)
            ->latest('updated_at')
            ->limit(5)
            ->get();
    @endphp
    @if ($antreanTerbaru->isNotEmpty())
        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Survei Menunggu Peninjauan Terbaru</h3>
                    <p class="text-xs text-slate-500">Pengajuan terbaru dari Surveyor yang siap diverifikasi.</p>
                </div>
                <a href="{{ route('verifikator.survei.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                    Lihat Semua Antrean &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Kode</th>
                            <th class="py-2.5 px-3">Nama KK</th>
                            <th class="py-2.5 px-3">Wilayah</th>
                            <th class="py-2.5 px-3">Surveyor</th>
                            <th class="py-2.5 px-3 text-center">Foto Bukti</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($antreanTerbaru as $item)
                            @php
                                $s = $item->survei()->first();
                                $fc = $s ? $s->komponenSurvei->sum(fn ($k) => $k->foto->count()) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-medium text-slate-900">{{ $item->kode_registrasi }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $item->nama_kepala_keluarga ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $item->wilayah?->nama ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $item->surveyor?->nama ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        <i data-lucide="image" class="w-3 h-3"></i>
                                        {{ $fc }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <a href="{{ route('verifikator.survei.show', $item) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow-sm">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    @endcan
    </div>
</div>
@endsection