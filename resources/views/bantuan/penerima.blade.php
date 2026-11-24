@extends('layouts.app')

@php
    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
    $isBelumDicatat = !$bantuan;
    $isDalamRenovasi = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI;
    $isSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_SELESAI;

    $riwayatPenetapan = $rumah->riwayatStatus
        ->where('status_ke', \App\Models\RumahWarga::STATUS_PENERIMA_BANTUAN)
        ->sortByDesc('id')
        ->first();

    $tglPenetapan = $riwayatPenetapan?->created_at
        ?? ($bantuan?->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan) : $rumah->created_at);

    $namaVerifikator = $riwayatPenetapan?->user?->nama ?? 'Verifikator Wilayah';
@endphp

@section('title', 'Detail Penerima Bantuan — ' . $rumah->nama_kepala_keluarga)

@section('content')
<div x-data="{ openModalCatat: {{ $errors->any() ? 'true' : 'false' }} }"
     @keydown.escape.window="openModalCatat = false"
     class="space-y-6 ra-page-enter">

    {{-- Navigasi & Header (Mockup Verifikator Halaman 11) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('bantuan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-forest hover:underline mb-1 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Daftar Penerima Bantuan
            </a>
            <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider mt-0.5">DETAIL PENERIMA BANTUAN</p>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-0.5">{{ $rumah->kode_registrasi }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->wilayahSingkat() }}</p>
        </div>

        <div class="flex items-center gap-2.5">
            @if ($bantuan)
                <a href="{{ route('bantuan.show', $rumah) }}"
                   class="px-4 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-1.5 ra-btn-press">
                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                    Review Penyelesaian Renovasi
                </a>
            @endif
            <a href="{{ route('bantuan.index') }}"
               class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-xs transition ra-btn-press">
                Kembali
            </a>
        </div>
    </div>

    {{-- Kartu Utama: Nama Pemilik, Status, dan Tombol + Catat Bantuan (Mockup Halaman 11) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm ra-card-hover">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            {{-- Nama Pemilik --}}
            <div class="flex items-center gap-4 md:col-span-5">
                <div class="w-14 h-14 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 font-medium block">Nama Pemilik</span>
                    <h2 class="text-lg font-extrabold text-slate-900 leading-tight truncate">{{ $rumah->nama_kepala_keluarga }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->alamatLengkap() }}</p>
                </div>
            </div>

            {{-- Status & Kategori --}}
            <div class="md:col-span-4 md:border-x border-slate-100 md:px-6">
                <span class="text-[11px] text-slate-400 font-medium block mb-1">Status</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $rumah->badgeStatusClass($rumah->status) }}">
                    {{ $rumah->status }}
                </span>
                <p class="text-[11px] text-slate-400 mt-1.5">
                    Kategori: Tidak Layak Huni &middot; Keputusan manual Verifikator
                </p>
            </div>

            {{-- Aksi Catat Bantuan / Status Pencatatan --}}
            <div class="md:col-span-3 flex flex-col items-start md:items-end justify-center">
                @if ($isBelumDicatat)
                    @can('verifikator')
                        <button type="button"
                                @click="openModalCatat = true"
                                class="w-full sm:w-auto px-5 py-3 bg-forest hover:bg-forest-light text-white text-xs font-extrabold rounded-xl shadow-sm transition inline-flex items-center justify-center gap-2 ra-btn-press">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Catat Bantuan</span>
                        </button>
                    @endcan
                    <span class="text-[11px] text-slate-400 mt-1.5">Bantuan belum dicatat</span>
                @else
                    <div class="text-left md:text-right">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                            Bantuan Tercatat ({{ $bantuan->sumber_program ?: 'APBD' }})
                        </span>
                        <p class="text-xs font-extrabold text-slate-900 mt-1.5">
                            Rp {{ number_format((float) $bantuan->nominal, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $bantuan->nomor_sk }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Banner Informasi Status Bantuan (Mockup Halaman 11) --}}
    @if ($isBelumDicatat)
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 text-xs text-emerald-950 flex items-start gap-3 shadow-2xs">
            <div class="w-7 h-7 rounded-full bg-forest/15 text-forest flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="font-bold text-emerald-950">Rumah telah ditetapkan sebagai penerima bantuan</h3>
                <p class="text-emerald-800 mt-0.5">Status baru akan berubah menjadi Dalam Renovasi setelah Verifikator mencatat bantuan berdasarkan dokumen resmi.</p>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-purple-50/80 border border-purple-200/80 text-xs text-purple-950 flex items-start justify-between gap-3 shadow-2xs">
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-purple-950">Data penyaluran bantuan telah tercatat</h3>
                    <p class="text-purple-800 mt-0.5">
                        Disalurkan pada {{ $bantuan->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan)->translatedFormat('d F Y') : '-' }} ({{ $bantuan->jenis_bantuan }} &middot; SK {{ $bantuan->nomor_sk }}). Progres saat ini: <strong>{{ $persen }}%</strong>.
                    </p>
                </div>
            </div>
            <a href="{{ route('bantuan.show', $rumah) }}" class="text-xs font-bold text-purple-800 hover:underline whitespace-nowrap self-center">
                Buka Review Renovasi &rarr;
            </a>
        </div>
    @endif

    {{-- Kartu Informasi Rumah (Mockup Halaman 11) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5 ra-card-hover">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Informasi Rumah</h2>
                <p class="text-xs text-slate-400">Data rumah penerima bantuan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8 text-xs">
            <div class="pb-3 border-b border-slate-100">
                <span class="text-slate-400 block mb-1">NIK Pemilik</span>
                <span class="font-mono font-bold text-slate-900 text-sm">{{ $rumah->nik_enc ? $rumah->nikTersamar() : '-' }}</span>
            </div>
            <div class="pb-3 border-b border-slate-100">
                <span class="text-slate-400 block mb-1">Jumlah Keluarga</span>
                <span class="font-bold text-slate-900 text-sm">{{ ($rumah->jumlah_anggota_kk ?? $rumah->jumlah_anggota ?? 4) }} orang</span>
            </div>
            <div class="pb-3 border-b border-slate-100">
                <span class="text-slate-400 block mb-1">Luas Bangunan</span>
                <span class="font-bold text-slate-900 text-sm">{{ $rumah->luas_rumah ? $rumah->luas_rumah . ' m²' : '48 m²' }}</span>
            </div>
            <div class="pb-3 border-b border-slate-100">
                <span class="text-slate-400 block mb-1">Surveyor</span>
                <span class="font-bold text-slate-900 text-sm">{{ $rumah->surveyor?->nama ?? 'Surveyor Lapangan' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">Verifikator</span>
                <span class="font-bold text-slate-900 text-sm">{{ $namaVerifikator }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">Tanggal Penetapan</span>
                <span class="font-bold text-slate-900 text-sm">{{ $tglPenetapan ? $tglPenetapan->translatedFormat('d F Y') : '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Jika Bantuan Sudah Dicatat, tampilkan juga Rincian Dokumen Bantuan --}}
    @if ($bantuan)
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4 ra-card-hover">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Informasi Bantuan</h2>
                        <p class="text-xs text-slate-400">Data administratif berdasarkan dokumen resmi</p>
                    </div>
                </div>
                <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">Tercatat</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Tanggal Bantuan</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $bantuan->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan)->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block">Jenis Bantuan</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">{{ $bantuan->jenis_bantuan }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Nominal Bantuan</span>
                    <span class="font-extrabold text-slate-900 mt-0.5 block">Rp {{ number_format((float) $bantuan->nominal, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Sumber Program</span>
                    <span class="font-bold text-forest mt-0.5 block">{{ $bantuan->sumber_program ?? 'APBD' }}</span>
                </div>
                <div class="md:col-span-2">
                    <span class="text-slate-400 block">Nomor SK</span>
                    <span class="font-mono font-bold text-slate-800 mt-0.5 block">{{ $bantuan->nomor_sk }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Kartu Riwayat Status (Mockup Halaman 11) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5 ra-card-hover">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Riwayat Status</h2>
                <p class="text-xs text-slate-400">Setiap perubahan tersimpan permanen</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($rumah->riwayatStatus->sortByDesc('id') as $r)
                <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0 mt-0.5">
                            @if ($r->status_ke === 'Penerima Bantuan')
                                <i data-lucide="users" class="w-4 h-4"></i>
                            @elseif ($r->status_ke === 'Dalam Renovasi')
                                <i data-lucide="hammer" class="w-4 h-4"></i>
                            @elseif ($r->status_ke === 'Selesai')
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            @else
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900">
                                @if ($r->status_ke === 'Penerima Bantuan')
                                    Ditetapkan sebagai penerima bantuan
                                @elseif ($r->status_ke === 'Dalam Renovasi')
                                    Bantuan dicatat &amp; renovasi dimulai
                                @elseif ($r->status_ke === 'Selesai')
                                    Renovasi ditetapkan selesai
                                @elseif ($r->status_ke === 'Diajukan')
                                    Data diajukan
                                @else
                                    Perubahan status ke {{ $r->status_ke }}
                                @endif
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $r->user?->nama ?? 'Petugas Sistem' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1.5">
                                Status berubah: {{ $r->status_dari }} &rarr; <span class="font-semibold text-slate-700">{{ $r->status_ke }}</span>
                            </p>
                            @if ($r->catatan)
                                <p class="text-[11px] text-slate-400 mt-0.5 italic">"{{ $r->catatan }}"</p>
                            @endif
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">
                        {{ $r->created_at?->format('d M Y, H:i') }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4 text-center">Belum ada riwayat perubahan status.</p>
            @endforelse
        </div>
    </div>

    {{-- 3 Kartu Bawah: Status Renovasi, Alur Bantuan, Hak Akses (Mockup Halaman 11) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Kartu 1: Status Renovasi --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3 ra-card-hover">
            <h3 class="text-xs font-extrabold text-slate-900">Status Renovasi</h3>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $rumah->badgeStatusClass($rumah->status) }}">
                    {{ $rumah->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                @if ($isBelumDicatat)
                    Menunggu data bantuan dicatat oleh Verifikator.
                @elseif ($isDalamRenovasi)
                    Bantuan telah dicatat. Surveyor melaporkan progres lapangan hingga 100%.
                @else
                    Renovasi telah selesai dan diverifikasi oleh Verifikator.
                @endif
            </p>
        </div>

        {{-- Kartu 2: Alur Bantuan --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3 ra-card-hover">
            <h3 class="text-xs font-extrabold text-slate-900">Alur Bantuan</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-forest flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i data-lucide="check" class="w-3 h-3"></i>
                    </span>
                    <div>
                        <p class="font-bold text-slate-900 leading-none">Verifikasi</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Selesai</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full {{ $isBelumDicatat ? 'bg-forest text-white' : 'bg-emerald-100 text-forest' }} flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">
                        @if ($isBelumDicatat) 2 @else <i data-lucide="check" class="w-3 h-3"></i> @endif
                    </span>
                    <div>
                        <p class="font-bold text-slate-900 leading-none">Penerima Bantuan</p>
                        <p class="text-[11px] {{ $isBelumDicatat ? 'text-forest font-semibold' : 'text-slate-400' }} mt-0.5">
                            {{ $isBelumDicatat ? 'Status saat ini' : 'Selesai' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full {{ $bantuan ? 'bg-emerald-100 text-forest' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">
                        @if ($bantuan) <i data-lucide="check" class="w-3 h-3"></i> @else 3 @endif
                    </span>
                    <div>
                        <p class="font-bold {{ $bantuan ? 'text-slate-900' : 'text-slate-400' }} leading-none">Bantuan Dicatat</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $bantuan ? 'Selesai' : 'Belum dimulai' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full {{ $isDalamRenovasi ? 'bg-forest text-white' : ($isSelesai ? 'bg-emerald-100 text-forest' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">
                        @if ($isSelesai) <i data-lucide="check" class="w-3 h-3"></i> @else 4 @endif
                    </span>
                    <div>
                        <p class="font-bold {{ ($isDalamRenovasi || $isSelesai) ? 'text-slate-900' : 'text-slate-400' }} leading-none">Dalam Renovasi</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $isDalamRenovasi ? 'Status saat ini' : ($isSelesai ? 'Selesai' : 'Belum dimulai') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full {{ $isSelesai ? 'bg-forest text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">
                        @if ($isSelesai) <i data-lucide="check" class="w-3 h-3"></i> @else 5 @endif
                    </span>
                    <div>
                        <p class="font-bold {{ $isSelesai ? 'text-slate-900' : 'text-slate-400' }} leading-none">Selesai</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $isSelesai ? 'Status saat ini' : 'Belum dimulai' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu 3: Hak Akses --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3 ra-card-hover">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <h3 class="text-xs font-extrabold text-slate-900">Hak Akses</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Hanya <strong>Verifikator</strong> dapat mencatat bantuan. <strong>Surveyor</strong> mencatat progress setelah status Dalam Renovasi. <strong>Admin</strong> hanya dapat melihat.
            </p>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL POP-UP: CATAT BANTUAN (MOCKUP VERIFIKATOR HALAMAN 10)             --}}
    {{-- ======================================================================= --}}
    @can('verifikator')
        @if ($isBelumDicatat)
            <div x-show="openModalCatat"
                 x-cloak
                 x-transition.opacity.duration.200ms
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.away="openModalCatat = false"
                     x-show="openModalCatat"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                     class="bg-white rounded-3xl max-w-lg w-full p-7 shadow-2xl border border-slate-200 space-y-5">

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">FR-20 &middot; VERIFIKATOR</p>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-0.5">Catat Bantuan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi bantuan yang diberikan sebelum memulai proses renovasi.</p>
                        </div>
                        <button type="button" @click="openModalCatat = false"
                                class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-500 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('bantuan.salurkan.store', $rumah) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Bantuan <span class="text-rose-600">*</span></label>
                                <input type="date" name="tanggal_bantuan" value="{{ old('tanggal_bantuan', now()->format('Y-m-d')) }}" required
                                       class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Bantuan <span class="text-rose-600">*</span></label>
                                <select name="jenis_bantuan" required
                                        class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                    <option value="">Pilih jenis bantuan</option>
                                    <option value="Bantuan Renovasi Rumah" {{ old('jenis_bantuan') === 'Bantuan Renovasi Rumah' ? 'selected' : '' }}>Bantuan Renovasi Rumah</option>
                                    <option value="Material bangunan" {{ old('jenis_bantuan') === 'Material bangunan' ? 'selected' : '' }}>Material bangunan</option>
                                    <option value="Dana tunai" {{ old('jenis_bantuan') === 'Dana tunai' ? 'selected' : '' }}>Dana tunai</option>
                                    <option value="BSPS Renovasi Atap & Dinding" {{ old('jenis_bantuan') === 'BSPS Renovasi Atap & Dinding' ? 'selected' : '' }}>BSPS Renovasi Atap &amp; Dinding</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nominal Bantuan <span class="text-rose-600">*</span></label>
                            <div class="flex items-center rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-forest/20 focus-within:border-forest bg-white">
                                <span class="px-3.5 py-2.5 bg-slate-50 border-r border-slate-200 text-xs font-bold text-slate-600">Rp</span>
                                <input type="number" name="nominal" min="0" step="1000" value="{{ old('nominal', 20000000) }}" required
                                       placeholder="0"
                                       class="w-full px-3.5 py-2.5 text-xs bg-white text-slate-800 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Sumber Program <span class="text-rose-600">*</span></label>
                            <select name="sumber_program" required
                                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                <option value="APBN" {{ old('sumber_program') === 'APBN' ? 'selected' : '' }}>APBN (Program Bantuan Rumah Tidak Layak Huni Pusat)</option>
                                <option value="APBD" {{ old('sumber_program') === 'APBD' ? 'selected' : '' }}>APBD (Program Bantuan Renovasi Daerah)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor SK <span class="text-rose-600">*</span></label>
                            <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" required
                                   placeholder="Masukkan nomor SK (Contoh: SK/RTLH/2026/0031)"
                                   class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                        </div>

                        <div class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200/80 text-xs text-forest flex items-center gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
                            <span>Pastikan data bantuan sesuai dengan dokumen resmi yang diterbitkan.</span>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2">
                            <button type="button" @click="openModalCatat = false"
                                    class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition ra-btn-press">
                                Simpan Bantuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

</div>
@endsection
