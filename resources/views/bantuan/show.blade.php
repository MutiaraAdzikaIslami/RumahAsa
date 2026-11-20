@extends('layouts.app')

@php
    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
    $bisaSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI && $persen >= 100;
    $isSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_SELESAI;
    $isBelumDicatat = $rumah->status === \App\Models\RumahWarga::STATUS_PENERIMA_BANTUAN && !$bantuan;

    $tglPenetapan = $rumah->riwayatStatus->where('status_ke', \App\Models\RumahWarga::STATUS_PENERIMA_BANTUAN)->sortByDesc('id')->first()?->created_at
        ?? ($bantuan?->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan) : $rumah->created_at);

    // Foto Sebelum (dari survei awal) & Sesudah (dari progres 100%)
    $fotoSebelum = null;
    if ($rumah->survei->isNotEmpty()) {
        foreach ($rumah->survei->first()->komponen as $k) {
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

@section('title', ($isBelumDicatat ? 'Detail Penerima Bantuan — ' : 'Monitoring Renovasi — ') . $rumah->nama_kepala_keluarga)

@section('content')
<div class="space-y-6">
    {{-- Navigasi & Aksi Header (Mockup Page 11 & 15) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            @if ($isBelumDicatat)
                <a href="{{ route('bantuan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-forest hover:underline mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Daftar Penerima Bantuan
                </a>
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">DETAIL PENERIMA BANTUAN</p>
            @else
                <a href="{{ route('bantuan.index', ['mode' => 'progres']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-forest hover:underline mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Monitoring Renovasi
                </a>
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">REVIEW PENYELESAIAN RENOVASI</p>
            @endif
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $rumah->kode_registrasi }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->wilayahSingkat() }}</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- RBAC: HANYA Surveyor yang bisa menambah/mengedit laporan progres renovasi --}}
            @can('surveyor')
                @if ($bantuan && !$isSelesai)
                    <a href="{{ route('bantuan.progres.index', $rumah) }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                        <i data-lucide="hammer" class="w-4 h-4 text-forest"></i>
                        Kelola Progres
                    </a>
                @endif
            @endcan

            {{-- RBAC: Verifikator hanya mencatat bantuan awal atau menetapkan renovasi selesai --}}
            @can('verifikator')
                @if ($bisaSelesai)
                    <button type="button" onclick="document.getElementById('modal-selesai').classList.remove('hidden')"
                            class="px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Tetapkan Selesai
                    </button>
                @endif
            @endcan
        </div>
    </div>

    {{-- Kartu Ringkasan Penerima (Mockup Page 11 & 15) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            {{-- Data Warga --}}
            <div class="flex items-center gap-4 md:col-span-5">
                <div class="w-14 h-14 rounded-full bg-forest text-white flex items-center justify-center font-bold text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 block">Penerima Bantuan</span>
                    <h2 class="text-base font-bold text-slate-900 leading-tight truncate">{{ $rumah->nama_kepala_keluarga }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->alamatLengkap() }}</p>
                </div>
            </div>

            {{-- Status Saat Ini (Golden Rule Legenda Warna) --}}
            <div class="md:col-span-3 md:text-center md:border-x border-slate-100 md:px-4">
                <span class="text-[11px] text-slate-400 block mb-1">Status Saat Ini</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $rumah->badgeStatusClass($rumah->status) }}">
                    {{ $rumah->status }}
                </span>
            </div>

            {{-- Progres Renovasi & Tombol Catat Bantuan (Mockup Page 11) --}}
            <div class="md:col-span-4 flex items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-slate-500">Progres Renovasi</span>
                        <span class="text-base font-bold text-slate-900">{{ $persen }}%</span>
                    </div>
                    <div class="w-full bg-slate-200/90 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-forest h-2.5 rounded-full transition-all duration-500" style="width: {{ $persen }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Laporan: {{ $bantuan?->progres?->last() ? \Carbon\Carbon::parse($bantuan->progres->last()->tanggal_progres)->format('d M Y') : 'Belum dimulai' }}
                    </p>
                </div>

                @can('verifikator')
                    @if ($isBelumDicatat)
                        <button type="button"
                                onclick="document.getElementById('modal-catat-bantuan').classList.remove('hidden')"
                                class="px-4 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5 flex-shrink-0">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Catat Bantuan
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- Banner Status Workflow (Mockup Page 11 & 15) --}}
    @if ($isBelumDicatat)
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-xs text-emerald-950 flex items-start gap-3 shadow-xs">
            <div class="w-7 h-7 rounded-full bg-forest text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="info" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="font-bold text-emerald-950">Rumah telah ditetapkan sebagai penerima bantuan &mdash; <span class="font-normal text-emerald-800">Status baru akan berubah menjadi Dalam Renovasi setelah Verifikator mencatat bantuan berdasarkan dokumen resmi.</span></h3>
            </div>
        </div>
    @elseif ($bisaSelesai)
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-3 shadow-sm">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-700 flex-shrink-0 mt-0.5"></i>
            <div>
                <h3 class="font-bold text-emerald-950">Renovasi siap ditinjau</h3>
                <p class="mt-0.5 text-emerald-800">Progres renovasi telah mencapai 100%. Tinjau dokumentasi dan laporan sebelum menetapkan status Selesai.</p>
            </div>
        </div>
    @elseif ($isSelesai)
        <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 text-xs text-teal-900 flex items-start gap-3 shadow-sm">
            <i data-lucide="shield-check" class="w-5 h-5 text-teal-700 flex-shrink-0 mt-0.5"></i>
            <div>
                <h3 class="font-bold text-teal-950">Renovasi telah ditetapkan selesai</h3>
                <p class="mt-0.5 text-teal-800">Keputusan akhir telah dicatat. Riwayat renovasi tersimpan permanen dan terkunci dari perubahan.</p>
            </div>
        </div>
    @endif

    {{-- Layout Grid: Konten Utama & Sidebar --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri (2 Kolom): Informasi Bantuan / Rumah / Riwayat Progres / Foto Sebelum-Sesudah --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Bantuan (Mockup Page 11 & 15) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Informasi Bantuan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Data administratif berdasarkan dokumen resmi</p>
                    </div>
                    @if ($bantuan)
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">Tercatat</span>
                    @else
                        <span class="text-[11px] font-semibold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/60">Belum Tercatat</span>
                    @endif
                </div>

                @if ($bantuan)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block">Tanggal Bantuan</span>
                            <span class="font-medium text-slate-800">
                                {{ $bantuan->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan)->format('d M Y') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Jenis Bantuan</span>
                            <span class="font-medium text-slate-800">{{ $bantuan->jenis_bantuan }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Nominal Bantuan</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format((float) $bantuan->nominal, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Sumber Program</span>
                            <span class="font-semibold text-forest">{{ $bantuan->sumber_program ?? '-' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-slate-400 block">Nomor SK</span>
                            <span class="font-mono font-medium text-slate-800">{{ $bantuan->nomor_sk }}</span>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 p-8 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100/80 text-forest flex items-center justify-center mx-auto">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                        <div class="max-w-md mx-auto">
                            <h3 class="text-sm font-bold text-slate-900">Detail bantuan renovasi rumah ini belum dicatat</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Klik tombol <strong>Catat Bantuan</strong> untuk mencatat tanggal bantuan, jenis bantuan, nominal bantuan, dan nomor SK. Setelah disimpan, status rumah akan berubah menjadi <strong>Dalam Renovasi</strong>.
                            </p>
                        </div>
                        @can('verifikator')
                            <div class="pt-1">
                                <button type="button"
                                        onclick="document.getElementById('modal-catat-bantuan').classList.remove('hidden')"
                                        class="px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Catat Bantuan Sekarang
                                </button>
                            </div>
                        @endcan
                    </div>
                @endif
            </div>

            {{-- Informasi Rumah (Mockup Page 11) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Informasi Rumah</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                        <span class="text-slate-400 block">NIK Pemilik</span>
                        <span class="font-mono font-bold text-slate-800 mt-0.5 block">{{ $rumah->nik_enc ? $rumah->nikTersamar() : '-' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                        <span class="text-slate-400 block">Jumlah Keluarga</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $rumah->jumlah_anggota_kk ? $rumah->jumlah_anggota_kk . ' jiwa' : '-' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                        <span class="text-slate-400 block">Luas Bangunan</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $rumah->luas_rumah ? $rumah->luas_rumah . ' m²' : '-' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100">
                        <span class="text-slate-400 block">Surveyor</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $rumah->surveyor?->nama ?? 'Surveyor Lapangan' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100 sm:col-span-2">
                        <span class="text-slate-400 block">Tanggal Penetapan</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $tglPenetapan ? $tglPenetapan->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Riwayat Progres Renovasi --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Riwayat Progres Renovasi</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Laporan lapangan dari Surveyor penugasan</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $bantuan?->progres?->count() ?? 0 }} laporan
                    </span>
                </div>

                @if (!$bantuan || $bantuan->progres->isEmpty())
                    <p class="text-xs text-slate-500 text-center py-8">Belum ada laporan progres yang dicatat oleh Surveyor.</p>
                @else
                    <div class="space-y-6">
                        @foreach ($bantuan->progres->sortByDesc('tanggal_progres') as $p)
                            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $p->tahap_pekerjaan }}</h3>
                                        <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($p->tanggal_progres)->format('d M Y') }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-forest">{{ $p->persentase }}%</span>
                                </div>

                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-forest h-1.5 rounded-full" style="width: {{ $p->persentase }}%"></div>
                                </div>

                                @if ($p->catatan)
                                    <p class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200/60">{{ $p->catatan }}</p>
                                @endif

                                {{-- Galeri Foto Progres (SCRUM-128 & SCRUM-133) --}}
                                @if ($p->fotos->isNotEmpty())
                                    <div>
                                        <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">{{ $p->fotos->count() }} foto dokumentasi</span>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach ($p->fotos as $f)
                                                <a href="{{ route('bantuan.progres.foto', $f) }}" target="_blank"
                                                   class="aspect-video rounded-lg overflow-hidden border border-slate-200 bg-slate-100 hover:opacity-90 transition block shadow-sm">
                                                    <img src="{{ route('bantuan.progres.foto', $f) }}" alt="Dokumentasi progres" class="w-full h-full object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Dokumentasi Sebelum & Sesudah (Mockup Page 15) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Dokumentasi Sebelum &amp; Sesudah</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Perbandingan kondisi rumah penerima bantuan</p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">Ditinjau wajib</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Sebelum --}}
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                        <div class="aspect-video bg-slate-200 relative flex items-center justify-center">
                            @if ($fotoSebelum)
                                <img src="{{ route('survei.foto.show', $fotoSebelum) }}" alt="Foto sebelum renovasi" class="w-full h-full object-cover">
                            @else
                                <div class="text-slate-400 text-xs flex flex-col items-center gap-1">
                                    <i data-lucide="image" class="w-6 h-6"></i>
                                    <span>Foto awal survei</span>
                                </div>
                            @endif
                            <span class="absolute top-2 left-2 px-2.5 py-0.5 bg-forest text-white rounded-full text-[10px] font-bold">
                                Sebelum
                            </span>
                        </div>
                        <div class="p-3 text-xs">
                            <span class="font-semibold text-slate-800 block">Kondisi Awal</span>
                            <span class="text-slate-400 text-[11px]">Berdasarkan hasil survei lapangan awal</span>
                        </div>
                    </div>

                    {{-- Sesudah --}}
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                        <div class="aspect-video bg-slate-200 relative flex items-center justify-center">
                            @if ($fotoSesudah)
                                <img src="{{ route('bantuan.progres.foto', $fotoSesudah) }}" alt="Foto sesudah renovasi" class="w-full h-full object-cover">
                            @else
                                <div class="text-slate-400 text-xs flex flex-col items-center gap-1">
                                    <i data-lucide="image" class="w-6 h-6"></i>
                                    <span>Foto akhir progres</span>
                                </div>
                            @endif
                            <span class="absolute top-2 left-2 px-2.5 py-0.5 bg-emerald-700 text-white rounded-full text-[10px] font-bold">
                                Sesudah
                            </span>
                        </div>
                        <div class="p-3 text-xs">
                            <span class="font-semibold text-slate-800 block">Kondisi Akhir</span>
                            <span class="text-slate-400 text-[11px]">Dokumentasi progres renovasi lapangan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Aturan Penyelesaian, Hak Akses & Riwayat Status --}}
        <div class="space-y-6">
            {{-- Aturan Penyelesaian (Mockup Page 15) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-forest/10 text-forest flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Aturan Penyelesaian</h3>
                </div>
                <ul class="space-y-2 text-xs text-slate-600">
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 {{ $persen >= 100 ? 'text-forest' : 'text-slate-300' }} mt-0.5"></i>
                        <span>Progres telah mencapai 100%</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-forest mt-0.5"></i>
                        <span>Status tidak berubah otomatis</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-forest mt-0.5"></i>
                        <span>Dokumentasi perlu ditinjau</span>
                    </li>
                </ul>
            </div>

            {{-- Hak Akses & Alur Bantuan (Mockup Page 11) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Hak Akses</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Hanya <strong>Verifikator</strong> dapat menetapkan renovasi selesai. <strong>Surveyor</strong> melaporkan progres dan foto, sedangkan <strong>Admin</strong> hanya dapat melihat catatan.
                </p>
            </div>

            {{-- Riwayat Status Rumah --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-forest/10 text-forest flex items-center justify-center">
                        <i data-lucide="history" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Riwayat Status</h3>
                </div>
                <ul class="space-y-3 text-xs">
                    @foreach ($rumah->riwayatStatus->sortByDesc('id') as $r)
                        <li class="pb-2 border-b border-slate-100 last:border-0 last:pb-0">
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ $r->created_at?->format('d M Y, H:i') }}</span>
                                <span class="font-semibold text-slate-700">{{ $r->status_ke }}</span>
                            </div>
                            <p class="text-slate-600 mt-0.5">{{ $r->status_dari }} &rarr; {{ $r->status_ke }}</p>
                            @if ($r->catatan)
                                <p class="text-[11px] text-slate-400 mt-0.5 italic">"{{ $r->catatan }}"</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Modal Catat Bantuan (Mockup Page 10) --}}
@can('verifikator')
    @if ($isBelumDicatat)
        <div id="modal-catat-bantuan" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-200">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Catat Bantuan</h3>
                            <p class="text-xs text-slate-500">Pencatatan bantuan resmi untuk memulai tahap renovasi.</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-catat-bantuan').classList.add('hidden')"
                            class="w-8 h-8 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-500">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $rumah->nama_kepala_keluarga }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ $rumah->kode_registrasi }} &middot; {{ $rumah->alamatLengkap() }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-300 flex-shrink-0">
                        Menjadi Dalam Renovasi
                    </span>
                </div>

                <form method="POST" action="{{ route('bantuan.salurkan.store', $rumah) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Bantuan <span class="text-rose-600">*</span></label>
                            <input type="date" name="tanggal_bantuan" value="{{ old('tanggal_bantuan', now()->format('Y-m-d')) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sumber Program <span class="text-rose-600">*</span></label>
                            <select name="sumber_program" required
                                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                <option value="APBN">APBN (BSPS Pusat)</option>
                                <option value="APBD">APBD (Rutilahu Daerah)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Bantuan <span class="text-rose-600">*</span></label>
                            <select name="jenis_bantuan" required
                                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                                <option value="BSPS Renovasi Atap & Dinding">BSPS Renovasi Atap &amp; Dinding</option>
                                <option value="Bantuan Material Bangunan">Bantuan Material Bangunan</option>
                                <option value="Renovasi Total RTLH">Renovasi Total RTLH</option>
                                <option value="Perbaikan Sanitasi & Lantai">Perbaikan Sanitasi &amp; Lantai</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Bantuan (Rp) <span class="text-rose-600">*</span></label>
                            <input type="number" name="nominal" min="0" step="1000" value="{{ old('nominal', 20000000) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SK <span class="text-rose-600">*</span></label>
                        <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" required
                               placeholder="Contoh: SK/BSPS/KOTABARU/2026/014"
                               class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800">
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/80 text-[11px] text-emerald-900 flex items-start gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-forest flex-shrink-0 mt-0.5"></i>
                        <span>Setelah disimpan, data bantuan akan tercatat di riwayat rumah dan status otomatis berubah dari <strong>Penerima Bantuan</strong> menjadi <strong>Dalam Renovasi</strong>.</span>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('modal-catat-bantuan').classList.add('hidden')"
                                class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Simpan &amp; Mulai Renovasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan

{{-- Modal Tetapkan Selesai (Mockup Page 14) --}}
@can('verifikator')
    @if ($bisaSelesai)
        <div id="modal-selesai" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-200">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-slate-900">Tetapkan Status Renovasi Selesai?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Pastikan hasil renovasi dan dokumentasi telah ditinjau. Setelah dikonfirmasi, status rumah akan berubah menjadi Selesai dan terkunci dari perubahan.
                    </p>
                </div>

                <form method="POST" action="{{ route('bantuan.selesai', $rumah) }}" class="space-y-4 pt-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Verifikasi Akhir (Opsional)</label>
                        <textarea name="catatan" rows="3" maxlength="500"
                                  placeholder="Tambahkan catatan hasil verifikasi jika diperlukan..."
                                  class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('modal-selesai').classList.add('hidden')"
                                class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition">
                            Ya, Tetapkan Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan
@endsection
