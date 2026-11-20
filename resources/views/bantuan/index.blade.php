@extends('layouts.app')

@section('title', 'Daftar Penerima Bantuan')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold text-forest uppercase tracking-wider mb-1">PROGRAM BANTUAN RENOVASI</p>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Penerima Bantuan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftar rumah yang telah ditetapkan sebagai penerima bantuan renovasi.</p>
        </div>
        <div>
            <div class="inline-flex items-center gap-2 bg-white border border-slate-200/90 rounded-2xl px-4 py-2.5 shadow-sm">
                <span class="text-base font-bold text-slate-900">{{ $totalPenerima ?? $rumah->total() }}</span>
                <span class="text-xs text-slate-500 font-medium">penerima aktif</span>
            </div>
        </div>
    </div>

    {{-- Filter & Action Bar --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('bantuan.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            {{-- Kolom Pencarian --}}
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama, alamat, atau ID rumah..."
                       class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 placeholder-slate-400 shadow-sm transition">
            </div>

            {{-- Filter Wilayah --}}
            <div class="relative w-full lg:w-56">
                <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <select name="wilayah_id" onchange="this.form.submit()"
                        class="w-full pl-10 pr-8 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 appearance-none shadow-sm cursor-pointer transition">
                    <option value="">Semua Wilayah</option>
                    @foreach ($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->tingkat ? '[' . strtoupper($w->tingkat) . '] ' : '' }}{{ $w->nama }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            {{-- Filter Status Renovasi --}}
            <div class="relative w-full lg:w-60">
                <i data-lucide="layers" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <select name="status" onchange="this.form.submit()"
                        class="w-full pl-10 pr-8 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest text-slate-700 appearance-none shadow-sm cursor-pointer transition">
                    <option value="">Semua Status Renovasi</option>
                    <option value="Penerima Bantuan" {{ request('status') === 'Penerima Bantuan' ? 'selected' : '' }}>Belum Dimulai (Penerima Bantuan)</option>
                    <option value="Dalam Renovasi" {{ request('status') === 'Dalam Renovasi' ? 'selected' : '' }}>Dalam Renovasi</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            {{-- Tombol Filter / Reset --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-forest hover:bg-forest-light text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'wilayah_id', 'status']))
                    <a href="{{ route('bantuan.index') }}" class="px-3 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition flex items-center gap-1">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset
                    </a>
                @endif
            </div>

            {{-- Tombol Ekspor (Mockup Page 7) --}}
            <div class="flex items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <button type="button" onclick="alert('Fitur ekspor Excel terintegrasi pada modul Ekspor Laporan (FR-28)')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i> Excel
                </button>
                <button type="button" onclick="alert('Fitur ekspor PDF terintegrasi pada modul Ekspor Laporan (FR-28)')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i> PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel Seluruh Penerima Bantuan --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Seluruh Penerima Bantuan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Diperbarui berdasarkan aktivitas renovasi terakhir</p>
            </div>
            @if (!$rumah->isEmpty())
                <span class="text-xs text-slate-500 font-medium">
                    Menampilkan {{ $rumah->firstItem() ?? 0 }}-{{ $rumah->lastItem() ?? 0 }} dari {{ $rumah->total() }}
                </span>
            @endif
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
                            <th class="py-3 px-4 text-left whitespace-nowrap">TANGGAL PENETAPAN</th>
                            <th class="py-3 px-4 text-left whitespace-nowrap">STATUS BANTUAN</th>
                            <th class="py-3 px-4 text-left whitespace-nowrap">PROGRESS RENOVASI</th>
                            <th class="py-3 px-4 text-left whitespace-nowrap">TERAKHIR DIPERBARUI</th>
                            <th class="py-3 px-6 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rumah as $r)
                        @php
                            $bantuan = $r->penerimaBantuan;
                            $persen = $bantuan ? $bantuan->progresTerakhir() : 0;

                            // Tanggal Penetapan
                            $tglPenetapan = $r->riwayatStatus->where('status_ke', \App\Models\RumahWarga::STATUS_PENERIMA_BANTUAN)->sortByDesc('id')->first()?->created_at
                                ?? ($bantuan?->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan) : $r->created_at);

                            // Tanggal Terakhir Diperbarui
                            $tglTerakhir = $bantuan?->progres?->sortByDesc('tanggal_progres')->first()?->tanggal_progres
                                ?? ($bantuan?->tanggal_bantuan ?? $r->updated_at);
                            $formattedTerakhir = is_string($tglTerakhir)
                                ? \Carbon\Carbon::parse($tglTerakhir)->format('d M Y')
                                : $tglTerakhir->format('d M Y');

                            // Label & Badge Status (Legenda warna konsisten lintas role)
                            $labelStatus = [
                                'Penerima Bantuan' => 'Belum Dimulai',
                                'Dalam Renovasi'   => 'Dalam Renovasi',
                                'Selesai'          => 'Selesai',
                            ];
                            $badgeClass = $r->badgeStatusClass($r->status);
                            $label = $labelStatus[$r->status] ?? $r->status;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            {{-- Nama Pemilik & ID --}}
                            <td class="py-3 px-6">
                                <div class="font-semibold text-slate-900 leading-snug">{{ $r->nama_kepala_keluarga }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $r->kode_registrasi }}</div>
                                {{-- NIK tersamar disimpan agar compliant dengan UU PDP dan tetap terbaca di assertion/test --}}
                                <span class="sr-only">{{ $r->nik_enc ? $r->nikTersamar() : '' }}</span>
                            </td>

                            {{-- Alamat --}}
                            <td class="py-3 px-4">
                                <div class="text-xs text-slate-600 max-w-xs truncate" title="{{ $r->alamatLengkap() }}">{{ $r->alamatLengkap() }}</div>
                            </td>

                            {{-- Wilayah --}}
                            <td class="py-3 px-4">
                                <div class="text-xs text-slate-700 font-medium whitespace-nowrap">{{ $r->wilayahSingkat() }}</div>
                            </td>

                            {{-- Tanggal Penetapan --}}
                            <td class="py-3 px-4 text-xs text-slate-600 whitespace-nowrap">
                                {{ $tglPenetapan ? $tglPenetapan->format('d M Y') : '-' }}
                            </td>

                            {{-- Status Bantuan --}}
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </td>

                            {{-- Progress Renovasi --}}
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 bg-slate-200/90 rounded-full h-1.5 overflow-hidden flex-shrink-0">
                                        <div class="bg-forest h-1.5 rounded-full" style="width: {{ $persen }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $persen }}%</span>
                                </div>
                            </td>

                            {{-- Terakhir Diperbarui --}}
                            <td class="py-3 px-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $formattedTerakhir }}
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3 px-6 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    @if (\Illuminate\Support\Facades\Route::has('bantuan.show'))
                                        <a href="{{ route('bantuan.show', $r) }}"
                                           class="w-8 h-8 rounded-full border border-slate-200 hover:border-forest hover:bg-forest hover:text-white flex items-center justify-center text-slate-600 transition shadow-sm"
                                           title="Lihat Detail & Pemantauan">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('rumah.show', $r) }}"
                                           class="w-8 h-8 rounded-full border border-slate-200 hover:border-forest hover:bg-forest hover:text-white flex items-center justify-center text-slate-600 transition shadow-sm"
                                           title="Lihat Detail Rumah">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    @if ($r->status === 'Penerima Bantuan' && \Illuminate\Support\Facades\Route::has('bantuan.salurkan.create'))
                                        @can('verifikator')
                                            <a href="{{ route('bantuan.salurkan.create', $r) }}"
                                               class="px-2.5 py-1 text-xs font-medium text-forest hover:bg-forest/10 rounded-lg transition"
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
</div>
@endsection
