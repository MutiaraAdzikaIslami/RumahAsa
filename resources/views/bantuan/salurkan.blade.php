@extends('layouts.app')

@section('title', 'Catat Bantuan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Navigasi Kembali --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('bantuan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-forest hover:text-forest-dark transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Penerima Bantuan
        </a>
        <a href="{{ route('bantuan.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition shadow-sm">
            Kembali
        </a>
    </div>

    {{-- Kartu Ringkasan Penerima Bantuan (Mockup Page 11) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-forest text-white flex items-center justify-center font-bold text-base shadow-sm">
                    {{ strtoupper(substr($rumah->nama_kepala_keluarga, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">{{ $rumah->nama_kepala_keluarga }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#FBF4E6] text-[#A26D27] border border-[#F2DEBF]">
                            Penerima Bantuan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span class="font-mono text-slate-600">{{ $rumah->kode_registrasi }}</span> &middot;
                        {{ $rumah->alamat }}, {{ $rumah->wilayah?->nama ?? '-' }}
                    </p>
                </div>
            </div>
            <div class="text-left md:text-right">
                <span class="text-xs font-medium text-slate-400 block">Kategori Kelayakan</span>
                <span class="text-xs font-semibold text-slate-700">Tidak Layak Huni &middot; Keputusan manual Verifikator</span>
            </div>
        </div>

        {{-- Banner Info Status Workflow --}}
        <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/70 text-emerald-900 text-xs flex items-start gap-3">
            <i data-lucide="info" class="w-4 h-4 text-emerald-700 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold">Rumah telah ditetapkan sebagai penerima bantuan.</p>
                <p class="text-emerald-700 mt-0.5">Status baru akan berubah menjadi <strong class="font-semibold">Dalam Renovasi</strong> setelah Verifikator mencatat penyaluran bantuan berdasarkan dokumen resmi.</p>
            </div>
        </div>

        {{-- Ringkasan Data Rumah --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-2 text-xs">
            <div>
                <span class="text-slate-400 block">NIK Pemilik</span>
                <span class="font-mono font-medium text-slate-700">{{ $rumah->nik_enc ? $rumah->nikTersamar() : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Jumlah Anggota Keluarga</span>
                <span class="font-medium text-slate-700">{{ $rumah->jumlah_anggota ?? '-' }} orang</span>
            </div>
            <div>
                <span class="text-slate-400 block">Surveyor Pendata</span>
                <span class="font-medium text-slate-700">{{ $rumah->surveyor?->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Wilayah Penugasan</span>
                <span class="font-medium text-slate-700">{{ $rumah->wilayah?->nama ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Form / Modal Catat Bantuan (Mockup Page 10) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
            <div>
                <span class="text-[11px] font-bold text-forest uppercase tracking-wider block">FR-20 &middot; VERIFIKATOR</span>
                <h2 class="text-xl font-bold text-slate-900 mt-0.5">Catat Bantuan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi bantuan yang diberikan sebelum memulai proses renovasi.</p>
            </div>
            <a href="{{ route('bantuan.index') }}" class="text-slate-400 hover:text-slate-600 transition p-1">
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

        <form method="POST" action="{{ route('bantuan.salurkan.store', $rumah) }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Tanggal Bantuan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Bantuan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_bantuan" value="{{ old('tanggal_bantuan', now()->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">
                </div>

                {{-- Jenis Bantuan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jenis Bantuan <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_bantuan" required
                            class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm cursor-pointer">
                        <option value="">Pilih jenis bantuan</option>
                        <option value="Bantuan Stimulan Perumahan Swadaya (BSPS)" {{ old('jenis_bantuan') === 'Bantuan Stimulan Perumahan Swadaya (BSPS)' ? 'selected' : '' }}>
                            Bantuan Stimulan Perumahan Swadaya (BSPS)
                        </option>
                        <option value="Bantuan Renovasi Rumah (RTLH)" {{ old('jenis_bantuan') === 'Bantuan Renovasi Rumah (RTLH)' ? 'selected' : '' }}>
                            Bantuan Renovasi Rumah (RTLH)
                        </option>
                        <option value="Material Bangunan" {{ old('jenis_bantuan') === 'Material Bangunan' ? 'selected' : '' }}>
                            Material Bangunan
                        </option>
                        <option value="Dana Tunai" {{ old('jenis_bantuan') === 'Dana Tunai' ? 'selected' : '' }}>
                            Dana Tunai
                        </option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nominal Bantuan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nominal Bantuan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" name="nominal" value="{{ old('nominal', '20000000') }}" min="0" step="1" required
                               placeholder="0"
                               class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">
                    </div>
                </div>

                {{-- Sumber Program (APBD / APBN / CSR / BAZNAS) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Sumber Program <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="sumber_program" value="{{ old('sumber_program') }}" required maxlength="100"
                           placeholder="Masukkan sumber program (mis. APBD, APBN, CSR, BAZNAS)"
                           class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">
                </div>
            </div>

            {{-- Nomor SK --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nomor SK <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" required maxlength="100"
                       placeholder="Masukkan nomor SK resmi (mis. SK/RTLH/2026/001)"
                       class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 shadow-sm">
            </div>

            {{-- Callout Kepastian Dokumen --}}
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-center gap-2.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-forest flex-shrink-0"></i>
                <span>Pastikan data bantuan sesuai dengan dokumen resmi yang diterbitkan. Data ini akan tercatat dalam audit log.</span>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('bantuan.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan Bantuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
