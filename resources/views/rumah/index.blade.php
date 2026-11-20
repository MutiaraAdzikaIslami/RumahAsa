@extends('layouts.app')

@section('title', 'Data Rumah')

@section('content')
@php
    $counts = $statusAlurCounts ?? array_fill_keys(\App\Models\RumahWarga::STATUS_ALUR_LIST, 0);
    $activeStatus = request('status');
@endphp

<div class="space-y-6">
    {{-- Header Halaman Sesuai Mockup Page 7 --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-wider text-amber-600">Pendataan Wilayah</p>
            <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Data Rumah</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data hasil survei lapangan secara tertib dan transparan.</p>
        </div>

        @can('input-rumah')
            <a href="{{ route('rumah.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition self-start sm:self-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Input Rumah
            </a>
        @endcan
    </div>

    {{-- 8 Kartu Status Alur Lengkap (Sesuai Draf Rancangan Terkini) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        @foreach (\App\Models\RumahWarga::STATUS_ALUR_LIST as $stLabel)
            @php
                $isStActive = $activeStatus === $stLabel;
                $dotColor = \App\Models\RumahWarga::STATUS_DOT_COLORS[$stLabel] ?? '#64748B';
            @endphp
            <a href="{{ route('rumah.index', array_filter(['status' => $isStActive ? null : $stLabel, 'search' => request('search'), 'wilayah_id' => request('wilayah_id')])) }}"
               class="rounded-2xl border p-4 transition flex flex-col justify-between {{ $isStActive ? 'bg-forest/5 border-forest ring-2 ring-forest/20 shadow-sm' : 'bg-white border-slate-200/90 hover:border-forest/40 shadow-xs' }}">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-xs font-semibold text-slate-600 leading-tight">{{ $stLabel }}</span>
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $dotColor }};"></span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900 mt-3">{{ $counts[$stLabel] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    {{-- Baris Pencarian & Filter Terpisah (Sesuai Mockup Page 7) --}}
    <form method="GET" action="{{ route('rumah.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
        {{-- Kotak 1: Pencarian Mandiri --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/90 px-4 py-2.5 shadow-xs flex items-center gap-3">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama, alamat, atau wilayah..."
                class="w-full text-xs text-slate-800 placeholder-slate-400 bg-transparent focus:outline-none"
            >
            @if (request('search'))
                <a href="{{ route('rumah.index', array_filter(['wilayah_id' => request('wilayah_id'), 'status' => request('status')])) }}" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>

        {{-- Kotak 2: Filter Wilayah Mandiri --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/90 px-4 py-2.5 shadow-xs flex items-center gap-2.5">
            <i data-lucide="map" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
            <select name="wilayah_id" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-700 bg-transparent focus:outline-none cursor-pointer">
                <option value="">Semua wilayah</option>
                @foreach ($wilayahList as $w)
                    <option value="{{ $w->id }}" {{ (string) request('wilayah_id') === (string) $w->id ? 'selected' : '' }}>
                        {{ ucfirst($w->tingkat) }} {{ $w->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Kotak 3: Filter 8 Status Alur Mandiri --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/90 px-4 py-2.5 shadow-xs flex items-center gap-2.5">
            <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
            <select name="status" onchange="this.form.submit()" class="w-full text-xs font-semibold text-slate-700 bg-transparent focus:outline-none cursor-pointer">
                <option value="">Semua status</option>
                @foreach (\App\Models\RumahWarga::STATUS_ALUR_LIST as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Kartu Tabel Seluruh Data Rumah --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Seluruh Data Rumah</h2>
                <p class="text-xs text-slate-500 mt-0.5">Rumah di wilayah tugas Anda dan tindakan yang diperlukan.</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                @if (request()->anyFilled(['search', 'wilayah_id', 'status']))
                    <a href="{{ route('rumah.index') }}" class="text-forest font-semibold hover:underline">Reset filter</a>
                    <span>&middot;</span>
                @endif
                <span>{{ $rumah->firstItem() ?? 0 }}–{{ $rumah->lastItem() ?? 0 }} dari {{ $rumah->total() }}</span>
            </div>
        </div>

        @if ($rumah->isEmpty())
            <div class="p-10 text-center space-y-2">
                <p class="text-sm font-semibold text-slate-600">Belum ada data rumah yang sesuai.</p>
                <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau pilihan filter wilayah dan status.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50/70 text-slate-400 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-5">Nama Pemilik</th>
                            <th class="py-3.5 px-4">Alamat</th>
                            <th class="py-3.5 px-4">Wilayah</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Terakhir Diperbarui</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rumah as $r)
                            @php
                                $stAlur = $r->statusAlur();
                                $progresPct = $r->penerimaBantuan?->progresTerakhir() ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5">
                                    <p class="font-bold text-slate-900 text-xs">{{ $r->nama_kepala_keluarga ?? '-' }}</p>
                                    <p class="font-mono text-[11px] text-slate-400 mt-0.5">
                                        ID {{ $r->kode_registrasi }}
                                        @if ($r->nik_enc)
                                            &middot;
                                            @can('view-audit-log')
                                                {{ app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNik($r->nik_enc) }}
                                            @else
                                                {{ app(\App\Modules\Core\Services\NikEncryptor::class)->tersamar($r->nik_enc) }}
                                            @endcan
                                        @endif
                                    </p>
                                </td>
                                <td class="py-4 px-4 text-slate-600 max-w-xs">
                                    {{ $r->alamatLengkap() }}
                                </td>
                                <td class="py-4 px-4 text-slate-600">
                                    {{ $r->wilayahSingkat() }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[11px] {{ $r->badgeStatusClass($stAlur) }}">
                                        {{ $stAlur }}
                                    </span>
                                    @if ($stAlur === 'Dalam Renovasi' && $progresPct === 0)
                                        <p class="text-[10px] text-amber-600 mt-1">Belum ada progres</p>
                                    @elseif ($stAlur === 'Dalam Renovasi' && $progresPct > 0)
                                        <p class="text-[10px] text-purple-600 font-semibold mt-1">Progres {{ $progresPct }}%</p>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $r->terakhirDiperbaruiLabel() }}
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    @can('surveyor')
                                        @if ($stAlur === 'Menunggu Disurvei')
                                            <a href="{{ route('survei.create', $r) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                                Mulai Survei
                                            </a>
                                        @elseif ($stAlur === 'Draf')
                                            <a href="{{ route('survei.create', $r) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                                Lanjutkan
                                            </a>
                                        @elseif ($stAlur === 'Perlu Perbaikan')
                                            <a href="{{ route('survei.create', $r) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                                Perbaiki
                                            </a>
                                        @elseif ($stAlur === 'Dalam Renovasi')
                                            <a href="{{ route('bantuan.progres.index', $r) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                                Isi Progres
                                            </a>
                                        @else
                                            <a href="{{ route('rumah.show', $r) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-xs">
                                                Lihat &rarr;
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('rumah.show', $r) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-xs">
                                            Lihat &rarr;
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
                <span>Menampilkan {{ $rumah->count() }} data</span>
                <div>
                    {{ $rumah->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
