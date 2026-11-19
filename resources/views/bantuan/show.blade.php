@extends('layouts.app')

@section('title', 'Monitoring Renovasi — ' . $rumah->nama_kepala_keluarga)

@section('content')
@php
    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
    $bisaSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI && $persen >= 100;
    $isSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_SELESAI;

    $badgeStyles = [
        'Penerima Bantuan' => 'bg-[#FBF4E6] text-[#A26D27] border border-[#F2DEBF]',
        'Dalam Renovasi'   => 'bg-[#F4EFFC] text-[#6941C6] border border-[#E0D4F7]',
        'Selesai'          => 'bg-[#EDF7EE] text-[#2D6A4F] border border-[#CDE5D8]',
    ];

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

<div class="space-y-6">
    {{-- Navigasi & Aksi Header (Mockup Page 2 & 15) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('bantuan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-forest hover:underline mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Monitoring Renovasi
            </a>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">REVIEW PENYELESAIAN RENOVASI</p>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $rumah->kode_registrasi }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->wilayah?->nama ?? '-' }}</p>
        </div>

        <div class="flex items-center gap-2">
            @if ($bantuan)
                <a href="{{ route('bantuan.progres.index', $rumah) }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                    <i data-lucide="hammer" class="w-4 h-4 text-forest"></i>
                    Kelola Progres
                </a>
            @endif

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

    {{-- Kartu Ringkasan Penerima --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
            {{-- Data Warga --}}
            <div class="flex items-center gap-4 md:col-span-2">
                <div class="w-14 h-14 rounded-full bg-forest text-white flex items-center justify-center font-bold text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block">Penerima Bantuan</span>
                    <h2 class="text-base font-bold text-slate-900 leading-tight">{{ $rumah->nama_kepala_keluarga }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $rumah->alamat }}, {{ $rumah->wilayah?->nama ?? '-' }}</p>
                </div>
            </div>

            {{-- Status Saat Ini --}}
            <div class="md:text-center md:border-x border-slate-100 md:px-4">
                <span class="text-[11px] text-slate-400 block mb-1">Status Saat Ini</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeStyles[$rumah->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $rumah->status }}
                </span>
            </div>

            {{-- Progres Renovasi --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-500">Progres Renovasi</span>
                    <span class="text-base font-bold text-slate-900">{{ $persen }}%</span>
                </div>
                <div class="w-full bg-slate-200/90 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-forest h-2.5 rounded-full transition-all duration-500" style="width: {{ $persen }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Laporan: {{ $bantuan?->progres?->last() ? \Carbon\Carbon::parse($bantuan->progres->last()->tanggal_progres)->format('d M Y') : '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Banner Status Workflow --}}
    @if ($bisaSelesai)
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
        {{-- Kolom Kiri: Riwayat Progres & Foto Sebelum-Sesudah --}}
        <div class="lg:col-span-2 space-y-6">
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
                    <p class="text-xs text-slate-500 text-center py-8">Belum ada laporan progres yang dicatat.</p>
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

            {{-- Dokumentasi Sebelum & Sesudah (Mockup Page 2 & 15) --}}
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

            {{-- Informasi Bantuan (Mockup Page 2 & 15) --}}
            @if ($bantuan)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Informasi Bantuan</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Data administratif berdasarkan dokumen resmi</p>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">Tercatat</span>
                    </div>

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
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Aturan Penyelesaian & Riwayat Status --}}
        <div class="space-y-6">
            {{-- Aturan Penyelesaian (Mockup Page 2 & 15) --}}
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
                                <span class="font-medium text-slate-700">{{ $r->status_ke }}</span>
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
