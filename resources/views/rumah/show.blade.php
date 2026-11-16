@extends('layouts.app')

@section('title_suffix', ' - Detail Rumah')
@section('header', 'Detail Rumah')

@section('content')
    <div class="space-y-6">
        @include('partials.flash')

        <div class="rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="grid h-14 w-14 place-items-center rounded-xl bg-forest-700 text-cream-50">
                        <i
                            data-lucide="home"
                            class="h-7 w-7"
                        ></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-forest-900">{{ $rumah->kode_registrasi }}
                            </h2>
                            <span
                                class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold {{ $rumah->badgeColorClass() }}"
                            >
                                {{ $rumah->status }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-forest-600">{{ $rumah->nama_kepala_keluarga }}
                            &middot; {{ $rumah->wilayah?->nama ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if ($rumah->bisaDieditOlehAdmin())
                        <a
                            href="{{ route('rumah.edit', $rumah) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-forest-100 bg-white px-4 py-2 text-sm font-medium text-forest-700 hover:bg-forest-50"
                        >
                            <i
                                data-lucide="pencil"
                                class="h-4 w-4"
                            ></i> Edit
                        </a>
                        <form
                            method="POST"
                            action="{{ route('rumah.destroy', $rumah) }}"
                            onsubmit="return confirm('Hapus data rumah ini?')"
                            class="inline"
                        >
                            @csrf @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                            >
                                <i
                                    data-lucide="trash-2"
                                    class="h-4 w-4"
                                ></i> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-forest-50">
                        <i
                            data-lucide="users"
                            class="h-4 w-4 text-forest-600"
                        ></i>
                    </div>
                    <h3 class="font-bold text-forest-900">Data Warga</h3>
                </div>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-forest-500">Nama Kepala Keluarga</dt>
                        <dd class="font-medium text-forest-900">{{ $rumah->nama_kepala_keluarga }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">NIK (tersamar)</dt>
                        <dd class="font-mono text-forest-900">{{ $rumah->nikTersamar() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">No. Kartu Keluarga (tersamar)</dt>
                        <dd class="font-mono text-forest-900">{{ $rumah->noKkTersamar() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">Jumlah Anggota</dt>
                        <dd class="font-medium text-forest-900">{{ $rumah->jumlah_anggota }} orang</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">Pekerjaan</dt>
                        <dd class="font-medium text-forest-900">{{ $rumah->pekerjaan ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">Penghasilan</dt>
                        <dd class="font-medium text-forest-900">Rp
                            {{ number_format((float) $rumah->penghasilan, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">Status Kepemilikan</dt>
                        <dd class="font-medium text-forest-900">
                            {{ ucfirst(str_replace('_', ' ', $rumah->status_kepemilikan)) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-forest-50 pt-3">
                        <dt class="text-forest-500">Luas Rumah</dt>
                        <dd class="font-medium text-forest-900">
                            {{ $rumah->luas_rumah ? $rumah->luas_rumah . ' m²' : '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-forest-50">
                            <i
                                data-lucide="map-pin"
                                class="h-4 w-4 text-forest-600"
                            ></i>
                        </div>
                        <h3 class="font-bold text-forest-900">Lokasi</h3>
                    </div>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-forest-500">Alamat</dt>
                            <dd class="mt-0.5 font-medium text-forest-900">{{ $rumah->alamat }}</dd>
                        </div>
                        <div class="border-t border-forest-50 pt-3">
                            <dt class="text-forest-500">RT / RW</dt>
                            <dd class="mt-0.5 font-medium text-forest-900">
                                {{ $rumah->wilayah?->nama ?? '-' }} /
                                {{ $rumah->wilayah?->parent?->nama ?? '-' }}</dd>
                        </div>
                        <div class="border-t border-forest-50 pt-3">
                            <dt class="text-forest-500">Kelurahan/Desa</dt>
                            <dd class="mt-0.5 font-medium text-forest-900">
                                {{ $rumah->wilayah?->parent?->parent?->nama ?? '-' }}</dd>
                        </div>
                        <div class="border-t border-forest-50 pt-3">
                            <dt class="text-forest-500">Koordinat GPS</dt>
                            <dd class="mt-0.5 font-mono text-forest-900">{{ $rumah->latitude ?? '-' }},
                                {{ $rumah->longitude ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-forest-50">
                            <i
                                data-lucide="user-check"
                                class="h-4 w-4 text-forest-600"
                            ></i>
                        </div>
                        <h3 class="font-bold text-forest-900">Penugasan Surveyor</h3>
                    </div>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="grid h-10 w-10 place-items-center rounded-lg bg-forest-700 text-xs font-bold text-cream-50">
                                {{ strtoupper(substr($rumah->surveyor?->nama ?? '??', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-forest-900">
                                    {{ $rumah->surveyor?->nama ?? '-' }}</p>
                                <p class="text-xs text-forest-500">{{ $rumah->surveyor?->username ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="border-t border-forest-50 pt-3">
                            <dt class="text-forest-500">Tanggal Input</dt>
                            <dd class="mt-0.5 font-medium text-forest-900">
                                {{ $rumah->created_at?->format('d M Y, H.i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        @if ($rumah->status === \App\Models\RumahWarga::STATUS_MENUNGGU_SURVEI)
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 p-5">
                <div class="flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-amber-100">
                        <i
                            data-lucide="alert-triangle"
                            class="h-5 w-5 text-amber-600"
                        ></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-amber-900">Menunggu Survei</h3>
                        <p class="mt-1 text-sm text-amber-800">
                            Data rumah ini sudah lengkap dan ditugaskan ke surveyor
                            <strong>{{ $rumah->surveyor?->nama ?? '-' }}</strong>.
                            Surveyor akan melihat rumah ini di halaman <em>Daftar Tugas Survei</em> dan
                            melakukan Survei Kelayakan.
                            Admin tidak perlu melanjutkan ke form Survei Kelayakan.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if ($rumah->riwayatStatus->isNotEmpty())
            <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-forest-50">
                        <i
                            data-lucide="history"
                            class="h-4 w-4 text-forest-600"
                        ></i>
                    </div>
                    <h3 class="font-bold text-forest-900">Riwayat Status</h3>
                </div>
                <ol class="space-y-3 text-sm">
                    @foreach ($rumah->riwayatStatus->sortByDesc('created_at') as $riw)
                        <li class="flex items-start gap-3 rounded-lg border border-forest-50 p-3">
                            <div
                                class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-forest-50">
                                <i
                                    data-lucide="arrow-right"
                                    class="h-4 w-4 text-forest-600"
                                ></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-forest-900">
                                    {{ $riw->status_dari }} &rarr; {{ $riw->status_ke }}
                                </p>
                                <p class="text-xs text-forest-500">
                                    {{ $riw->created_at?->format('d M Y, H.i') }} &middot;
                                    oleh {{ $riw->user?->nama ?? 'System' }}
                                </p>
                                @if ($riw->catatan)
                                    <p class="mt-1 text-xs italic text-forest-600">
                                        "{{ $riw->catatan }}"</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif
    </div>
@endsection
