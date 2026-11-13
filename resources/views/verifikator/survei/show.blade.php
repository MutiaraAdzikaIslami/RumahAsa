@extends('layouts.app')

@section('title', 'Review Survei — ' . $rumah->kode_registrasi)

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12" x-data="{
    lightboxOpen: false,
    lightboxImage: '',
    lightboxTitle: '',
    lightboxMeta: '',
    modalPerbaikanOpen: false,
    modalKategoriOpen: false,
    openLightbox(url, title, meta) {
        this.lightboxImage = url;
        this.lightboxTitle = title;
        this.lightboxMeta = meta;
        this.lightboxOpen = true;
    }
}" @keydown.escape.window="lightboxOpen = false; modalPerbaikanOpen = false; modalKategoriOpen = false">

    {{-- Breadcrumb & Navigasi Kembali --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('verifikator.survei.index') }}" class="hover:text-emerald-700 flex items-center gap-1">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                Antrean Survei
            </a>
            <span>/</span>
            <span class="font-mono text-slate-700 font-semibold">{{ $rumah->kode_registrasi }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('verifikator.survei.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Kembali ke Antrean
            </a>
            <a href="{{ route('rumah.show', $rumah) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                Lihat Detail Lengkap Rumah
            </a>
        </div>
    </div>

    {{-- Banner Status & Informasi Pengajuan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs font-bold text-slate-500 uppercase tracking-wider bg-slate-100 px-2.5 py-1 rounded-md">
                        {{ $rumah->kode_registrasi }}
                    </span>
                    @php
                        $badgeColors = [
                            'Draf' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'Diajukan' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Perlu Perbaikan' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Layak Huni' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Penerima Bantuan' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'Dalam Renovasi' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'Selesai' => 'bg-teal-50 text-teal-700 border-teal-200',
                        ];
                        $badgeClass = $badgeColors[$rumah->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        Status: {{ $rumah->status }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 pt-1">
                    Review Survei Kelayakan: {{ $rumah->nama_kepala_keluarga ?? 'Rumah Warga' }}
                </h1>
                <p class="text-xs text-slate-500 flex items-center gap-4 pt-1">
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                        Surveyor: <strong>{{ $rumah->surveyor?->nama ?? '-' }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                        Diajukan: <strong>{{ ($survei?->dikirim_at ?? $rumah->updated_at)?->translatedFormat('d F Y, H:i') }} WIB</strong>
                    </span>
                </p>
            </div>

            @if ($rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN)
                <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0">
                    <button type="button" @click="modalPerbaikanOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Minta Perbaikan
                    </button>
                    <button type="button" @click="modalKategoriOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Tetapkan Kategori
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Alert Panduan & Ringkasan Kelayakan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Card 1: Kelengkapan 7 Komponen --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">7 Komponen Penilaian</p>
                <p class="text-sm font-bold text-slate-900">
                    {{ $survei?->komponenSurvei?->whereNotNull('opsi_kondisi_id')->count() ?? 0 }} / 7 Terisi
                </p>
            </div>
        </div>

        {{-- Card 2: Total Galeri Foto --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="images" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Foto Bukti</p>
                <p class="text-sm font-bold text-slate-900">{{ $totalFoto }} Foto Terunggah</p>
            </div>
        </div>

        {{-- Card 3: Indikasi Kondisi RTLH --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $rtlhCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-600' }} flex items-center justify-center shrink-0">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Indikasi Kerusakan Berat</p>
                <p class="text-sm font-bold {{ $rtlhCount > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $rtlhCount }} Komponen RTLH
                </p>
            </div>
        </div>
    </div>

    {{-- Grid Informasi Warga & Lokasi --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Data Warga & Kepemilikan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="p-1.5 bg-forest/10 text-forest rounded-lg">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <h2 class="text-sm font-bold text-slate-900">Data Kepala Keluarga & Warga</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">Nama Kepala Keluarga</span>
                    <span class="font-semibold text-slate-900 text-sm">{{ $rumah->nama_kepala_keluarga ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">NIK (KTP)</span>
                    <span class="font-mono font-semibold text-slate-800">
                        @if ($rumah->nik_enc)
                            @can('lihatNikPenuh', $rumah)
                                {{ app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNik($rumah->nik_enc) }}
                            @else
                                {{ $rumah->nikTersamar() }}
                            @endcan
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">No. Kartu Keluarga (KK)</span>
                    <span class="font-mono text-slate-700">
                        @if ($rumah->no_kk_enc)
                            @can('lihatNikPenuh', $rumah)
                                {{ app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNoKk($rumah->no_kk_enc) }}
                            @else
                                {{ $rumah->noKkTersamar() }}
                            @endcan
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Jumlah Anggota Keluarga</span>
                    <span class="text-slate-800 font-medium">{{ $rumah->jumlah_anggota ? $rumah->jumlah_anggota . ' Jiwa' : '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Pekerjaan</span>
                    <span class="text-slate-800">{{ $rumah->pekerjaan ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Penghasilan per Bulan</span>
                    <span class="font-semibold text-slate-900">
                        {{ $rumah->penghasilan ? 'Rp ' . number_format($rumah->penghasilan, 0, ',', '.') : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Status Kepemilikan</span>
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 uppercase">
                        {{ str_replace('_', ' ', $rumah->status_kepemilikan ?? '-') }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Luas Bangunan Rumah</span>
                    <span class="text-slate-800 font-medium">{{ $rumah->luas_rumah ? $rumah->luas_rumah . ' m²' : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Data Lokasi & Rumah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="p-1.5 bg-forest/10 text-forest rounded-lg">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <h2 class="text-sm font-bold text-slate-900">Lokasi & Alamat Survei</h2>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">Wilayah Administratif</span>
                    <span class="font-semibold text-slate-800 text-sm">
                        {{ $rumah->wilayah?->nama ?? '-' }}
                    </span>
                    <span class="text-slate-400 ml-1 text-xs">({{ strtoupper($rumah->wilayah?->tingkat ?? '') }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Alamat Lapangan</span>
                    <p class="text-slate-800 leading-relaxed">{{ $rumah->alamat ?? '-' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-1">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Koordinat Latitude</span>
                        <span class="font-mono text-slate-700">{{ $rumah->latitude ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Koordinat Longitude</span>
                        <span class="font-mono text-slate-700">{{ $rumah->longitude ?? '-' }}</span>
                    </div>
                </div>
                @if ($rumah->latitude && $rumah->longitude)
                    <div class="pt-2">
                        <a href="https://www.google.com/maps?q={{ $rumah->latitude }},{{ $rumah->longitude }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-semibold">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            Buka di Google Maps
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail 7 Komponen Penilaian & Galeri Foto Bukti (CORE OF SCRUM-111) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-emerald-600"></i>
                    Detail Penilaian 7 Komponen dan Galeri Foto Bukti
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tinjau kondisi fisik masing-masing komponen beserta catatan lapangan dan galeri foto bukti resolusi penuh.
                </p>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                Klik pada foto mana pun untuk memperbesar tampilan
            </div>
        </div>

        @if (!$survei || $survei->komponenSurvei->isEmpty())
            <div class="p-12 text-center text-slate-500 text-sm">
                Belum ada data komponen survei yang terekam untuk rumah ini.
            </div>
        @else
            <div class="divide-y divide-slate-200">
                @foreach ($survei->komponenSurvei->sortBy('komponen.urutan') as $itemKomponen)
                    @php
                        $komponen = $itemKomponen->komponen;
                        $opsi = $itemKomponen->opsiKondisi;
                        $fotoList = $itemKomponen->foto;
                    @endphp
                    <div class="p-6 space-y-4 hover:bg-slate-50/30 transition">
                        {{-- Header Komponen --}}
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                        {{ $loop->iteration }}
                                    </span>
                                    <h3 class="text-base font-bold text-slate-900">{{ $komponen->nama }}</h3>
                                    @if ($opsi && $opsi->otomatis_rtlh)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            Kondisi RTLH
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-1 pl-8">{{ $komponen->deskripsi }}</p>
                            </div>

                            {{-- Badge Opsi Terpilih --}}
                            <div class="pl-8 sm:pl-0 shrink-0">
                                @if ($opsi)
                                    <div class="text-right sm:text-right">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold {{ $opsi->otomatis_rtlh ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                            <i data-lucide="{{ $opsi->otomatis_rtlh ? 'alert-triangle' : 'check' }}" class="w-3.5 h-3.5"></i>
                                            {{ $opsi->label }}
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs text-amber-700 bg-amber-50 border border-amber-200">
                                        Belum dinilai
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Panduan Opsi & Catatan Lapangan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-8 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 font-semibold block mb-1">Deskripsi Standar Kondisi:</span>
                                <p class="text-slate-700 leading-relaxed italic">
                                    {{ $opsi?->deskripsi ?? 'Tidak ada deskripsi kondisi baku.' }}
                                </p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 font-semibold block mb-1">Catatan Khusus Surveyor:</span>
                                <p class="text-slate-800 leading-relaxed">
                                    {{ $itemKomponen->catatan ?: 'Tidak ada catatan tambahan dari surveyor.' }}
                                </p>
                            </div>
                        </div>

                        {{-- Galeri Foto Bukti Komponen --}}
                        <div class="pl-8 pt-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
                                    <i data-lucide="camera" class="w-3.5 h-3.5 text-slate-400"></i>
                                    Foto Bukti Lapangan ({{ $fotoList->count() }} Foto):
                                </span>
                            </div>

                            @if ($fotoList->isEmpty())
                                <div class="p-4 rounded-xl border border-dashed border-amber-300 bg-amber-50/50 text-amber-700 text-xs flex items-center gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                                    <span>Belum ada foto bukti yang diunggah untuk komponen ini.</span>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    @foreach ($fotoList as $foto)
                                        @php
                                            $fotoUrl = route('survei.foto.show', $foto);
                                            $fotoMeta = ($foto->nama_asli ?? 'Foto Bukti') . ' • ' . round(($foto->ukuran_byte ?? 0) / 1024) . ' KB';
                                        @endphp
                                        <div class="group relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video sm:aspect-square shadow-sm hover:shadow-md transition cursor-pointer"
                                             @click="openLightbox('{{ $fotoUrl }}', '{{ $komponen->nama }} — {{ $opsi?->label ?? 'Kondisi' }}', '{{ $fotoMeta }}')">
                                            <img src="{{ $fotoUrl }}" alt="{{ $komponen->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-2">
                                                <div class="text-[10px] text-white font-medium flex items-center gap-1 truncate">
                                                    <i data-lucide="zoom-in" class="w-3 h-3 shrink-0"></i>
                                                    <span class="truncate">Perbesar Foto</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Riwayat Status Rumah (FR-24) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-emerald-600"></i>
                Riwayat Perubahan Status & Catatan Verifikasi
            </h2>
            <span class="text-xs text-slate-500 font-mono">
                {{ $rumah->riwayatStatus->count() }} Catatan
            </span>
        </div>

        @if ($rumah->riwayatStatus->isEmpty())
            <p class="text-xs text-slate-500 italic">Belum ada riwayat perubahan status.</p>
        @else
            <div class="relative pl-6 border-l-2 border-slate-200 space-y-4 text-xs">
                @foreach ($rumah->riwayatStatus as $riwayat)
                    <div class="relative group">
                        <div class="absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full bg-white border-2 border-emerald-500"></div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-1.5 font-semibold">
                                    <span class="text-slate-600">{{ $riwayat->status_dari }}</span>
                                    <span class="text-slate-400">→</span>
                                    <span class="text-emerald-700">{{ $riwayat->status_ke }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">
                                    {{ $riwayat->created_at ? $riwayat->created_at->format('d/m/Y H:i') : '-' }}
                                </span>
                            </div>
                            <div class="text-slate-700 pt-1">
                                <span class="text-slate-400">Catatan:</span>
                                <span class="italic text-slate-800">{{ $riwayat->catatan ?: '-' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                Pelaku: {{ $riwayat->user?->nama ?? 'Sistem' }} ({{ $riwayat->user?->role?->nama ?? '-' }})
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Panel Aksi Keputusan Verifikator --}}
    @if ($rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN)
        <div class="bg-gradient-to-r from-forest to-forest-light text-white rounded-2xl p-6 shadow-md">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Tetapkan Keputusan Hasil Review</h3>
                    <p class="text-xs text-green-100 mt-1 max-w-xl">
                        Setelah meneliti seluruh kondisi 7 komponen dan foto bukti, Anda dapat meminta perbaikan ke surveyor atau langsung menetapkan kelayakan rumah ini.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="modalPerbaikanOpen = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Minta Perbaikan
                    </button>
                    <button type="button" @click="modalKategoriOpen = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow transition">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        Tetapkan Kategori
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL LIGHTBOX FOTO RESOLUSI PENUH --}}
    <div x-show="lightboxOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/85 backdrop-blur-sm" @click.self="lightboxOpen = false">
        <div class="relative bg-white rounded-2xl max-w-4xl w-full overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h4 class="text-sm font-bold text-slate-900" x-text="lightboxTitle"></h4>
                    <p class="text-xs text-slate-500" x-text="lightboxMeta"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="lightboxImage" target="_blank" class="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition" title="Buka gambar di tab baru">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                    <button type="button" @click="lightboxOpen = false" class="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition" title="Tutup">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div class="p-4 overflow-auto flex-1 flex items-center justify-center bg-slate-950/5">
                <img :src="lightboxImage" alt="Preview Foto Bukti" class="max-h-[70vh] w-auto object-contain rounded-lg shadow-sm">
            </div>
        </div>
    </div>

    {{-- MODAL MINTA PERBAIKAN --}}
    <div x-show="modalPerbaikanOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" @click.self="modalPerbaikanOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-amber-100 text-amber-700 rounded-xl">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Minta Perbaikan Survei</h4>
                        <p class="text-xs text-slate-500">Kembalikan survei ke Surveyor untuk dilengkapi.</p>
                    </div>
                </div>
                <button type="button" @click="modalPerbaikanOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('verifikator.survei.minta-perbaikan', $rumah) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Catatan Perbaikan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan" rows="4" required minlength="5" maxlength="1000" placeholder="Tuliskan komponen atau foto bukti yang perlu diperbaiki atau diulang oleh surveyor..." class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan tampil pada Surveyor saat memperbaiki survei.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalPerbaikanOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition shadow">
                        Kirim Permintaan Perbaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TETAPKAN KATEGORI --}}
    <div x-show="modalKategoriOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" @click.self="modalKategoriOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" x-data="{ pilihanKategori: 'tidak_layak_huni' }">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-emerald-100 text-emerald-700 rounded-xl">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Tetapkan Kategori Kelayakan</h4>
                        <p class="text-xs text-slate-500">Penetapan manual hasil review oleh Verifikator.</p>
                    </div>
                </div>
                <button type="button" @click="modalKategoriOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('verifikator.survei.tetapkan-kategori', $rumah) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">
                        Pilih Kategori <span class="text-rose-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition hover:bg-slate-50" :class="pilihanKategori === 'tidak_layak_huni' ? 'border-rose-500 bg-rose-50/40 ring-1 ring-rose-500' : 'border-slate-200'">
                            <input type="radio" name="kategori" value="tidak_layak_huni" x-model="pilihanKategori" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div>
                                <span class="font-bold text-xs text-slate-900">Tidak Layak Huni (Penerima Bantuan)</span>
                                <p class="text-[11px] text-slate-500 mt-0.5">Rumah memenuhi syarat untuk dimasukkan ke Daftar Penerima Bantuan program renovasi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition hover:bg-slate-50" :class="pilihanKategori === 'layak_huni' ? 'border-emerald-500 bg-emerald-50/40 ring-1 ring-emerald-500' : 'border-slate-200'">
                            <input type="radio" name="kategori" value="layak_huni" x-model="pilihanKategori" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="font-bold text-xs text-slate-900">Layak Huni</span>
                                <p class="text-[11px] text-slate-500 mt-0.5">Kondisi rumah dinilai masih layak huni dan tidak membutuhkan intervensi program bantuan saat ini.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Catatan & Alasan Penetapan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan" rows="3" required minlength="5" maxlength="1000" placeholder="Jelaskan dasar pertimbangan atau alasan keputusan kelayakan..." class="w-full text-xs p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Alasan penetapan akan dicatat secara permanen dalam riwayat status.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalKategoriOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow">
                        Simpan Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
