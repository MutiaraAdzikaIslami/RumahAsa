@extends('layouts.app')

@section('title', 'Rekap Dana')

@section('content')
@php
    $triwulanLabels = [
        '1' => 'Triwulan 1 (Jan–Mar)',
        '2' => 'Triwulan 2 (Apr–Jun)',
        '3' => 'Triwulan 3 (Jul–Sep)',
        '4' => 'Triwulan 4 (Okt–Des)',
    ];
    $labelPeriodeTotal = $triwulan && isset($triwulanLabels[(string) $triwulan])
        ? 'Total Triwulan ' . $triwulan . ' ' . $tahun
        : 'Total Tahun ' . $tahun;
@endphp

<div x-data="{ openExportModal: false, exportFormat: 'excel' }" @keydown.escape.window="openExportModal = false" class="space-y-6">

    {{-- Header Halaman Sesuai Mockup Page 3 --}}
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900">Rekap Dana Bantuan</h1>
        <p class="text-sm text-slate-500 mt-1">Dana yang dicatat disalurkan per triwulan.</p>
    </div>

    {{-- Filter Bar Card Sesuai Mockup Page 3 --}}
    <form method="GET" action="{{ route('bantuan.rekap') }}" class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            {{-- Tahun --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun</label>
                <select name="tahun" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest cursor-pointer">
                    @for ($y = now()->year; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ (int) $tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Triwulan --}}
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Triwulan</label>
                <select name="triwulan" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest cursor-pointer">
                    <option value="">Semua Triwulan</option>
                    @foreach ($triwulanLabels as $twKey => $twText)
                        <option value="{{ $twKey }}" {{ (string) $triwulan === (string) $twKey ? 'selected' : '' }}>{{ $twText }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Wilayah --}}
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Wilayah</label>
                <select name="wilayah_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest cursor-pointer">
                    <option value="">Semua Wilayah</option>
                    @foreach ($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ (string) $wilayahId === (string) $w->id ? 'selected' : '' }}>
                            {{ ucfirst($w->tingkat) }} {{ $w->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sumber Program --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Sumber Program</label>
                <select name="sumber_program" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest cursor-pointer">
                    <option value="">Semua</option>
                    @foreach (['APBN', 'APBD', 'CSR', 'Swadaya'] as $sp)
                        <option value="{{ $sp }}" {{ $sumberProgram === $sp ? 'selected' : '' }}>{{ $sp }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Ekspor --}}
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="button" @click="openExportModal = true" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold shadow-xs transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor</span>
                </button>
            </div>
        </div>
    </form>

    {{-- Kartu 1: Ringkasan per Sumber Program (Sesuai Mockup Page 3) --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-base font-extrabold text-slate-900">Ringkasan per Sumber Program</h2>
            <p class="text-xs text-slate-500 mt-0.5">Akumulasi sesuai filter yang dipilih</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50/70 text-slate-400 font-bold border-b border-slate-100 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-6">Sumber Program</th>
                        <th class="py-3.5 px-4">Total Dana Tersalur</th>
                        <th class="py-3.5 px-4">Jumlah Rumah</th>
                        <th class="py-3.5 px-6">Rata-rata per Rumah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rekapSumber as $sumber => $data)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ \App\Models\PenerimaBantuan::badgeSumberClass($sumber) }}">
                                    {{ $sumber }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-extrabold text-slate-900 text-sm">
                                Rp{{ number_format($data['nominal'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-slate-600 font-medium">
                                {{ $data['jumlah'] }} rumah
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                Rp{{ number_format($data['rata_rata'] ?? ($data['jumlah'] > 0 ? $data['nominal'] / $data['jumlah'] : 0), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data penyaluran pada periode filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-emerald-50/70 border-t border-emerald-100 font-extrabold text-slate-900 text-xs">
                    <tr>
                        <td class="py-4 px-6">{{ $labelPeriodeTotal }}</td>
                        <td class="py-4 px-4 text-sm text-forest">Rp{{ number_format($totalNominal, 0, ',', '.') }}</td>
                        <td class="py-4 px-4">{{ $totalPenerima }} rumah</td>
                        <td class="py-4 px-6">Rp{{ $totalPenerima > 0 ? number_format($totalNominal / $totalPenerima, 0, ',', '.') : 0 }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Kartu 2: Rumah Penerima Dana (Sesuai Mockup Page 3 + Golden Rule Legenda Status) --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Rumah Penerima Dana</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar rumah penerima sesuai filter aktif</p>
            </div>

            <form method="GET" action="{{ route('bantuan.rekap') }}" class="relative w-full sm:w-80">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                @if ($triwulan) <input type="hidden" name="triwulan" value="{{ $triwulan }}"> @endif
                @if ($wilayahId) <input type="hidden" name="wilayah_id" value="{{ $wilayahId }}"> @endif
                @if ($sumberProgram) <input type="hidden" name="sumber_program" value="{{ $sumberProgram }}"> @endif

                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? request('search') }}"
                    placeholder="Cari kode rumah, nama kepala keluarga, atau NIK..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/70 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-forest/20 focus:border-forest"
                >
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50/70 text-slate-400 font-bold border-b border-slate-100 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-6">Kode</th>
                        <th class="py-3.5 px-4">Nama Kepala Keluarga</th>
                        <th class="py-3.5 px-4">NIK</th>
                        <th class="py-3.5 px-4">Wilayah</th>
                        <th class="py-3.5 px-4">Jenis Bantuan</th>
                        <th class="py-3.5 px-4">Sumber Program</th>
                        <th class="py-3.5 px-4">Status Renovasi</th>
                        <th class="py-3.5 px-4">Nominal</th>
                        <th class="py-3.5 px-6">Tanggal Penyaluran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarBantuan as $b)
                        @php
                            $rm = $b->rumah;
                            $sumberVal = $b->sumber_program ?: 'APBD';
                            $tglBantuanStr = $b->tanggal_bantuan
                                ? \Carbon\Carbon::parse($b->tanggal_bantuan)->format('d M Y')
                                : '-';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                @if ($rm)
                                    <a href="{{ route('bantuan.show', $rm) }}" class="hover:text-forest hover:underline">{{ $rm->kode_registrasi }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-900">
                                {{ $rm?->nama_kepala_keluarga ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-mono text-slate-500">
                                {{ $rm && $rm->nik_enc ? $rm->nikTersamar() : '-' }}
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                {{ $rm ? $rm->wilayahSingkat() : '-' }}
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                {{ $b->jenis_bantuan }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ \App\Models\PenerimaBantuan::badgeSumberClass($sumberVal) }}">
                                    {{ $sumberVal }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if ($rm)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $rm->badgeStatusClass($rm->status) }}">
                                        {{ $rm->status }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-4 font-extrabold text-slate-900 whitespace-nowrap">
                                Rp{{ number_format($b->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                {{ $tglBantuanStr }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">Belum ada data rumah penerima dana untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Menampilkan {{ $daftarBantuan->count() > 0 ? '1–' . $daftarBantuan->count() : '0' }} dari {{ $daftarBantuan->count() }} data</span>
            <div class="flex items-center gap-1">
                <span class="w-7 h-7 rounded-lg bg-forest text-white font-bold flex items-center justify-center">1</span>
            </div>
        </div>
    </div>

    {{-- Modal Ekspor Rekap Dana Sesuai Mockup Page 1 --}}
    <div x-show="openExportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 backdrop-blur-xs p-4">
        <div @click.away="openExportModal = false" class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-7 shadow-2xl space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                        <i data-lucide="download" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Ekspor Rekap Dana</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih format file untuk mengunduh rekap sesuai filter yang digunakan.</p>
                    </div>
                </div>
                <button type="button" @click="openExportModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-2.5">
                <p class="text-xs font-extrabold text-slate-800">Ringkasan filter</p>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                        <span class="text-slate-400 block text-[11px]">Tahun</span>
                        <span class="font-bold text-slate-900 mt-0.5 block">{{ $tahun }}</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                        <span class="text-slate-400 block text-[11px]">Triwulan</span>
                        <span class="font-bold text-slate-900 mt-0.5 block">{{ $triwulan && isset($triwulanLabels[(string) $triwulan]) ? $triwulanLabels[(string) $triwulan] : 'Semua Triwulan' }}</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                        <span class="text-slate-400 block text-[11px]">Wilayah</span>
                        <span class="font-bold text-slate-900 mt-0.5 block">{{ $wilayahId ? ($wilayahList->firstWhere('id', $wilayahId)?->nama ?? 'Terpilih') : 'Semua Wilayah' }}</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                        <span class="text-slate-400 block text-[11px]">Sumber Program</span>
                        <span class="font-bold text-slate-900 mt-0.5 block">{{ $sumberProgram ?: 'Semua Sumber Program' }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button type="button"
                        @click="exportFormat = 'excel'"
                        :class="exportFormat === 'excel' ? 'border-forest bg-emerald-50/40 ring-1 ring-forest' : 'border-slate-200 hover:bg-slate-50'"
                        class="p-4 rounded-2xl border text-left transition flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 text-forest flex-shrink-0"></i>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900">Excel (.xlsx)</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Unduh rekap dalam format Excel.</p>
                        </div>
                    </div>
                    <span class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                          :class="exportFormat === 'excel' ? 'bg-forest text-white' : 'border border-slate-300'">
                        <i x-show="exportFormat === 'excel'" data-lucide="check" class="w-3 h-3"></i>
                    </span>
                </button>

                <button type="button"
                        @click="exportFormat = 'pdf'"
                        :class="exportFormat === 'pdf' ? 'border-forest bg-emerald-50/40 ring-1 ring-forest' : 'border-slate-200 hover:bg-slate-50'"
                        class="p-4 rounded-2xl border text-left transition flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-text" class="w-5 h-5 text-forest flex-shrink-0"></i>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900">PDF (.pdf)</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Unduh rekap dalam format PDF.</p>
                        </div>
                    </div>
                    <span class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                          :class="exportFormat === 'pdf' ? 'bg-forest text-white' : 'border border-slate-300'">
                        <i x-show="exportFormat === 'pdf'" data-lucide="check" class="w-3 h-3"></i>
                    </span>
                </button>
            </div>

            <div class="flex items-center gap-2.5 text-xs text-forest bg-emerald-50/80 border border-emerald-200/70 rounded-xl px-4 py-3">
                <i data-lucide="shield-check" class="w-4 h-4 flex-shrink-0"></i>
                <span>NIK pada hasil ekspor ditampilkan tersamar. Aktivitas ini tercatat di audit log.</span>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-1">
                <button type="button" @click="openExportModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="button" @click="window.print(); openExportModal = false" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold shadow-sm transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
