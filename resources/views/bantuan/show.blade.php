@extends('layouts.app')

@php
    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
    $bisaSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI && $persen >= 100;
    $isSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_SELESAI;

    $riwayatSelesai = $rumah->riwayatStatus
        ->where('status_ke', \App\Models\RumahWarga::STATUS_SELESAI)
        ->sortByDesc('id')
        ->first();

    $progresTerakhirItem = $bantuan?->progres?->sortByDesc('tanggal_progres')->first();

    // Foto Sebelum (dari survei awal) & Sesudah (dari progres terbaru)
    $fotoSebelum = null;
    if ($rumah->survei->isNotEmpty()) {
        foreach ($rumah->survei->first()->komponenSurvei as $k) {
            if ($k->foto->isNotEmpty()) {
                $fotoSebelum = $k->foto->first();
                break;
            }
        }
    }

    $fotoSesudah = null;
    if ($bantuan && $bantuan->progres->isNotEmpty()) {
        $lastProgres = $bantuan->progres->sortByDesc('persentase')->first();
        if ($lastProgres && $lastProgres->fotos->isNotEmpty()) {
            $fotoSesudah = $lastProgres->fotos->first();
        }
    }
@endphp

@section('title', 'Detail Renovasi — ' . $rumah->nama_kepala_keluarga)

@section('content')
<div x-data="{
        openModalSelesai: false,
        lightboxOpen: false,
        lightboxImage: '',
        lightboxTitle: '',
        openPhoto(url, title) {
            this.lightboxImage = url;
            this.lightboxTitle = title;
            this.lightboxOpen = true;
        }
     }"
     @keydown.escape.window="openModalSelesai = false; lightboxOpen = false"
     class="space-y-6 ra-page-enter">

    {{-- Navigasi & Aksi Header: REVIEW PENYELESAIAN RENOVASI (Mockup Halaman 2, 14, 15) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('bantuan.index', ['mode' => 'progres']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-forest hover:underline mb-1 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Monitoring Renovasi
            </a>
            <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider mt-0.5">REVIEW PENYELESAIAN RENOVASI</p>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-0.5">{{ $rumah->kode_registrasi }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->wilayahSingkat() }}</p>
        </div>

        <div class="flex items-center gap-2.5">
            {{-- RBAC: HANYA Surveyor yang bisa menambah/mengedit laporan progres renovasi --}}
            @can('surveyor')
                @if ($bantuan && !$isSelesai)
                    <a href="{{ route('bantuan.progres.index', $rumah) }}"
                       class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-1.5 ra-btn-press">
                        <i data-lucide="hammer" class="w-4 h-4 text-forest"></i>
                        Kelola Progres
                    </a>
                @endif
            @endcan

            {{-- RBAC: Verifikator menetapkan renovasi selesai saat progres 100% --}}
            @can('verifikator')
                @if ($bisaSelesai)
                    <button type="button"
                            @click="openModalSelesai = true"
                            class="px-5 py-3 bg-forest hover:bg-forest-light text-white text-xs font-extrabold rounded-xl shadow-sm transition inline-flex items-center gap-2 ra-btn-press">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Tetapkan Selesai
                    </button>
                @endif
            @endcan
        </div>
    </div>

    {{-- Kartu Ringkasan 4 Kolom (Mockup Halaman 15 & Halaman 2) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm ra-card-hover">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            {{-- Kolom 1: Penerima Bantuan --}}
            <div class="flex items-center gap-4 md:col-span-4">
                <div class="w-14 h-14 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 font-medium block">Penerima Bantuan</span>
                    <h2 class="text-base font-extrabold text-slate-900 leading-tight truncate">{{ $rumah->nama_kepala_keluarga }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5 truncate" title="{{ $rumah->alamatLengkap() }}">{{ $rumah->alamatLengkap() }}</p>
                </div>
            </div>

            {{-- Kolom 2: Status Saat Ini (Golden Rule Legenda Warna) --}}
            <div class="md:col-span-2 md:border-l border-slate-100 md:pl-5">
                <span class="text-[11px] text-slate-400 font-medium block mb-1.5">Status Saat Ini</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $rumah->badgeStatusClass($rumah->status) }}">
                    {{ $rumah->status }}
                </span>
            </div>

            {{-- Kolom 3: Progres Renovasi --}}
            <div class="md:col-span-3 md:border-x border-slate-100 md:px-5">
                <span class="text-[11px] text-slate-400 font-medium block">Progres Renovasi</span>
                <span class="text-2xl font-extrabold text-slate-900 block mt-0.5">{{ $persen }}%</span>
                <div class="w-full bg-slate-200/90 rounded-full h-2 overflow-hidden mt-2">
                    <div class="bg-forest h-2 rounded-full transition-all duration-700" style="width: {{ $persen }}%"></div>
                </div>
            </div>

            {{-- Kolom 4: Laporan Terakhir --}}
            <div class="md:col-span-3 md:pl-2">
                <span class="text-[11px] text-slate-400 font-medium block">Laporan Terakhir</span>
                <span class="text-sm font-extrabold text-slate-900 block mt-0.5">
                    {{ $progresTerakhirItem ? \Carbon\Carbon::parse($progresTerakhirItem->tanggal_progres)->translatedFormat('d F Y') : 'Belum ada laporan' }}
                </span>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    {{ $rumah->surveyor?->nama ?? 'Surveyor Lapangan' }} &middot; Surveyor
                </p>
            </div>
        </div>
    </div>

    {{-- Banner Status Workflow (Mockup Halaman 15 & Halaman 2) --}}
    @if ($bisaSelesai)
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 flex items-start gap-3 shadow-2xs">
            <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="font-bold text-amber-950">Renovasi siap ditinjau</h3>
                <p class="mt-0.5 text-amber-800">Progres renovasi telah mencapai 100%. Tinjau dokumentasi dan laporan sebelum menetapkan status Selesai.</p>
            </div>
        </div>
    @elseif ($isSelesai)
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-xs text-emerald-950 flex items-start gap-3 shadow-2xs">
            <div class="w-7 h-7 rounded-full bg-forest text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="font-bold text-emerald-950">Renovasi telah ditetapkan selesai</h3>
                <p class="mt-0.5 text-emerald-800">
                    Keputusan akhir dicatat oleh {{ $riwayatSelesai?->user?->nama ?? 'Verifikator' }} pada {{ $riwayatSelesai?->created_at ? $riwayatSelesai->created_at->translatedFormat('d F Y, H.i') . ' WIB' : now()->translatedFormat('d F Y') }}.
                </p>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200/80 text-xs text-purple-950 flex items-start justify-between gap-3 shadow-2xs">
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-purple-950">Pemantauan progres renovasi sedang berjalan ({{ $persen }}%)</h3>
                    <p class="mt-0.5 text-purple-800">
                        Penetapan status <strong>Selesai</strong> hanya dapat dilakukan setelah Surveyor melaporkan progres renovasi hingga mencapai <strong>100%</strong>.
                    </p>
                </div>
            </div>
            <a href="{{ route('bantuan.penerima.show', $rumah) }}" class="text-xs font-bold text-purple-800 hover:underline whitespace-nowrap self-center">
                Detail Penerima &rarr;
            </a>
        </div>
    @endif

    {{-- Layout Grid: Konten Utama & Sidebar --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri (2 Kolom): Ringkasan Penyelesaian, Riwayat Progres, Foto Sebelum-Sesudah, Informasi Bantuan --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Kartu Ringkasan Penyelesaian (Khusus Saat Status Selesai — Mockup Halaman 2) --}}
            @if ($isSelesai)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4 ra-card-hover">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Ringkasan Penyelesaian</h2>
                                <p class="text-xs text-slate-400">Catatan final bersifat hanya baca</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200/70">Terverifikasi</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="pb-3 border-b border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Status Akhir</span>
                            <span class="font-extrabold text-slate-900">Selesai</span>
                        </div>
                        <div class="pb-3 border-b border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Tanggal Penyelesaian</span>
                            <span class="font-extrabold text-slate-900">
                                {{ $riwayatSelesai?->created_at ? $riwayatSelesai->created_at->translatedFormat('d F Y, H.i') . ' WIB' : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Ditetapkan Oleh</span>
                            <span class="font-extrabold text-slate-900">{{ $riwayatSelesai?->user?->nama ?? 'Verifikator' }} &middot; Verifikator</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Progres Terakhir</span>
                            <span class="font-extrabold text-slate-900">100%</span>
                        </div>
                    </div>

                    @if ($riwayatSelesai?->catatan)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs text-slate-700 italic">
                            "{{ $riwayatSelesai->catatan }}"
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/70 text-[11px] text-forest flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 flex-shrink-0"></i>
                        <span>Catatan penyelesaian tersimpan permanen dan tidak dapat diubah atau dihapus.</span>
                    </div>
                </div>
            @endif

            {{-- Riwayat Progres Renovasi (Mockup Halaman 15 & Halaman 2) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-6 ra-card-hover">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Riwayat Progres Renovasi</h2>
                            <p class="text-xs text-slate-400">Laporan lapangan oleh {{ $rumah->surveyor?->nama ?? 'Surveyor Lapangan' }} &middot; Surveyor</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">
                        {{ $bantuan?->progres?->count() ?? 0 }} laporan
                    </span>
                </div>

                @if (!$bantuan || $bantuan->progres->isEmpty())
                    <p class="text-xs text-slate-500 text-center py-8">Belum ada laporan progres yang dicatat oleh Surveyor.</p>
                @else
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                        @foreach ($bantuan->progres->sortByDesc('tanggal_progres') as $p)
                            <div class="relative">
                                @if ($loop->first)
                                    <span class="w-6 h-6 rounded-full bg-forest text-white flex items-center justify-center absolute -left-6 top-1 ring-4 ring-white shadow-2xs">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-white border-2 border-slate-300 absolute -left-[22px] top-1.5 ring-4 ring-white"></span>
                                @endif

                                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-slate-300 transition space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-sm font-extrabold text-slate-900">{{ $p->tahap_pekerjaan }}</h3>
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($p->tanggal_progres)->translatedFormat('d F Y') }}</p>
                                        </div>
                                        <span class="text-sm font-extrabold text-forest">{{ $p->persentase }}%</span>
                                    </div>

                                    @if ($p->catatan)
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $p->catatan }}</p>
                                    @endif

                                    <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-forest h-1.5 rounded-full transition-all duration-500" style="width: {{ $p->persentase }}%"></div>
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            {{ $p->fotos->count() }} foto dokumentasi
                                        </span>
                                        <span>{{ $rumah->surveyor?->nama ?? 'Surveyor Lapangan' }}</span>
                                    </div>

                                    {{-- Galeri Foto Progres dengan Lightbox Pop-up --}}
                                    @if ($p->fotos->isNotEmpty())
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1">
                                            @foreach ($p->fotos as $f)
                                                @php $fUrl = route('bantuan.progres.foto', $f); @endphp
                                                <button type="button"
                                                        @click="openPhoto('{{ $fUrl }}', '{{ $p->tahap_pekerjaan }} ({{ $p->persentase }}%)')"
                                                        class="aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100 hover:opacity-90 transition block shadow-2xs group">
                                                    <img src="{{ $fUrl }}" alt="Dokumentasi progres" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Dokumentasi Sebelum & Sesudah (Mockup Halaman 15 & Halaman 2) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4 ra-card-hover">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Dokumentasi Sebelum &amp; Sesudah</h2>
                            <p class="text-xs text-slate-400">Perbandingan kondisi rumah penerima bantuan</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">Ditinjau wajib</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Sebelum --}}
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/70">
                        <div class="aspect-video bg-slate-200 relative flex items-center justify-center overflow-hidden">
                            @if ($fotoSebelum)
                                @php $urlSebelum = route('survei.foto.show', $fotoSebelum); @endphp
                                <img src="{{ $urlSebelum }}"
                                     @click="openPhoto('{{ $urlSebelum }}', 'Kondisi Sebelum Renovasi — {{ $rumah->nama_kepala_keluarga }}')"
                                     alt="Foto sebelum renovasi"
                                     class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-300">
                            @else
                                <div class="text-slate-400 text-xs flex flex-col items-center gap-1">
                                    <i data-lucide="image" class="w-6 h-6"></i>
                                    <span>Foto awal survei</span>
                                </div>
                            @endif
                            <span class="absolute bottom-2.5 left-2.5 px-2.5 py-0.5 bg-forest text-white rounded-full text-[10px] font-bold shadow-xs">
                                Sebelum
                            </span>
                        </div>
                        <div class="p-3.5 text-xs">
                            <span class="font-bold text-slate-900 block">
                                {{ $rumah->survei->first()?->tanggal_survei ? $rumah->survei->first()->tanggal_survei->translatedFormat('d F Y') : 'Kondisi Awal' }}
                            </span>
                            <span class="text-slate-400 text-[11px]">Atap dan dinding sebelum diperbaiki</span>
                        </div>
                    </div>

                    {{-- Sesudah --}}
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/70">
                        <div class="aspect-video bg-slate-200 relative flex items-center justify-center overflow-hidden">
                            @if ($fotoSesudah)
                                @php $urlSesudah = route('bantuan.progres.foto', $fotoSesudah); @endphp
                                <img src="{{ $urlSesudah }}"
                                     @click="openPhoto('{{ $urlSesudah }}', 'Kondisi Sesudah Renovasi ({{ $persen }}%) — {{ $rumah->nama_kepala_keluarga }}')"
                                     alt="Foto sesudah renovasi"
                                     class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-300">
                            @else
                                <div class="text-slate-400 text-xs flex flex-col items-center gap-1">
                                    <i data-lucide="image" class="w-6 h-6"></i>
                                    <span>Foto akhir progres</span>
                                </div>
                            @endif
                            <span class="absolute bottom-2.5 left-2.5 px-2.5 py-0.5 bg-emerald-700 text-white rounded-full text-[10px] font-bold shadow-xs">
                                Sesudah
                            </span>
                        </div>
                        <div class="p-3.5 text-xs">
                            <span class="font-bold text-slate-900 block">
                                {{ $progresTerakhirItem ? \Carbon\Carbon::parse($progresTerakhirItem->tanggal_progres)->translatedFormat('d F Y') : 'Kondisi Akhir' }}
                            </span>
                            <span class="text-slate-400 text-[11px]">Dokumentasi akhir progres {{ $persen }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informasi Bantuan (Mockup Halaman 15 & Halaman 2) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4 ra-card-hover">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Informasi Bantuan</h2>
                            <p class="text-xs text-slate-400">Data administratif berdasarkan dokumen resmi</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200/60">
                        {{ $bantuan ? 'Tercatat' : 'Belum Tercatat' }}
                    </span>
                </div>

                @if ($bantuan)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="pb-3 border-b border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Tanggal Bantuan</span>
                            <span class="font-extrabold text-slate-900">
                                {{ $bantuan->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan)->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>
                        <div class="pb-3 border-b border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Jenis Bantuan</span>
                            <span class="font-extrabold text-slate-900">{{ $bantuan->jenis_bantuan }}</span>
                        </div>
                        <div class="pb-3 border-b border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Nominal Bantuan</span>
                            <span class="font-extrabold text-slate-900">Rp{{ number_format((float) $bantuan->nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="pb-3 border-b border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Sumber Program</span>
                            <span class="font-extrabold text-slate-900">{{ $bantuan->sumber_program ?? 'APBN' }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 block mb-0.5">Nomor SK</span>
                            <span class="font-mono font-extrabold text-slate-900">{{ $bantuan->nomor_sk }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 text-xs text-slate-600 flex items-center justify-between gap-4">
                        <span>Data penyaluran bantuan untuk rumah ini belum dicatat.</span>
                        <a href="{{ route('bantuan.penerima.show', $rumah) }}" class="px-3.5 py-2 rounded-xl bg-forest text-white font-bold hover:bg-forest-light transition whitespace-nowrap">
                            Buka Detail Penerima &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Status Renovasi, Hak Akses, Aturan Penyelesaian (Mockup Halaman 15 & Halaman 2) --}}
        <div class="space-y-6">
            {{-- Kartu 1: Status Renovasi Stepper (Mockup Halaman 15) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4 ra-card-hover">
                <h3 class="text-sm font-extrabold text-slate-900">Status Renovasi</h3>
                <div class="space-y-3.5 text-xs">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-forest flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-900 leading-none">Verifikasi</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Selesai</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full {{ $bantuan ? 'bg-emerald-100 text-forest' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center flex-shrink-0 font-bold text-[11px]">
                            @if ($bantuan) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 2 @endif
                        </span>
                        <div>
                            <p class="font-bold text-slate-900 leading-none">Bantuan Dicatat</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $bantuan ? 'Selesai' : 'Menunggu pencatatan' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full {{ ($persen > 0 || $isSelesai) ? 'bg-emerald-100 text-forest' : 'bg-forest text-white' }} flex items-center justify-center flex-shrink-0 font-bold text-[11px]">
                            @if ($persen > 0 || $isSelesai) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 3 @endif
                        </span>
                        <div>
                            <p class="font-bold text-slate-900 leading-none">Dalam Renovasi</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ ($persen >= 100 || $isSelesai) ? 'Selesai' : 'Sedang berjalan' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full {{ $isSelesai ? 'bg-emerald-100 text-forest' : ($persen >= 100 ? 'bg-forest text-white' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center flex-shrink-0 font-bold text-[11px]">
                            @if ($isSelesai) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 4 @endif
                        </span>
                        <div>
                            <p class="font-bold {{ $persen >= 100 ? 'text-slate-900' : 'text-slate-400' }} leading-none">Progres 100%</p>
                            <p class="text-[11px] {{ $bisaSelesai ? 'text-forest font-semibold' : 'text-slate-400' }} mt-0.5">
                                {{ $isSelesai ? 'Selesai' : ($bisaSelesai ? 'Status saat ini' : 'Menunggu 100%') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full {{ $isSelesai ? 'bg-forest text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center flex-shrink-0 font-bold text-[11px]">
                            5
                        </span>
                        <div>
                            <p class="font-bold {{ $isSelesai ? 'text-slate-900' : 'text-slate-400' }} leading-none">Selesai</p>
                            <p class="text-[11px] {{ $isSelesai ? 'text-forest font-semibold' : 'text-slate-400' }} mt-0.5">
                                {{ $isSelesai ? 'Status saat ini' : 'Menunggu verifikator' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu 2: Hak Akses (Mockup Halaman 15) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3 ra-card-hover">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Hak Akses</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Hanya <strong>Verifikator</strong> dapat menetapkan renovasi selesai. <strong>Surveyor</strong> melaporkan progres dan foto, sedangkan <strong>Admin</strong> hanya dapat melihat catatan.
                </p>
            </div>

            {{-- Kartu 3: Aturan Penyelesaian (Mockup Halaman 15) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3 ra-card-hover">
                <h3 class="text-sm font-extrabold text-slate-900">Aturan Penyelesaian</h3>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-center gap-2.5">
                        <i data-lucide="check" class="w-4 h-4 {{ $persen >= 100 ? 'text-forest' : 'text-slate-300' }}"></i>
                        <span>Progres telah mencapai 100%</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i data-lucide="clock" class="w-4 h-4 text-forest"></i>
                        <span>Status tidak berubah otomatis</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i data-lucide="eye" class="w-4 h-4 text-forest"></i>
                        <span>Dokumentasi perlu ditinjau</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL POP-UP: TETAPKAN STATUS RENOVASI SELESAI? (MOCKUP HALAMAN 14)     --}}
    {{-- ======================================================================= --}}
    @can('verifikator')
        @if ($bisaSelesai)
            <div x-show="openModalSelesai"
                 x-cloak
                 x-transition.opacity.duration.200ms
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.away="openModalSelesai = false"
                     x-show="openModalSelesai"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                     class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-slate-200 space-y-5">

                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-forest flex items-center justify-center mx-auto">
                        <i data-lucide="check" class="w-6 h-6"></i>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-extrabold text-slate-900">Tetapkan Status Renovasi Selesai?</h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Pastikan hasil renovasi dan dokumentasi telah ditinjau. Setelah dikonfirmasi, status rumah akan berubah menjadi Selesai.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('bantuan.selesai', $rumah) }}" class="space-y-4">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-700">Catatan Verifikasi Akhir</label>
                                <span class="text-[11px] text-slate-400">(Opsional)</span>
                            </div>
                            <textarea name="catatan" rows="3" maxlength="500"
                                      placeholder="Tambahkan catatan hasil verifikasi jika diperlukan"
                                      class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-2xs"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button type="button" @click="openModalSelesai = false"
                                    class="py-2.5 px-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="py-2.5 px-4 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition ra-btn-press">
                                Ya, Tetapkan Selesai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    {{-- ======================================================================= --}}
    {{-- MODAL POP-UP: LIGHTBOX PREVIEW FOTO DOKUMENTASI                         --}}
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
