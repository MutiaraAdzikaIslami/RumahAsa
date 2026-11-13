@extends('layouts.app')

@section('title', 'Antrean Survei Diajukan')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header & Statistik Singkat --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">Antrean Survei Diajukan</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    {{ $totalAntrean }} Menunggu Peninjauan
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Daftar survei dari semua wilayah yang diajukan oleh Surveyor dan siap ditinjau.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rumah.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg shadow-sm transition">
                <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                Semua Data Rumah
            </a>
        </div>
    </div>

    {{-- Banner Prinsip Verifikasi --}}
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
        <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg shrink-0 mt-0.5">
            <i data-lucide="shield-alert" class="w-5 h-5"></i>
        </div>
        <div class="text-sm">
            <p class="font-semibold text-emerald-900">Prinsip Verifikasi RumahAsa</p>
            <p class="text-emerald-800 text-xs mt-0.5 leading-relaxed">
                Penetapan kelayakan dilakukan secara <strong>manual</strong> oleh Verifikator berdasarkan kelengkapan data lapangan dan galeri foto bukti 7 komponen. Sistem tidak membuat skor atau menyarankan keputusan otomatis.
            </p>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('verifikator.survei.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Survei</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode registrasi atau nama kepala keluarga..." class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
            </div>

            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Wilayah</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </span>
                    <select name="wilayah_id" class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                        <option value="">Semua Wilayah</option>
                        @foreach ($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                                [{{ strtoupper($w->tingkat) }}] {{ $w->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-forest hover:bg-forest-light text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Terapkan
                </button>
                @if (request()->hasAny(['search', 'wilayah_id']))
                    <a href="{{ route('verifikator.survei.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition flex items-center justify-center" title="Reset filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Antrean Survei --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @if ($antrean->isEmpty())
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-800">Tidak Ada Survei Menunggu Peninjauan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    @if (request()->hasAny(['search', 'wilayah_id']))
                        Tidak ada antrean survei yang cocok dengan kriteria filter yang Anda tentukan. Silakan sesuaikan atau reset filter.
                    @else
                        Semua survei yang diajukan telah selesai ditinjau, atau belum ada survei baru yang dikirim oleh Surveyor.
                    @endif
                </p>
                @if (request()->hasAny(['search', 'wilayah_id']))
                    <div class="mt-4">
                        <a href="{{ route('verifikator.survei.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset Filter
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-slate-600 font-semibold border-b border-slate-200 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Rumah & Kode</th>
                            <th class="py-3.5 px-4">Kepala Keluarga</th>
                            <th class="py-3.5 px-4">Wilayah</th>
                            <th class="py-3.5 px-4">Surveyor</th>
                            <th class="py-3.5 px-4">Waktu Pengajuan</th>
                            <th class="py-3.5 px-4 text-center">Bukti Foto</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($antrean as $item)
                            @php
                                $survei = $item->survei()->first();
                                $fotoCount = $survei ? $survei->komponenSurvei->sum(fn ($k) => $k->foto->count()) : 0;
                                $tanggalPengajuan = $survei?->dikirim_at ?? $item->updated_at;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-semibold text-slate-900 text-xs">{{ $item->kode_registrasi }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">ID: #{{ $item->id }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $item->nama_kepala_keluarga ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5">
                                        @if ($item->nik_enc)
                                            {{ $item->nikTersamar() }}
                                        @else
                                            <span class="text-slate-400 italic">NIK belum diisi</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-800 font-medium text-xs">{{ $item->wilayah?->nama ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ strtoupper($item->wilayah?->tingkat ?? '') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-800 text-xs font-medium">{{ $item->surveyor?->nama ?? 'Surveyor Lapangan' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $item->surveyor?->email ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700 text-xs font-medium">{{ $tanggalPengajuan ? $tanggalPengajuan->translatedFormat('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $tanggalPengajuan ? $tanggalPengajuan->format('H:i') . ' WIB' : '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $fotoCount >= 7 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                        <span>{{ $fotoCount }} Foto</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('verifikator.survei.show', $item) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                                        <span>Review Survei</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($antrean->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $antrean->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
