@extends('layouts.app')

@section('title', 'Dashboard Pimpinan')

@section('content')
<div class="space-y-6">
    {{-- Header Dashboard --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Pimpinan</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau dana bantuan per triwulan.</p>
        </div>
    </div>

    {{-- Banner Hanya-Baca --}}
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center gap-3">
        <i data-lucide="info" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
        <div class="text-xs text-slate-600">
            <span class="font-semibold text-slate-800">Data bersifat hanya-baca.</span> NIK ditampilkan tersamar.
        </div>
    </div>

    {{-- Top 3 Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Total Dana Tersalur --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <div class="text-xs font-medium text-slate-500">Total Dana Tersalur</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">Rp{{ number_format($totalDana, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Triwulan 2 - 2026</div>
        </div>

        {{-- Rumah Penerima --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-4">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <div class="text-xs font-medium text-slate-500">Rumah Penerima</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalPenerima }} Rumah</div>
            <div class="text-[11px] text-slate-400 mt-1">Triwulan 2 - 2026</div>
        </div>

        {{-- Rata-rata per Rumah --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-4">
                <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
            </div>
            <div class="text-xs font-medium text-slate-500">Rata-rata per Rumah</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">Rp{{ number_format($rataRata, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Triwulan 2 - 2026</div>
        </div>
    </div>

    {{-- Dana per Sumber Program Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">Dana per Sumber Program</h2>
                <p class="text-xs text-slate-500">Proporsi penyaluran Triwulan 2 tahun 2026</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                Total Rp{{ number_format($totalDana, 0, ',', '.') }}
            </span>
        </div>

        <div class="space-y-5">
            {{-- APBN --}}
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5 font-semibold">
                    <span class="text-slate-700">APBN <span class="text-slate-400 font-normal">({{ $countApbn }} rumah)</span></span>
                    <span class="text-slate-900 font-bold">Rp{{ number_format($danaApbn, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-[#1D4034] h-2.5 rounded-full" style="width: {{ $totalDana > 0 ? round(($danaApbn / $totalDana) * 100) : 0 }}%"></div>
                </div>
            </div>

            {{-- APBD --}}
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5 font-semibold">
                    <span class="text-slate-700">APBD <span class="text-slate-400 font-normal">({{ $countApbd }} rumah)</span></span>
                    <span class="text-slate-900 font-bold">Rp{{ number_format($danaApbd, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-[#9A5B32] h-2.5 rounded-full" style="width: {{ $totalDana > 0 ? round(($danaApbd / $totalDana) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- FR-31: Heatmap Status Rumah Component --}}
    @include('dashboard.components.heatmap', [
        'role' => 'pimpinan',
        'subtitle' => 'Sebaran status rumah di seluruh wilayah.',
        'wilayahList' => $wilayahList,
        'sumberProgramList' => $sumberProgramList,
    ])

    {{-- Penyaluran Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Penyaluran Terbaru</h2>
                <p class="text-xs text-slate-500">Lima penyaluran terakhir pada periode aktif</p>
            </div>
            <a href="#" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 flex items-center gap-1 transition">
                Lihat semua
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if ($penyaluranTerbaru->isEmpty())
            <div class="py-8 text-center text-slate-400 text-xs">
                Belum ada penyaluran bantuan yang tercatat.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider text-[10px]">
                            <th class="py-3 font-semibold">Nama Kepala Keluarga</th>
                            <th class="py-3 font-semibold">Wilayah</th>
                            <th class="py-3 font-semibold">Sumber Program</th>
                            <th class="py-3 font-semibold">Nominal</th>
                            <th class="py-3 font-semibold text-right">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($penyaluranTerbaru as $salur)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 pr-2 font-semibold text-slate-900">
                                    {{ $salur->rumah?->nama_kepala_keluarga ?: 'Warga' }}
                                </td>
                                <td class="py-3.5 pr-2 text-slate-600">
                                    {{ $salur->rumah?->wilayah?->nama ?: '-' }}
                                </td>
                                <td class="py-3.5 pr-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ ($salur->sumber_program ?? 'APBN') === 'APBN' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ $salur->sumber_program ?: 'APBD' }}
                                    </span>
                                </td>
                                <td class="py-3.5 pr-2 font-bold text-slate-900">
                                    Rp{{ number_format($salur->nominal ?: 10000000, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 text-right text-slate-400">
                                    {{ $salur->tanggal_bantuan ? $salur->tanggal_bantuan->format('d M Y') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
