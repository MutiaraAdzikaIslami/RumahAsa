@extends('layouts.app')

@section('title', $progres ? 'Ubah Progres Renovasi' : 'Tambah Progres Renovasi')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Navigasi Kembali --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('bantuan.progres.index', $rumah) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-forest hover:text-forest-dark transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Progres Renovasi
        </a>
    </div>

    {{-- Form Tambah / Ubah Progres (Mockup Page 11) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
            <div>
                <span class="text-[11px] font-bold text-forest uppercase tracking-wider block">PEMBARUAN LAPANGAN</span>
                <h2 class="text-xl font-bold text-slate-900 mt-0.5">
                    {{ $progres ? 'Ubah Progres Renovasi' : 'Tambah Progres Renovasi' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">
                    {{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->kode_registrasi }}
                </p>
            </div>
            <a href="{{ route('bantuan.progres.index', $rumah) }}" class="text-slate-400 hover:text-slate-600 transition p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        @if ($errors->any())
            <div class="m-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 space-y-1">
                <p class="font-bold flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4"></i> Mohon periksa kembali input Anda:</p>
                <ul class="list-disc list-inside space-y-0.5 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $progres ? route('bantuan.progres.update', [$rumah, $progres]) : route('bantuan.progres.store', $rumah) }}"
              enctype="multipart/form-data"
              class="p-6 space-y-5">
            @csrf
            @if ($progres)
                @method('PUT')
            @endif

            {{-- Tahap Pekerjaan --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Tahap pekerjaan <span class="text-red-500">*</span>
                </label>
                @php
                    $tahapList = [
                        'Persiapan',
                        'Pekerjaan Pondasi',
                        'Pekerjaan Struktur',
                        'Pekerjaan Dinding',
                        'Pekerjaan Atap',
                        'Pekerjaan Lantai',
                        'Sanitasi / MCK',
                        'Finishing',
                    ];
                    $currentTahap = old('tahap_pekerjaan', $progres?->tahap_pekerjaan);
                @endphp
                <div class="space-y-2">
                    <select name="tahap_pekerjaan" required
                            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm cursor-pointer">
                        <option value="">Pilih tahap renovasi lapangan</option>
                        @foreach ($tahapList as $t)
                            <option value="{{ $t }}" {{ $currentTahap === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                        @if ($currentTahap && !in_array($currentTahap, $tahapList))
                            <option value="{{ $currentTahap }}" selected>{{ $currentTahap }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Persentase Progres --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Persentase progres <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="persentase" value="{{ old('persentase', $progres?->persentase) }}"
                               min="0" max="100" required placeholder="0"
                               class="w-full pl-3.5 pr-10 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">%</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Masukkan progres aktual antara 0-100% (tidak boleh lebih rendah dari riwayat sebelumnya).</p>
                </div>

                {{-- Tanggal Pembaruan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal pembaruan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_progres"
                           value="{{ old('tanggal_progres', $progres?->tanggal_progres ?? now()->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">
                </div>
            </div>

            {{-- Catatan Perkembangan --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Catatan perkembangan
                </label>
                <textarea name="catatan" rows="3" maxlength="1000"
                          placeholder="Jelaskan pekerjaan yang telah dilakukan, kondisi material, dan rencana berikutnya..."
                          class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">{{ old('catatan', $progres?->catatan) }}</textarea>
            </div>

            {{-- Upload Foto Dokumentasi (Mockup Page 11 & SCRUM-128) --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Foto dokumentasi
                </label>
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-forest/40 transition bg-slate-50/50">
                    <div class="w-10 h-10 rounded-full bg-forest/10 text-forest flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="image-plus" class="w-5 h-5"></i>
                    </div>
                    <label for="foto-input" class="cursor-pointer text-xs font-semibold text-forest hover:underline">
                        Pilih atau ambil foto
                    </label>
                    <p class="text-[11px] text-slate-400 mt-1">JPG, PNG, atau WEBP &middot; Maksimal 5 MB per foto</p>
                    <input id="foto-input" type="file" name="foto[]" multiple accept="image/*" class="mt-3 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-forest/10 file:text-forest hover:file:bg-forest/20">
                </div>

                {{-- Tampilkan foto yang sudah tersimpan jika sedang mode edit --}}
                @if ($progres && $progres->fotos->isNotEmpty())
                    <div class="mt-4">
                        <span class="text-xs font-medium text-slate-500 block mb-2">Foto Dokumentasi Tersimpan:</span>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            @foreach ($progres->fotos as $f)
                                <div class="relative rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 shadow-sm group">
                                    <img src="{{ route('bantuan.progres.foto', $f) }}" alt="Foto progres" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('bantuan.progres.index', $rumah) }}" class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan Progres
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
