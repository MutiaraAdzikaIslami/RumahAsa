@extends('layouts.app')

@section('title', 'Progres Renovasi — ' . $rumah->nama_kepala_keluarga)

@section('content')
@php
    $dalamRenovasi = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI;
    $persenTerakhir = $bantuan->progresTerakhir();
@endphp

<div class="space-y-6">
    {{-- Header Section (Mockup Page 9) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold text-forest uppercase tracking-wider mb-1">PEMANTAUAN BANTUAN</p>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Progres Renovasi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Catat perkembangan pekerjaan dan dokumentasi lapangan secara berkala.</p>
        </div>
        <div class="flex items-center gap-2">
            @can('surveyor')
                @if ($dalamRenovasi)
                    <a href="{{ route('bantuan.progres.create', $rumah) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Tambah Progres
                    </a>
                @endif
            @endcan
            <a href="{{ route('bantuan.index') }}"
               class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition">
                Kembali
            </a>
        </div>
    </div>

    {{-- Dropdown Pemilihan Rumah Penerima Bantuan (Mockup Page 9) --}}
    @if (isset($daftarRumahRenovasi) && $daftarRumahRenovasi->count() > 1)
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-forest/10 text-forest flex items-center justify-center flex-shrink-0">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800">Pilih rumah penerima bantuan</h2>
                    <p class="text-[11px] text-slate-400">Data renovasi aktif di wilayah tugas Anda</p>
                </div>
            </div>
            <div class="relative w-full md:w-80">
                <select onchange="window.location.href = this.value"
                        class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm cursor-pointer">
                    @foreach ($daftarRumahRenovasi as $r)
                        <option value="{{ route('bantuan.progres.index', $r) }}" {{ $r->id === $rumah->id ? 'selected' : '' }}>
                            {{ $r->nama_kepala_keluarga }} &middot; {{ $r->kode_registrasi }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    {{-- Kartu Ringkasan Penerima & Progres Terakhir --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            {{-- Data Warga --}}
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-forest text-white flex items-center justify-center font-bold text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block">Nama Pemilik</span>
                    <h2 class="text-base font-bold text-slate-900 leading-tight">{{ $rumah->nama_kepala_keluarga }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5 truncate max-w-xs" title="{{ $rumah->alamat }}">
                        {{ $rumah->alamat }}, {{ $rumah->wilayah?->nama ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- Status Renovasi --}}
            <div class="md:text-center md:border-x border-slate-100 md:px-4">
                <span class="text-[11px] text-slate-400 block mb-1">Status Renovasi</span>
                @php
                    $badgeStyles = [
                        'Penerima Bantuan' => 'bg-[#FBF4E6] text-[#A26D27] border border-[#F2DEBF]',
                        'Dalam Renovasi'   => 'bg-[#F4EFFC] text-[#6941C6] border border-[#E0D4F7]',
                        'Selesai'          => 'bg-[#EDF7EE] text-[#2D6A4F] border border-[#CDE5D8]',
                    ];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeStyles[$rumah->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $rumah->status }}
                </span>
            </div>

            {{-- Progress Terakhir --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-medium text-slate-500">Progress terakhir</span>
                    <span class="text-lg font-bold text-slate-900">{{ $persenTerakhir }}%</span>
                </div>
                <div class="w-full bg-slate-200/90 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-forest h-2.5 rounded-full transition-all duration-500" style="width: {{ $persenTerakhir }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5">
                    Diperbarui: {{ $daftar->first() ? \Carbon\Carbon::parse($daftar->first()->tanggal_progres)->format('d M Y') : 'Belum ada pembaruan' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Layout Utama: Timeline Riwayat Progres & Panel Samping --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Riwayat Progres (Mockup Page 9) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Riwayat Pembaruan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Dokumentasi perkembangan renovasi terbaru</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $daftar->count() }} pembaruan
                    </span>
                </div>

                @if ($daftar->isEmpty())
                    <div class="py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800">Belum ada progres yang dicatat</h3>
                        <p class="text-xs text-slate-500 mt-1">Surveyor lapangan dapat menambahkan progres renovasi dan foto dokumentasi.</p>
                        @can('surveyor')
                            @if ($dalamRenovasi)
                                <a href="{{ route('bantuan.progres.create', $rumah) }}"
                                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Progres Pertama
                                </a>
                            @endif
                        @endcan
                    </div>
                @else
                    {{-- Timeline Items --}}
                    <div class="relative pl-6 space-y-8 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                        @foreach ($daftar as $index => $p)
                            <div class="relative group">
                                {{-- Node Icon --}}
                                <div class="absolute -left-6 top-1 w-5 h-5 rounded-full {{ $index === 0 ? 'bg-forest text-white' : 'bg-slate-300 text-white' }} flex items-center justify-center text-[10px] shadow-sm">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                </div>

                                {{-- Kartu Progres --}}
                                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm space-y-4 hover:border-slate-300 transition">
                                    {{-- Header Kartu Progres --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-bold text-slate-900">{{ $p->tahap_pekerjaan }}</h3>
                                            @if ($index === 0)
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">
                                                    Terbaru
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Tombol Edit & Hapus: Hanya Surveyor (Mockup Page 9) --}}
                                        @can('surveyor')
                                            @if ($dalamRenovasi)
                                                <div class="flex items-center gap-1">
                                                    <a href="{{ route('bantuan.progres.edit', [$rumah, $p]) }}"
                                                       class="w-7 h-7 rounded-lg text-slate-400 hover:text-forest hover:bg-forest/10 flex items-center justify-center transition"
                                                       title="Ubah Progres">
                                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('bantuan.progres.destroy', [$rumah, $p]) }}"
                                                          class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus entri progres ini? Data akan disimpan dalam arsip.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="w-7 h-7 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition"
                                                                title="Hapus Progres">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endcan
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-slate-500 font-medium">Progres renovasi</span>
                                            <span class="font-bold text-slate-900">{{ $p->persentase }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-200/90 rounded-full h-2 overflow-hidden">
                                            <div class="bg-forest h-2 rounded-full" style="width: {{ $p->persentase }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Catatan --}}
                                    @if ($p->catatan)
                                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                                            {{ $p->catatan }}
                                        </p>
                                    @endif

                                    {{-- Dokumentasi Foto (Mockup Page 9 & SCRUM-128) --}}
                                    @if ($p->fotos->isNotEmpty())
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                                            @foreach ($p->fotos as $f)
                                                <a href="{{ route('bantuan.progres.foto', $f) }}" target="_blank"
                                                   class="block rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 hover:opacity-90 transition group shadow-sm">
                                                    <img src="{{ route('bantuan.progres.foto', $f) }}" alt="Foto progres" class="w-full h-full object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Footer Item --}}
                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-forest text-white flex items-center justify-center text-[10px] font-bold">
                                                {{ strtoupper(substr($rumah->surveyor?->nama ?? 'SV', 0, 2)) }}
                                            </div>
                                            <span class="text-slate-700 font-medium">{{ $rumah->surveyor?->nama ?? 'Petugas Lapangan' }}</span>
                                            <span class="text-slate-400 hidden sm:inline">&middot; Surveyor Lapangan</span>
                                        </div>
                                        <div class="flex items-center gap-1 font-mono text-[11px]">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                            {{ \Carbon\Carbon::parse($p->tanggal_progres)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Panduan Petugas & Aktivitas Terakhir (Mockup Page 9) --}}
        <div class="space-y-6">
            {{-- Catatan untuk Petugas --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <div class="w-8 h-8 rounded-xl bg-forest/10 text-forest flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Catatan untuk petugas</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Tambahkan progres setelah memastikan kondisi pekerjaan dan dokumentasi foto sesuai keadaan lapangan.
                </p>
            </div>

            {{-- Aktivitas Terakhir --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Aktivitas Terakhir</h3>
                <div class="space-y-3 text-xs">
                    @forelse ($daftar->take(3) as $act)
                        <div class="flex items-start gap-2.5 pb-2 border-b border-slate-100 last:border-0 last:pb-0">
                            <i data-lucide="clock" class="w-4 h-4 text-forest flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $act->tahap_pekerjaan }} ({{ $act->persentase }}%)</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($act->tanggal_progres)->format('d M Y') }} &middot; Oleh {{ $rumah->surveyor?->nama ?? 'Petugas' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-400 text-xs">Belum ada riwayat aktivitas.</p>
                    @endforelse
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-[11px] text-slate-400">Semua perubahan tersimpan permanen dalam riwayat audit log.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
