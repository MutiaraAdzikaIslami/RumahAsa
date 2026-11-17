@extends('layouts.app')

@section('title', 'Dashboard Surveyor')

@section('content')
<div class="space-y-6">
    {{-- Header Dashboard --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Selamat datang, {{ $user->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola pendataan rumah dan pantau progres renovasi di wilayah tugas Anda.</p>
        </div>
        <div>
            <a href="{{ route('rumah.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#1D4034] text-white hover:bg-[#2A5244] shadow-sm transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Isi Survei Rumah
            </a>
        </div>
    </div>

    {{-- Banner Manual --}}
    <div class="bg-emerald-50/60 border border-emerald-200/70 rounded-xl p-4 flex items-start gap-3">
        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i data-lucide="check" class="w-3.5 h-3.5"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-emerald-900">Keputusan kategori dilakukan secara manual</div>
            <div class="text-[11px] text-emerald-700 mt-0.5 leading-relaxed">
                RumahAsa membantu pencatatan dan verifikasi data. Penetapan kelayakan tetap dilakukan Verifikator berdasarkan pedoman kebijakan pemerintah.
            </div>
        </div>
    </div>

    {{-- Row 6 Kartu Ringkasan Surveyor --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        {{-- Menunggu Survei --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center mb-2.5">
                <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $menungguSurveiCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Menunggu Survei</div>
        </div>

        {{-- Draf --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center mb-2.5">
                <i data-lucide="file-text" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $drafCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Draf</div>
        </div>

        {{-- Diajukan --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-2.5">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $diajukanCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Diajukan</div>
        </div>

        {{-- Perlu Perbaikan --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mb-2.5">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $perluPerbaikanCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Perlu Perbaikan</div>
        </div>

        {{-- Dalam Renovasi --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center mb-2.5">
                <i data-lucide="wrench" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $dalamRenovasiCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Dalam Renovasi</div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2.5">
                <i data-lucide="home" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $selesaiCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Selesai</div>
        </div>
    </div>

    {{-- Middle Section: Data Rumah Terbaru & Progres Renovasi --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: Data Rumah Terbaru --}}
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Data Rumah Terbaru</h2>
                    <p class="text-xs text-slate-500">Rumah di wilayah tugas Anda dan tindakan yang diperlukan.</p>
                </div>
                <a href="{{ route('rumah.index') }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 flex items-center gap-1 transition">
                    Lihat semua
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if ($dataRumahTerbaru->isEmpty())
                <div class="py-12 text-center text-slate-400 text-xs">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                    Belum ada data rumah di wilayah tugas Anda.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider text-[10px]">
                                <th class="py-3 font-semibold">Nama Pemilik</th>
                                <th class="py-3 font-semibold">Alamat</th>
                                <th class="py-3 font-semibold">Wilayah</th>
                                <th class="py-3 font-semibold">Status</th>
                                <th class="py-3 font-semibold">Terakhir Diperbarui</th>
                                <th class="py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($dataRumahTerbaru as $item)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 pr-2 font-semibold text-slate-900">
                                        {{ $item->nama_kepala_keluarga ?: 'Warga' }}
                                    </td>
                                    <td class="py-3.5 pr-2 text-slate-500 max-w-[150px] truncate">
                                        {{ $item->alamat ?: '-' }}
                                    </td>
                                    <td class="py-3.5 pr-2 text-slate-600">
                                        {{ $item->wilayah?->nama ?: '-' }}
                                    </td>
                                    <td class="py-3.5 pr-2">
                                        @php
                                            $badgeClasses = match($item->status) {
                                                'Menunggu Disurvei' => 'bg-slate-100 text-slate-700 border-slate-200',
                                                'Draf' => 'bg-slate-100 text-slate-700 border-slate-200',
                                                'Diajukan' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'Perlu Perbaikan' => 'bg-orange-50 text-orange-700 border-orange-200',
                                                'Dalam Renovasi' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeClasses }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-2 text-slate-400">
                                        {{ $item->updated_at ? $item->updated_at->diffForHumans() : '-' }}
                                    </td>
                                    <td class="py-3.5 text-right">
                                        @if ($item->status === 'Perlu Perbaikan')
                                            <a href="{{ route('rumah.show', $item) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-800 text-white hover:bg-emerald-900 transition">
                                                Perbaiki
                                            </a>
                                        @elseif ($item->status === 'Draf' || $item->status === 'Menunggu Disurvei')
                                            <a href="{{ route('rumah.show', $item) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-forest text-white hover:bg-forest-light transition">
                                                Mulai Survei
                                            </a>
                                        @else
                                            <a href="{{ route('rumah.show', $item) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 border border-slate-200 transition">
                                                Lihat →
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Right: Progres Renovasi Card --}}
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Progres Renovasi</h2>
                        <p class="text-xs text-slate-500">Rumah yang sedang ditangani</p>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                    </div>
                </div>

                @if ($progresRenovasi->isEmpty())
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada renovasi aktif di wilayah tugas.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($progresRenovasi as $prog)
                            @php
                                $percent = $prog->status === 'Selesai' ? 100 : 70;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center text-xs mb-1">
                                    <span class="font-semibold text-slate-800">{{ $prog->nama_kepala_keluarga }}</span>
                                    <span class="font-bold text-slate-900">{{ $percent }}%</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mb-1.5">{{ $prog->wilayah?->nama }}</div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-[#1D4034] h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-6 pt-3 border-t border-slate-100">
                <a href="{{ route('rumah.index') }}" class="w-full inline-flex items-center justify-center py-2 rounded-xl text-xs font-semibold text-slate-700 border border-slate-200 hover:bg-slate-50 transition">
                    Lihat semua progres →
                </a>
            </div>
        </div>
    </div>

    {{-- FR-31: Heatmap Status Rumah Component --}}
    @include('dashboard.components.heatmap', [
        'role' => 'surveyor',
        'subtitle' => 'Sebaran rumah di wilayah tugas Anda.',
        'surveyorWilayah' => $wilayah,
        'wilayahList' => $wilayahList,
        'sumberProgramList' => $sumberProgramList,
    ])
</div>
@endsection
