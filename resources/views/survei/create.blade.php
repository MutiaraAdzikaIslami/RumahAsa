@extends('layouts.app')

@section('title_suffix', ' - Survei Kelayakan')
@section('header', 'Survei Kelayakan')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        @include('partials.flash')

        <div class="rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-forest-700 text-cream-50">
                    <i data-lucide="home" class="h-6 w-6"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-forest-900">{{ $rumah->kode_registrasi }}</h2>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $rumah->badgeColorClass() }}">
                            {{ $rumah->status }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-forest-600">
                        {{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->wilayah?->nama ?? '-' }}
                        &middot; {{ $rumah->alamat }}
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('survei.store', $rumah) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="h-4 w-4"></i>
                        <span class="font-semibold">Ada kesalahan input:</span>
                    </div>
                    <ul class="mt-2 list-disc pl-8 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <label for="tanggal_survei" class="block text-sm font-semibold text-forest-900">
                    Tanggal Survei <span class="text-red-500">*</span>
                </label>
                <input
                    id="tanggal_survei"
                    name="tanggal_survei"
                    type="date"
                    required
                    value="{{ old('tanggal_survei', now()->format('Y-m-d')) }}"
                    max="{{ now()->format('Y-m-d') }}"
                    class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30 sm:max-w-xs"
                >
            </div>

            @foreach ($komponenList as $komponen)
                <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-forest-900">{{ $komponen->urutan }}. {{ $komponen->nama }}</h3>
                        @if ($komponen->deskripsi)
                            <p class="mt-0.5 text-xs text-forest-500">{{ $komponen->deskripsi }}</p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @php
                            $opsiList = $komponen->opsiKondisi->sortBy('urutan');
                            $selectedOpsi = old("kondisi.{$komponen->id}");
                        @endphp

                        @foreach ($opsiList as $opsi)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-forest-50 p-3 transition hover:border-forest-300 hover:bg-cream-50">
                                <input
                                    type="radio"
                                    name="kondisi[{{ $komponen->id }}]"
                                    value="{{ $opsi->id }}"
                                    id="komp-{{ $komponen->id }}-opsi-{{ $opsi->id }}"
                                    @checked($selectedOpsi == $opsi->id)
                                    class="mt-0.5 h-4 w-4 border-forest-300 text-forest-600 focus:ring-forest-500"
                                >
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-forest-900">{{ $opsi->label }}</span>
                                    @if ($opsi->otomatis_rtlh)
                                        <span class="ml-2 inline-flex rounded-full bg-clay-100 px-2 py-0.5 text-xs font-semibold text-clay-600">Otomatis RTLH</span>
                                    @endif
                                    @if ($opsi->deskripsi)
                                        <p class="mt-0.5 text-xs text-forest-500">{{ $opsi->deskripsi }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach

                        @if ($opsiList->isEmpty())
                            <p class="text-xs text-red-500">Belum ada opsi nilai untuk komponen ini. Hubungi admin.</p>
                        @endif
                    </div>

                    {{-- === UPLOAD FOTO BUKTI === --}}
                    <div class="mt-4 border-t border-forest-50 pt-4">
                        <label class="block text-sm font-semibold text-forest-900 mb-1">
                            <i data-lucide="camera" class="inline-block h-4 w-4 mr-1"></i>
                            Foto Bukti (Opsional, maks. 5 foto, masing-masing 5MB)
                        </label>
                        <p class="text-xs text-forest-500 mb-2">Format: JPG, PNG, atau WebP</p>
                        <input
                            type="file"
                            name="foto[{{ $komponen->id }}][]"
                            multiple
                            accept="image/jpeg,image/png,image/webp"
                            class="block w-full text-sm text-forest-700 file:mr-3 file:rounded-lg file:border-0 file:bg-forest-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-forest-700 hover:file:bg-forest-100"
                        >
                        @error("foto.{$komponen->id}") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-between rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <a href="{{ route('survei.index') }}" class="text-sm font-medium text-forest-600 hover:underline">Batal</a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-forest-700 px-6 py-2.5 text-sm font-semibold text-cream-50 shadow-sm hover:bg-forest-800"
                    onclick="return confirm('Kirim survei ini? Setelah dikirim, status rumah akan menjadi DIAJUKAN dan tidak bisa diedit lagi kecuali diminta revisi oleh verifikator.');"
                >
                    <i data-lucide="send" class="h-4 w-4"></i>
                    Kirim Survei
                </button>
            </div>
        </form>
    </div>
@endsection
