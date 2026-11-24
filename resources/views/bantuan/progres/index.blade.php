@extends('layouts.app')

@section('title', 'Progres Renovasi — ' . $rumah->nama_kepala_keluarga)

@section('content')
@php
    $dalamRenovasi = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI;
    $persenTerakhir = $bantuan->progresTerakhir();

    // 5 Tahapan Renovasi sesuai Mockup Surveyor Halaman 9
    $tahapanMaster = [
        ['label' => 'Persiapan',         'min' => 1],
        ['label' => 'Pekerjaan Atap',    'min' => 25],
        ['label' => 'Pekerjaan Dinding', 'min' => 50],
        ['label' => 'Pekerjaan Lantai',  'min' => 75],
        ['label' => 'Finishing',         'min' => 100],
    ];
    $tahapSelesaiCount = collect($tahapanMaster)->filter(fn ($t) => $persenTerakhir >= $t['min'])->count();
@endphp

<div x-data="{
        openModalTambah: {{ $errors->any() ? 'true' : 'false' }},
        editItemId: null,
        deleteItemId: null,
        lightboxOpen: false,
        lightboxImage: '',
        lightboxTitle: '',
        openPhoto(url, title) {
            this.lightboxImage = url;
            this.lightboxTitle = title;
            this.lightboxOpen = true;
        }
     }"
     @keydown.escape.window="openModalTambah = false; editItemId = null; deleteItemId = null; lightboxOpen = false"
     class="space-y-6 ra-page-enter">

    {{-- Header Section (Mockup Surveyor Halaman 9) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider mb-1">PEMANTAUAN BANTUAN</p>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Progres Renovasi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Catat perkembangan pekerjaan dan dokumentasi lapangan secara berkala.</p>
        </div>
        <div class="flex items-center gap-2.5">
            @can('surveyor')
                @if ($dalamRenovasi)
                    <button type="button"
                            @click="openModalTambah = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition ra-btn-press">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Tambah Progres
                    </button>
                @endif
            @endcan
            <a href="{{ auth()->user()?->isSurveyor() ? route('rumah.index', ['status' => 'Dalam Renovasi']) : route('bantuan.index', ['mode' => 'progres']) }}"
               class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-xs transition ra-btn-press">
                Kembali
            </a>
        </div>
    </div>

    {{-- Dropdown Pemilihan Rumah Penerima Bantuan (Mockup Surveyor Halaman 9) --}}
    @if (isset($daftarRumahRenovasi) && $daftarRumahRenovasi->count() >= 1)
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 ra-card-hover">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800">Pilih rumah penerima bantuan</h2>
                    <p class="text-[11px] text-slate-500 font-semibold">Data renovasi aktif di wilayah tugas Anda</p>
                </div>
            </div>
            <div class="relative w-full md:w-80">
                <select onchange="window.location.href = this.value"
                        class="w-full px-3.5 py-2.5 text-xs font-bold bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800 shadow-2xs cursor-pointer">
                    @foreach ($daftarRumahRenovasi as $r)
                        <option value="{{ route('bantuan.progres.index', $r) }}" {{ $r->id === $rumah->id ? 'selected' : '' }}>
                            {{ $r->nama_kepala_keluarga }} &middot; {{ $r->kode_registrasi }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    {{-- Kartu Ringkasan Penerima & Progres Terakhir (Mockup Surveyor Halaman 9) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm ra-card-hover">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            {{-- Data Warga --}}
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 block">Nama Pemilik</span>
                    <h2 class="text-base font-extrabold text-slate-900 leading-tight truncate">{{ $rumah->nama_kepala_keluarga }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5 truncate" title="{{ $rumah->alamatLengkap() }}">
                        {{ $rumah->alamatLengkap() }}
                    </p>
                </div>
            </div>

            {{-- Status Renovasi (Golden Rule Legenda Warna) --}}
            <div class="md:text-center md:border-x border-slate-100 md:px-4">
                <span class="text-[11px] text-slate-400 block mb-1">Status Renovasi</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $rumah->badgeStatusClass($rumah->status) }}">
                    {{ $rumah->status }}
                </span>
            </div>

            {{-- Progress Terakhir --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-medium text-slate-500">Progress terakhir</span>
                    <span class="text-2xl font-extrabold text-forest">{{ $persenTerakhir }}%</span>
                </div>
                <div class="w-full bg-slate-200/90 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-forest h-2.5 rounded-full transition-all duration-700" style="width: {{ $persenTerakhir }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5">
                    Diperbarui: {{ $daftar->first() ? \Carbon\Carbon::parse($daftar->first()->tanggal_progres)->format('d M Y') : 'Belum ada pembaruan' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Kartu Tahapan Renovasi 5 Langkah (Mockup Surveyor Halaman 9) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4 ra-card-hover">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Tahapan Renovasi</h2>
                <p class="text-xs text-slate-400 mt-0.5">Posisi pekerjaan saat ini dan tahap berikutnya</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                {{ max(1, $tahapSelesaiCount) }} dari 5 tahap
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 pt-1">
            @foreach ($tahapanMaster as $idx => $tm)
                @php
                    $isDone = $persenTerakhir >= $tm['min'];
                    $prevDone = $idx === 0 || $persenTerakhir >= $tahapanMaster[$idx - 1]['min'];
                    $isCurrent = !$isDone && $prevDone;
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl border {{ $isDone ? 'border-emerald-200 bg-emerald-50/40' : ($isCurrent ? 'border-forest bg-forest/5' : 'border-slate-100 bg-slate-50/50') }}">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold flex-shrink-0 {{ $isDone ? 'bg-forest text-white' : ($isCurrent ? 'border-2 border-forest text-forest bg-white' : 'bg-slate-200 text-slate-500') }}">
                        @if ($isDone)
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold {{ ($isDone || $isCurrent) ? 'text-slate-900' : 'text-slate-400' }} truncate">{{ $tm['label'] }}</p>
                        <p class="text-[10px] {{ $isCurrent ? 'text-forest font-semibold' : 'text-slate-400' }}">
                            {{ $isDone ? 'Selesai' : ($isCurrent ? 'Berjalan' : 'Berikutnya') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Layout Utama: Timeline Riwayat Progres & Panel Samping --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Riwayat Progres (Mockup Surveyor Halaman 9) --}}
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
                                <button type="button"
                                        @click="openModalTambah = true"
                                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition ra-btn-press">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Progres Pertama
                                </button>
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
                                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm space-y-4 hover:border-slate-300 transition ra-card-hover">
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

                                        {{-- Tombol Edit & Hapus Pop-up Modal: Hanya Surveyor (Mockup Halaman 9 & 11) --}}
                                        @can('surveyor')
                                            @if ($dalamRenovasi)
                                                <div class="flex items-center gap-1">
                                                    <button type="button"
                                                            @click="editItemId = {{ $p->id }}"
                                                            class="w-7 h-7 rounded-lg text-slate-400 hover:text-forest hover:bg-forest/10 flex items-center justify-center transition"
                                                            title="Ubah Progres">
                                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <button type="button"
                                                            @click="deleteItemId = {{ $p->id }}"
                                                            class="w-7 h-7 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition"
                                                            title="Hapus Progres">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
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
                                            <div class="bg-forest h-2 rounded-full transition-all duration-500" style="width: {{ $p->persentase }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Catatan --}}
                                    @if ($p->catatan)
                                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                                            {{ $p->catatan }}
                                        </p>
                                    @endif

                                    {{-- Dokumentasi Foto dengan Pop-up Lightbox (Mockup Halaman 9) --}}
                                    @if ($p->fotos->isNotEmpty())
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                                            @foreach ($p->fotos as $f)
                                                @php $fUrl = route('bantuan.progres.foto', $f); @endphp
                                                <button type="button"
                                                        @click="openPhoto('{{ $fUrl }}', '{{ $p->tahap_pekerjaan }} ({{ $p->persentase }}%)')"
                                                        class="block rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 hover:opacity-90 transition group shadow-sm">
                                                    <img src="{{ $fUrl }}" alt="Foto progres" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                                </button>
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
                                            <span class="text-slate-400 hidden sm:inline">&middot; Surveyor lapangan</span>
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

        {{-- Kolom Kanan: Panduan Petugas & Aktivitas Terakhir (Mockup Surveyor Halaman 9) --}}
        <div class="space-y-6">
            {{-- Catatan untuk Petugas --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3 ra-card-hover">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Catatan untuk petugas</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Tambahkan progres setelah memastikan kondisi pekerjaan dan dokumentasi foto sesuai keadaan lapangan.
                </p>
            </div>

            {{-- Aktivitas Terakhir --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4 ra-card-hover">
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
                    <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200/60 text-[11px] text-forest">
                        Semua perubahan tersimpan dalam riwayat aktivitas.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL POP-UP 1: TAMBAH PROGRES RENOVASI (MOCKUP SURVEYOR HALAMAN 11)    --}}
    {{-- ======================================================================= --}}
    @can('surveyor')
        @if ($dalamRenovasi)
            <div x-show="openModalTambah"
                 x-cloak
                 x-transition.opacity.duration.200ms
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.away="openModalTambah = false"
                     x-show="openModalTambah"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="bg-white rounded-3xl max-w-lg w-full p-7 shadow-2xl border border-slate-200 space-y-5">

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">PEMBARUAN LAPANGAN</p>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-0.5">Tambah Progres Renovasi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->kode_registrasi }}</p>
                        </div>
                        <button type="button" @click="openModalTambah = false"
                                class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-500">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('bantuan.progres.store', $rumah) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahap pekerjaan <span class="text-rose-600">*</span></label>
                            <select name="tahap_pekerjaan" required
                                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                @foreach (['Persiapan', 'Pondasi', 'Pekerjaan Atap', 'Pekerjaan Dinding', 'Pekerjaan Lantai', 'Finishing'] as $thp)
                                    <option value="{{ $thp }}" {{ old('tahap_pekerjaan') === $thp ? 'selected' : '' }}>{{ $thp }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Persentase progres <span class="text-rose-600">*</span></label>
                            <div class="flex items-center rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-forest/20 focus-within:border-forest bg-white">
                                <input type="number" name="persentase" min="{{ $persenTerakhir }}" max="100" value="{{ old('persentase', min(100, $persenTerakhir + 15)) }}" required
                                       class="w-full px-3.5 py-2.5 text-xs bg-white text-slate-800 focus:outline-none">
                                <span class="px-3.5 py-2.5 bg-slate-50 border-l border-slate-200 text-xs font-bold text-slate-600">%</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Masukkan progres aktual antara {{ $persenTerakhir }}–100%.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal pembaruan <span class="text-rose-600">*</span></label>
                            <input type="date" name="tanggal_progres" value="{{ old('tanggal_progres', now()->format('Y-m-d')) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan perkembangan <span class="text-rose-600">*</span></label>
                            <textarea name="catatan" rows="3" maxlength="1000" required
                                      placeholder="Jelaskan pekerjaan yang telah dilakukan, kondisi material, dan rencana berikutnya..."
                                      class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">{{ old('catatan') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Foto dokumentasi</label>
                            <label class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-forest/50 bg-slate-50/60 cursor-pointer transition">
                                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </span>
                                <div class="text-left">
                                    <span class="text-xs font-bold text-slate-800 block">Pilih atau ambil foto</span>
                                    <span class="text-[11px] text-slate-400">JPG atau PNG &middot; Maksimal 5 MB per foto</span>
                                </div>
                                <input type="file" name="foto[]" multiple accept="image/jpeg,image/png,image/webp" class="text-xs text-slate-500">
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                            <button type="button" @click="openModalTambah = false"
                                    class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition ra-btn-press">
                                Simpan Progres
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ======================================================================= --}}
            {{-- MODAL POP-UP 2: UBAH & HAPUS PROGRES RENOVASI PER ITEM                  --}}
            {{-- ======================================================================= --}}
            @foreach ($daftar as $p)
                {{-- Modal Edit Item --}}
                <div x-show="editItemId === {{ $p->id }}"
                     x-cloak
                     x-transition.opacity.duration.200ms
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div @click.away="editItemId = null"
                         class="bg-white rounded-3xl max-w-lg w-full p-7 shadow-2xl border border-slate-200 space-y-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">PEMBARUAN LAPANGAN</p>
                                <h3 class="text-xl font-extrabold text-slate-900 mt-0.5">Ubah Progres Renovasi</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->kode_registrasi }}</p>
                            </div>
                            <button type="button" @click="editItemId = null"
                                    class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-500">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bantuan.progres.update', [$rumah, $p]) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahap pekerjaan <span class="text-rose-600">*</span></label>
                                <input type="text" name="tahap_pekerjaan" value="{{ $p->tahap_pekerjaan }}" required
                                       class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Persentase (%) <span class="text-rose-600">*</span></label>
                                    <input type="number" name="persentase" min="0" max="100" value="{{ $p->persentase }}" required
                                           class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal pembaruan <span class="text-rose-600">*</span></label>
                                    <input type="date" name="tanggal_progres" value="{{ \Carbon\Carbon::parse($p->tanggal_progres)->format('Y-m-d') }}" required
                                           class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan perkembangan</label>
                                <textarea name="catatan" rows="3" maxlength="1000"
                                          class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">{{ $p->catatan }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tambah foto dokumentasi baru (opsional)</label>
                                <input type="file" name="foto[]" multiple accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-slate-600">
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                                <button type="button" @click="editItemId = null"
                                        class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-6 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition ra-btn-press">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal Konfirmasi Hapus Item --}}
                <div x-show="deleteItemId === {{ $p->id }}"
                     x-cloak
                     x-transition.opacity.duration.200ms
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div @click.away="deleteItemId = null"
                         class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-slate-200 space-y-5 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                            <i data-lucide="trash-2" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Hapus Laporan Progres?</h3>
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                Entri progres <strong>{{ $p->tahap_pekerjaan }} ({{ $p->persentase }}%)</strong> akan diarsipkan (soft delete) dan tercatat di audit log.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('bantuan.progres.destroy', [$rumah, $p]) }}" class="grid grid-cols-2 gap-3 pt-1">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="deleteItemId = null"
                                    class="py-2.5 px-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition ra-btn-press">
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    @endcan

    {{-- ======================================================================= --}}
    {{-- MODAL POP-UP 3: LIGHTBOX PREVIEW FOTO DOKUMENTASI                       --}}
    {{-- ======================================================================= --}}
    <div x-show="lightboxOpen"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs"
         @click.self="lightboxOpen = false">
        <div class="bg-white rounded-3xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-200">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-xs font-extrabold text-slate-900" x-text="lightboxTitle"></h4>
                <button type="button" @click="lightboxOpen = false" class="w-8 h-8 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-500">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-4 bg-slate-950/5 flex items-center justify-center">
                <img :src="lightboxImage" alt="Preview Dokumentasi" class="max-h-[72vh] w-auto object-contain rounded-xl shadow-sm">
            </div>
        </div>
    </div>

</div>
@endsection
