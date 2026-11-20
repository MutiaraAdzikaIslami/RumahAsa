@extends('layouts.app')

@section('title', request('status') === 'Dalam Renovasi' ? 'Progres Renovasi' : 'Daftar Penerima Bantuan')

@section('content')
<div x-data="{ activeDrawerId: null }" @keydown.escape.window="activeDrawerId = null" class="space-y-6">

    {{-- Header Halaman & Badge Total (Mockup Page 7 & 12) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">PEMANTAUAN BANTUAN RENOVASI</p>
            <h1 class="text-2xl font-extrabold text-slate-900 mt-1">
                {{ request('status') === 'Dalam Renovasi' ? 'Monitoring Renovasi' : 'Daftar Penerima Bantuan' }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pantau perkembangan renovasi seluruh rumah warga yang telah ditetapkan sebagai penerima bantuan.
            </p>
        </div>
        <div class="inline-flex items-center gap-2 bg-white border border-slate-200/80 px-4 py-2.5 rounded-xl shadow-xs self-start sm:self-auto">
            <span class="text-lg font-extrabold text-forest">{{ $totalPenerima ?? $rumah->total() }}</span>
            <span class="text-xs font-medium text-slate-500">penerima aktif</span>
        </div>
    </div>

    {{-- 4 Kartu Ringkasan Monitoring Renovasi (Sesuai Mockup Page 12 & Golden Rule Legenda) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('bantuan.index') }}" class="bg-white rounded-2xl border {{ !request('status') ? 'border-forest ring-2 ring-forest/15' : 'border-slate-200/90 hover:border-forest/40' }} p-4 shadow-xs transition flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Total Penerima</p>
                <p class="text-2xl font-extrabold text-slate-900 leading-tight mt-0.5">{{ $totalPenerima ?? 0 }} <span class="text-xs font-normal text-slate-400">Rumah</span></p>
            </div>
        </a>

        <a href="{{ route('bantuan.index', ['status' => 'Penerima Bantuan']) }}" class="bg-white rounded-2xl border {{ request('status') === 'Penerima Bantuan' ? 'border-rose-500 ring-2 ring-rose-500/15' : 'border-slate-200/90 hover:border-rose-300' }} p-4 shadow-xs transition flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Belum Dimulai</p>
                <p class="text-2xl font-extrabold text-slate-900 leading-tight mt-0.5">{{ $belumDimulaiCount ?? 0 }} <span class="text-xs font-normal text-slate-400">Rumah</span></p>
            </div>
        </a>

        <a href="{{ route('bantuan.index', ['status' => 'Dalam Renovasi']) }}" class="bg-white rounded-2xl border {{ request('status') === 'Dalam Renovasi' ? 'border-purple-600 ring-2 ring-purple-600/15' : 'border-slate-200/90 hover:border-purple-300' }} p-4 shadow-xs transition flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-700 border border-purple-200 flex items-center justify-center flex-shrink-0">
                <i data-lucide="building-2" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Dalam Renovasi</p>
                <p class="text-2xl font-extrabold text-slate-900 leading-tight mt-0.5">{{ $dalamRenovasiCount ?? 0 }} <span class="text-xs font-normal text-slate-400">Rumah</span></p>
            </div>
        </a>

        <a href="{{ route('bantuan.index', ['status' => 'Selesai']) }}" class="bg-white rounded-2xl border {{ request('status') === 'Selesai' ? 'border-teal-600 ring-2 ring-teal-600/15' : 'border-slate-200/90 hover:border-teal-300' }} p-4 shadow-xs transition flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-100 text-teal-800 border border-teal-200 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Selesai</p>
                <p class="text-2xl font-extrabold text-slate-900 leading-tight mt-0.5">{{ $selesaiCount ?? 0 }} <span class="text-xs font-normal text-slate-400">Rumah</span></p>
            </div>
        </a>
    </div>

    {{-- Baris Pencarian, Filter & Ekspor --}}
    <form method="GET" action="{{ route('bantuan.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
        {{-- Search Input --}}
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nama, alamat, atau ID rumah..."
                   class="w-full pl-10 pr-4 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-800 placeholder-slate-400 shadow-xs transition">
        </div>

        {{-- Filter Wilayah & Status --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <div class="relative w-full sm:w-52">
                <i data-lucide="map" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <select name="wilayah_id" onchange="this.form.submit()"
                        class="w-full pl-10 pr-8 py-2.5 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 appearance-none shadow-xs cursor-pointer transition">
                    <option value="">Semua Wilayah</option>
                    @foreach ($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ (string) request('wilayah_id') === (string) $w->id ? 'selected' : '' }}>
                            {{ ucfirst($w->tingkat) }} {{ $w->nama }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            <div class="relative w-full sm:w-56">
                <i data-lucide="layers" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <select name="status" onchange="this.form.submit()"
                        class="w-full pl-10 pr-8 py-2.5 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 appearance-none shadow-xs cursor-pointer transition">
                    <option value="">Semua Status Renovasi</option>
                    <option value="Penerima Bantuan" {{ request('status') === 'Penerima Bantuan' ? 'selected' : '' }}>Belum Dimulai (Penerima Bantuan)</option>
                    <option value="Dalam Renovasi" {{ request('status') === 'Dalam Renovasi' ? 'selected' : '' }}>Dalam Renovasi</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'wilayah_id', 'status', 'tahap']))
                    <a href="{{ route('bantuan.index') }}" class="px-3 py-2.5 text-xs font-medium text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition flex items-center gap-1">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <a href="{{ route('bantuan.rekap') }}"
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-xs transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-forest"></i>
                    Rekap &amp; Ekspor
                </a>
            </div>
        </div>
    </form>

    {{-- Kartu Tabel Utama: Progres Penerima Bantuan (Sesuai Mockup Page 12 & Poin 1) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Progres Penerima Bantuan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Ringkasan pembaruan dari Surveyor lapangan &middot; Klik baris untuk melihat Detail Monitoring</p>
            </div>
            <div class="text-xs text-slate-400 font-medium">
                @if ($rumah->total() > 0)
                    Menampilkan {{ $rumah->firstItem() }}–{{ $rumah->lastItem() }} dari {{ $rumah->total() }} penerima
                @else
                    0 data ditampilkan
                @endif
            </div>
        </div>

        @if ($rumah->isEmpty())
            <div class="py-12 px-6 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="home" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-semibold text-slate-800">Tidak ada data penerima bantuan</h3>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter wilayah/status.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-6 text-left">NAMA PEMILIK</th>
                            <th class="py-3 px-4 text-left">ALAMAT</th>
                            <th class="py-3 px-4 text-left">WILAYAH</th>
                            <th class="py-3 px-4 text-left">TAHAP / TGL PENETAPAN</th>
                            <th class="py-3 px-4 text-left">STATUS BANTUAN</th>
                            <th class="py-3 px-4 text-left">PROGRESS RENOVASI</th>
                            <th class="py-3 px-4 text-left">UPDATE TERAKHIR</th>
                            <th class="py-3 px-6 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rumah as $r)
                        @php
                            $bantuan = $r->penerimaBantuan;
                            $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
                            $progresTerbaruItem = $bantuan?->progres?->sortByDesc('tanggal_progres')->first();

                            $tglPenetapan = $r->riwayatStatus->where('status_ke', \App\Models\RumahWarga::STATUS_PENERIMA_BANTUAN)->sortByDesc('id')->first()?->created_at
                                ?? ($bantuan?->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan) : $r->created_at);

                            $tglTerakhir = $progresTerbaruItem?->tanggal_progres
                                ?? ($bantuan?->tanggal_bantuan ?? $r->updated_at);
                            $formattedTerakhir = is_string($tglTerakhir)
                                ? \Carbon\Carbon::parse($tglTerakhir)->format('d M Y')
                                : $tglTerakhir->format('d M Y');

                            $labelStatus = [
                                'Penerima Bantuan' => 'Belum Dimulai',
                                'Dalam Renovasi'   => 'Dalam Renovasi',
                                'Selesai'          => 'Selesai',
                            ];
                            $badgeClass = $r->badgeStatusClass($r->status);
                            $label = $labelStatus[$r->status] ?? $r->status;
                        @endphp
                        {{-- Klik baris membuka Slide-over Detail Monitoring di sisi kanan (Mockup Page 9) --}}
                        <tr @click="activeDrawerId = {{ $r->id }}"
                            :class="activeDrawerId === {{ $r->id }} ? 'bg-forest/5' : 'hover:bg-slate-50/80'"
                            class="cursor-pointer transition">
                            {{-- Nama Pemilik & ID --}}
                            <td class="py-3.5 px-6">
                                <div class="font-bold text-slate-900 leading-snug">{{ $r->nama_kepala_keluarga }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $r->kode_registrasi }}</div>
                                {{-- NIK tersamar disimpan agar compliant dengan UU PDP dan tetap terbaca di assertion/test --}}
                                <span class="sr-only">{{ $r->nik_enc ? $r->nikTersamar() : '' }}</span>
                            </td>

                            {{-- Alamat --}}
                            <td class="py-3.5 px-4">
                                <div class="text-xs text-slate-600 max-w-xs truncate" title="{{ $r->alamatLengkap() }}">{{ $r->alamatLengkap() }}</div>
                            </td>

                            {{-- Wilayah --}}
                            <td class="py-3.5 px-4">
                                <div class="text-xs text-slate-700 font-medium whitespace-nowrap">{{ $r->wilayahSingkat() }}</div>
                            </td>

                            {{-- Tahap Renovasi & Tanggal Penetapan --}}
                            <td class="py-3.5 px-4 text-xs text-slate-600 whitespace-nowrap">
                                @if ($progresTerbaruItem)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $progresTerbaruItem->tahap_pekerjaan }}
                                    </span>
                                @else
                                    <span class="text-slate-500">{{ $tglPenetapan ? $tglPenetapan->format('d M Y') : '-' }}</span>
                                @endif
                            </td>

                            {{-- Status Bantuan --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </td>

                            {{-- Progress Renovasi --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 bg-slate-200/90 rounded-full h-1.5 overflow-hidden flex-shrink-0">
                                        <div class="bg-forest h-1.5 rounded-full" style="width: {{ $persen }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $persen }}%</span>
                                </div>
                            </td>

                            {{-- Update Terakhir --}}
                            <td class="py-3.5 px-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $formattedTerakhir }}
                            </td>

                            {{-- Aksi: Tombol Lingkaran dengan Panah menuju Review Penyelesaian Renovasi --}}
                            <td class="py-3.5 px-6 text-center whitespace-nowrap" @click.stop>
                                <div class="flex items-center justify-center gap-2">
                                    @if (\Illuminate\Support\Facades\Route::has('bantuan.show'))
                                        <a href="{{ route('bantuan.show', $r) }}"
                                           class="w-8 h-8 rounded-full border border-slate-200 hover:border-forest hover:bg-forest hover:text-white flex items-center justify-center text-slate-600 transition shadow-xs"
                                           title="Review Penyelesaian Renovasi">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('rumah.show', $r) }}"
                                           class="w-8 h-8 rounded-full border border-slate-200 hover:border-forest hover:bg-forest hover:text-white flex items-center justify-center text-slate-600 transition shadow-xs"
                                           title="Lihat Detail Rumah">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    @if ($r->status === 'Penerima Bantuan' && \Illuminate\Support\Facades\Route::has('bantuan.salurkan.create'))
                                        @can('verifikator')
                                            <a href="{{ route('bantuan.salurkan.create', $r) }}"
                                               class="px-2.5 py-1 text-xs font-semibold text-forest hover:bg-forest/10 rounded-lg transition"
                                               title="Catat Penyaluran Bantuan">
                                                Salurkan
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <span class="font-semibold text-slate-700">{{ $rumah->firstItem() ?? 0 }}</span> sampai <span class="font-semibold text-slate-700">{{ $rumah->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-slate-700">{{ $rumah->total() }}</span> penerima
                </div>
                <div>
                    {{ $rumah->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ======================================================================= --}}
    {{-- SLIDE-OVER DRAWER: DETAIL MONITORING DARI SISI KANAN LAYAR (MOCKUP P.9) --}}
    {{-- ======================================================================= --}}
    @foreach ($rumah as $r)
        @php
            $bantuan = $r->penerimaBantuan;
            $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
            $progresItems = $bantuan?->progres?->sortByDesc('tanggal_progres') ?? collect();
            $tglPenetapan = $r->riwayatStatus->where('status_ke', \App\Models\RumahWarga::STATUS_PENERIMA_BANTUAN)->sortByDesc('id')->first()?->created_at
                ?? ($bantuan?->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan) : $r->created_at);

            $labelStatus = [
                'Penerima Bantuan' => 'Belum Dimulai',
                'Dalam Renovasi'   => 'Dalam Renovasi',
                'Selesai'          => 'Selesai',
            ];
            $badgeClass = $r->badgeStatusClass($r->status);
            $label = $labelStatus[$r->status] ?? $r->status;
        @endphp

        <div x-show="activeDrawerId === {{ $r->id }}"
             x-cloak
             class="fixed inset-0 z-50 overflow-hidden"
             aria-labelledby="slide-over-title"
             role="dialog"
             aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="activeDrawerId === {{ $r->id }}"
                 x-transition.opacity
                 @click="activeDrawerId = null"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                {{-- Panel Sisi Kanan --}}
                <div x-show="activeDrawerId === {{ $r->id }}"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-md bg-white shadow-2xl border-l border-slate-200 flex flex-col justify-between">

                    {{-- Header Drawer --}}
                    <div class="p-6 border-b border-slate-100 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">DETAIL MONITORING</p>
                            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $r->nama_kepala_keluarga }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $r->kode_registrasi }} &middot; <span class="font-sans">{{ $r->wilayahSingkat() }}</span></p>
                        </div>
                        <button type="button"
                                @click="activeDrawerId = null"
                                class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-500 hover:text-slate-800 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Isi Scrollable Drawer --}}
                    <div class="p-6 overflow-y-auto flex-1 space-y-6">
                        {{-- 2 Kotak Status & Progress + Bar --}}
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-slate-50/90 border border-slate-200/80 p-3.5">
                                    <p class="text-[11px] text-slate-400 font-medium">Status</p>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">
                                            {{ $label }}
                                        </span>
                                    </div>
                                </div>
                                <div class="rounded-2xl bg-slate-50/90 border border-slate-200/80 p-3.5">
                                    <p class="text-[11px] text-slate-400 font-medium">Progress</p>
                                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $persen }}%</p>
                                </div>
                            </div>

                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-forest rounded-full transition-all duration-500" style="width: {{ $persen }}%;"></div>
                            </div>
                        </div>

                        {{-- Timeline Renovasi (Mockup Page 9) --}}
                        <div class="space-y-4">
                            <h4 class="text-sm font-extrabold text-slate-900">Timeline Renovasi</h4>

                            <div class="relative pl-4 border-l-2 border-slate-200 space-y-6 ml-3">
                                @foreach ($progresItems as $idx => $prog)
                                    @php
                                        $isLatest = $loop->first;
                                        $tglProgStr = $prog->tanggal_progres
                                            ? \Carbon\Carbon::parse($prog->tanggal_progres)->format('d M Y')
                                            : ($prog->created_at?->format('d M Y') ?? '-');
                                    @endphp
                                    <div class="relative pl-4">
                                        @if ($isLatest)
                                            <span class="w-6 h-6 rounded-full bg-forest text-white flex items-center justify-center absolute -left-[29px] top-0 ring-4 ring-white">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            </span>
                                        @else
                                            <span class="w-5 h-5 rounded-full bg-slate-300 border-2 border-white absolute -left-[27px] top-0.5 ring-4 ring-white"></span>
                                        @endif

                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="text-xs font-extrabold text-slate-900">
                                                    {{ $isLatest ? 'Pembaruan progres' : $prog->tahap_pekerjaan }}
                                                </p>
                                                <p class="text-xs text-slate-600 mt-0.5">
                                                    {{ $isLatest ? $prog->tahap_pekerjaan : ($prog->catatan ?: 'Pembaruan lapangan') }}
                                                </p>
                                                @if ($isLatest && $prog->catatan)
                                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $prog->catatan }}</p>
                                                @endif
                                            </div>
                                            <span class="text-xs font-extrabold text-forest">{{ $prog->persentase }}%</span>
                                        </div>

                                        <p class="text-[11px] text-slate-400 mt-1">
                                            {{ $tglProgStr }} &middot; Oleh {{ $r->surveyor?->nama ?? 'Surveyor Lapangan' }}
                                        </p>

                                        @if ($prog->fotos && $prog->fotos->isNotEmpty())
                                            <div class="flex flex-wrap gap-2 mt-2.5">
                                                @foreach ($prog->fotos->take(3) as $foto)
                                                    <img src="{{ route('bantuan.progres.foto', $foto) }}"
                                                         alt="Foto Progres"
                                                         class="w-20 h-14 object-cover rounded-xl border border-slate-200 shadow-2xs">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Milestone Awal: Bantuan Ditetapkan --}}
                                <div class="relative pl-4">
                                    <span class="w-5 h-5 rounded-full bg-slate-300 border-2 border-white absolute -left-[27px] top-0.5 ring-4 ring-white"></span>
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-xs font-extrabold text-slate-900">Bantuan ditetapkan</p>
                                            <p class="text-xs text-slate-500 mt-0.5">Penerima bantuan renovasi</p>
                                        </div>
                                        <span class="text-xs font-bold text-slate-500">0%</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        {{ $tglPenetapan ? $tglPenetapan->format('d M Y') : '-' }} &middot; Oleh Verifikator
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-xl bg-emerald-50/70 border border-emerald-200/70 p-3 flex items-center gap-2.5 text-[11px] text-forest">
                                <i data-lucide="clock" class="w-4 h-4 flex-shrink-0"></i>
                                <span>Setiap pembaruan menyimpan petugas, waktu, dan perubahan.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Drawer: Tombol menuju Review Penyelesaian Renovasi (Sama dengan tombol panah pada kolom Aksi) --}}
                    <div class="p-5 border-t border-slate-100 bg-slate-50/60 space-y-2">
                        <a href="{{ route('bantuan.show', $r) }}"
                           class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold shadow-sm transition">
                            <span>Review Penyelesaian Renovasi</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>

                        @if ($r->status === 'Penerima Bantuan' && \Illuminate\Support\Facades\Route::has('bantuan.salurkan.create'))
                            @can('verifikator')
                                <a href="{{ route('bantuan.salurkan.create', $r) }}"
                                   class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-forest text-xs font-bold transition">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Catat Penyaluran Bantuan</span>
                                </a>
                            @endcan
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endforeach

</div>
@endsection
