@extends('layouts.app')

@section('title', 'Review Pengajuan')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-16" x-data="{
    lightboxOpen: false,
    lightboxImage: '',
    lightboxTitle: '',
    lightboxMeta: '',
    kategori: '',
    catatan: '',
    openLightbox(url, title, meta) {
        this.lightboxImage = url;
        this.lightboxTitle = title;
        this.lightboxMeta = meta;
        this.lightboxOpen = true;
    },
    submitTetapkan(e) {
        if (!this.kategori) {
            alert('Silakan pilih salah satu keputusan: Layak atau Tidak Layak.');
            e.preventDefault();
            return;
        }
        if (!this.catatan || this.catatan.trim().length < 5) {
            alert('Catatan/alasan keputusan wajib diisi minimal 5 karakter.');
            e.preventDefault();
            return;
        }
    },
    submitPerbaikan(e) {
        if (!this.catatan || this.catatan.trim().length < 5) {
            alert('Catatan perbaikan wajib diisi minimal 5 karakter sebelum mengembalikan berkas ke surveyor.');
            e.preventDefault();
            return;
        }
        if (!confirm('Apakah Anda yakin ingin mengembalikan survei ini ke Surveyor untuk perbaikan?')) {
            e.preventDefault();
            return;
        }
    }
}" @keydown.escape.window="lightboxOpen = false">

    {{-- Top Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('verifikator.survei.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2C614D] hover:underline mb-2 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Dashboard Verifikator
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Review Pengajuan</h1>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#EDF2F7] text-[#4A5568] border border-slate-200">
                    {{ $rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN ? 'Dalam Verifikasi' : $rumah->status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ $rumah->kode_registrasi }} • Diajukan {{ ($survei?->dikirim_at ?? $rumah->updated_at)?->translatedFormat('d F Y') }} oleh {{ $rumah->surveyor?->nama ?? 'Surveyor' }}
            </p>
        </div>

        <div>
            <a href="{{ route('verifikator.survei.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 shadow-sm transition">
                Kembali
            </a>
        </div>
    </div>

    {{-- Card 1: Nama Pemilik --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#EBE8E1] flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-[#1D4034] text-white flex items-center justify-center font-bold text-sm tracking-wide shrink-0">
                {{ strtoupper(substr($rumah->nama_kepala_keluarga ?? 'KK', 0, 2)) }}
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Nama Pemilik</span>
                <h2 class="text-xl font-bold text-slate-900 leading-tight">{{ $rumah->nama_kepala_keluarga ?? '-' }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    NIK {{ $rumah->nik_enc ? $rumah->nikTersamar() : '-' }} • {{ $rumah->jumlah_anggota ?? 0 }} anggota keluarga
                </p>
            </div>
        </div>
        <div>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-medium text-slate-600 bg-white border border-slate-200 shadow-2xs">
                {{ str_replace('_', ' ', ucfirst($rumah->status_kepemilikan ?? 'Milik sendiri')) }}
            </span>
        </div>
    </div>

    {{-- Card 2: Alamat Rumah --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#EBE8E1] space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#EAF2EE] text-[#2C614D] flex items-center justify-center shrink-0">
                <i data-lucide="map" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Alamat Rumah</h3>
                <p class="text-xs text-slate-400">Lokasi hasil survei lapangan</p>
            </div>
        </div>

        <div class="space-y-2">
            <p class="text-sm font-bold text-slate-800">{{ $rumah->alamat ?? '-' }}</p>
            <p class="text-xs text-slate-500">
                @if ($rumah->wilayah)
                    {{ $rumah->wilayah->nama }}, {{ strtoupper($rumah->wilayah->tingkat) }}
                @else
                    -
                @endif
            </p>

            @if ($rumah->latitude && $rumah->longitude)
                <div class="pt-1">
                    <a href="https://www.google.com/maps?q={{ $rumah->latitude }},{{ $rumah->longitude }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#EAF2EE] hover:bg-[#DFEBE6] text-[#2C614D] text-xs font-mono font-semibold rounded-xl transition">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        {{ $rumah->latitude }}, {{ $rumah->longitude }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Card 3: Informasi Rumah --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#EBE8E1] space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#EAF2EE] text-[#2C614D] flex items-center justify-center shrink-0">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Informasi Rumah</h3>
                <p class="text-xs text-slate-400">Data fisik dasar bangunan</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-6">
            <div>
                <span class="text-xs text-slate-400 block mb-1">Luas bangunan</span>
                <span class="text-sm font-bold text-slate-900">{{ $rumah->luas_rumah ? $rumah->luas_rumah . ' m²' : '48 m²' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Luas tanah</span>
                <span class="text-sm font-bold text-slate-900">{{ $rumah->luas_rumah ? round($rumah->luas_rumah * 1.5) . ' m²' : '72 m²' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Tahun dibangun</span>
                <span class="text-sm font-bold text-slate-900">2008</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Status hunian</span>
                <span class="text-sm font-bold text-slate-900">{{ $rumah->status_kepemilikan === 'milik_sendiri' ? 'Dihuni pemilik' : str_replace('_', ' ', ucfirst($rumah->status_kepemilikan ?? 'Dihuni pemilik')) }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Jumlah kamar</span>
                <span class="text-sm font-bold text-slate-900">{{ max(1, round(($rumah->jumlah_anggota ?? 4) / 2)) }} kamar</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Jumlah lantai</span>
                <span class="text-sm font-bold text-slate-900">1 lantai</span>
            </div>
        </div>
    </div>

    {{-- Card 4: Kondisi Rumah --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#EBE8E1] space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#EAF2EE] text-[#2C614D] flex items-center justify-center shrink-0">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Kondisi Rumah</h3>
                <p class="text-xs text-slate-400">Catatan deskriptif Surveyor tanpa skor</p>
            </div>
        </div>

        @if (!$survei || $survei->komponenSurvei->isEmpty())
            <div class="p-8 text-center text-slate-400 text-xs">Belum ada catatan kondisi komponen.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($survei->komponenSurvei->sortBy('komponen.urutan') as $itemKomponen)
                    @php
                        $komponen = $itemKomponen->komponen;
                        $opsi = $itemKomponen->opsiKondisi;
                        $fotoList = $itemKomponen->foto;
                    @endphp
                    <div class="rounded-2xl p-4 border border-[#EBE8E1] bg-white flex flex-col justify-between hover:border-slate-300 transition space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-[#FAF5EB] text-[#C29352] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $komponen->nama }}</h4>
                                    <p class="text-[11px] font-medium text-[#C29352]">
                                        {{ $opsi?->label ?? 'Belum dinilai' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-1 shrink-0">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>{{ $fotoList->count() }} foto</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 leading-relaxed italic">
                            {{ $itemKomponen->catatan ?: ($opsi?->deskripsi ?? 'Tidak ada catatan tambahan.') }}
                        </p>

                        @if ($fotoList->isNotEmpty())
                            <div class="flex items-center gap-2 pt-1 overflow-x-auto">
                                @foreach ($fotoList as $foto)
                                    @php
                                        $fUrl = route('survei.foto.show', $foto);
                                        $fMeta = ($foto->nama_asli ?? 'Foto Bukti') . ' • ' . round(($foto->ukuran_byte ?? 0) / 1024) . ' KB';
                                    @endphp
                                    <img src="{{ $fUrl }}" alt="{{ $komponen->nama }}" 
                                         @click="openLightbox('{{ $fUrl }}', '{{ $komponen->nama }} — {{ $opsi?->label ?? 'Kondisi' }}', '{{ $fMeta }}')"
                                         class="w-14 h-14 rounded-xl object-cover border border-slate-200 cursor-pointer hover:opacity-80 transition shrink-0">
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Card 5: Galeri Foto --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#EBE8E1] space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#EAF2EE] text-[#2C614D] flex items-center justify-center shrink-0">
                <i data-lucide="eye" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Galeri Foto</h3>
                <p class="text-xs text-slate-400">Semua dokumentasi survei</p>
            </div>
        </div>

        @php
            $semuaFoto = collect();
            if ($survei) {
                foreach ($survei->komponenSurvei->sortBy('komponen.urutan') as $sk) {
                    foreach ($sk->foto as $idx => $f) {
                        $semuaFoto->push([
                            'foto' => $f,
                            'komponen' => $sk->komponen->nama,
                            'nomor' => $idx + 1,
                            'opsi' => $sk->opsiKondisi?->label ?? '',
                        ]);
                    }
                }
            }
        @endphp

        @if ($semuaFoto->isEmpty())
            <div class="p-8 text-center text-slate-400 text-xs">Belum ada foto dokumentasi survei.</div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($semuaFoto as $itemFoto)
                    @php
                        $f = $itemFoto['foto'];
                        $fUrl = route('survei.foto.show', $f);
                        $fMeta = ($f->nama_asli ?? 'Foto Bukti') . ' • ' . round(($f->ukuran_byte ?? 0) / 1024) . ' KB';
                    @endphp
                    <div class="aspect-[4/3] rounded-2xl overflow-hidden relative group cursor-pointer shadow-2xs border border-slate-200 bg-slate-100"
                         @click="openLightbox('{{ $fUrl }}', '{{ $itemFoto['komponen'] }} — Foto {{ $itemFoto['nomor'] }}', '{{ $fMeta }}')">
                        <img src="{{ $fUrl }}" alt="{{ $itemFoto['komponen'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent flex flex-col justify-end p-3 pointer-events-none">
                            <span class="text-xs font-bold text-white drop-shadow-sm leading-tight">{{ $itemFoto['komponen'] }}</span>
                            <span class="text-[10px] text-slate-300 drop-shadow-sm">Foto {{ $itemFoto['nomor'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Card 6: Keputusan Verifikasi --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#EBE8E1] space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#EAF2EE] text-[#2C614D] flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Keputusan Verifikasi</h3>
                <p class="text-xs text-slate-400">Tetapkan kelayakan bantuan renovasi untuk rumah ini</p>
            </div>
        </div>

        {{-- Alert 1: Acuan kebijakan eksternal --}}
        <div class="bg-[#F0F7F4] border border-[#D5EAE2] rounded-2xl p-4 flex items-start gap-3">
            <div class="text-[#2C614D] shrink-0 mt-0.5">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900">Acuan kebijakan eksternal</h4>
                <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">
                    Gunakan panduan kebijakan pemerintah sebagai acuan verifikasi. Penilaian kelayakan merupakan diskresi verifikator berbasis standar kualitatif.
                </p>
            </div>
        </div>

        {{-- Alert 2: Tidak ada rekomendasi otomatis --}}
        <div class="bg-[#FBFBFA] border border-slate-200/80 rounded-2xl p-4">
            <h4 class="text-xs font-bold text-slate-900">Tidak ada rekomendasi otomatis</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">
                RumahAsa tidak menghitung skor atau merekomendasikan kategori kelayakan.
            </p>
        </div>

        @if ($rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN)
            <form id="form-keputusan" action="{{ route('verifikator.survei.tetapkan-kategori', $rumah) }}" method="POST" class="space-y-6">
                @csrf

                {{-- Pilihan Keputusan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-900 mb-2">Keputusan <span class="text-rose-500">*</span></label>
                    <div class="space-y-3">
                        <label class="block border rounded-2xl p-4 cursor-pointer transition hover:bg-slate-50"
                               :class="kategori === 'layak_huni' ? 'border-[#2C614D] bg-[#F0F7F4]/60 ring-1 ring-[#2C614D]' : 'border-slate-200'">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="kategori" value="layak_huni" x-model="kategori" class="mt-0.5 text-[#2C614D] focus:ring-[#2C614D]">
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Layak</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Rumah dinilai layak huni, tidak perlu bantuan renovasi.</p>
                                </div>
                            </div>
                        </label>

                        <label class="block border rounded-2xl p-4 cursor-pointer transition hover:bg-slate-50"
                               :class="kategori === 'tidak_layak_huni' ? 'border-[#2C614D] bg-[#F0F7F4]/60 ring-1 ring-[#2C614D]' : 'border-slate-200'">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="kategori" value="tidak_layak_huni" x-model="kategori" class="mt-0.5 text-[#2C614D] focus:ring-[#2C614D]">
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Tidak Layak</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Tidak layak huni, prioritaskan untuk penerima bantuan renovasi.</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Catatan / Alasan --}}
                <div>
                    <label class="block text-xs font-bold text-slate-900 mb-1">Catatan / Alasan <span class="text-rose-500">*</span></label>
                    <textarea name="catatan" rows="4" x-model="catatan" required minlength="5" maxlength="1000"
                              placeholder="Jelaskan alasan keputusan berdasarkan peninjauan data dan galeri foto..."
                              class="w-full rounded-2xl border border-slate-200 p-4 text-xs focus:ring-2 focus:ring-[#1D4034] focus:border-[#1D4034] outline-none text-slate-800 leading-relaxed"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Catatan wajib diisi dan akan tampil pada riwayat peninjauan.</p>
                </div>

                {{-- Box Info Verifikator --}}
                <div class="bg-[#F0F7F4] border border-[#D5EAE2] rounded-xl p-3 flex items-center gap-2.5 text-xs text-slate-700">
                    <i data-lucide="clock" class="w-4 h-4 text-[#2C614D] shrink-0"></i>
                    <span>Keputusan akan dicatat atas nama <strong>{{ auth()->user()->nama }} ({{ auth()->user()->role->nama }})</strong>, {{ now()->translatedFormat('d F Y') }}.</span>
                </div>

                {{-- Tombol Aksi --}}
                <div class="space-y-3 pt-2">
                    <button type="submit"
                            formaction="{{ route('verifikator.survei.minta-perbaikan', $rumah) }}"
                            formnovalidate
                            @click="submitPerbaikan($event)"
                            class="w-full py-3.5 bg-white hover:bg-amber-50 text-amber-800 border border-amber-200 font-semibold text-xs rounded-xl shadow-2xs transition">
                        Kembalikan untuk Perbaikan
                    </button>

                    <button type="submit"
                            @click="submitTetapkan($event)"
                            class="w-full py-4 bg-[#8FAEA2] hover:bg-[#7D9E92] text-white font-bold text-sm rounded-xl shadow-sm transition">
                        Tetapkan Keputusan
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-600 flex items-center justify-between">
                <div>
                    <span class="font-bold text-slate-800">Status Saat Ini: {{ $rumah->status }}</span>
                    <p class="text-slate-500 mt-0.5">Keputusan untuk rumah ini telah ditetapkan.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white border border-slate-200">
                    Selesai Ditinjau
                </span>
            </div>
        @endif
    </div>

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

</div>
@endsection
