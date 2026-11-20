@extends('layouts.app')

@section('title', 'Rekap Dana Bantuan')

@section('content')
<div x-data="{ openExportModal: false, exportFormat: 'excel' }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-extrabold text-slate-900">Rekapitulasi Dana Bantuan</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-forest/10 text-forest border border-forest/20">
                    Tahun {{ $tahun }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Rekap dana bantuan per triwulan berdasarkan sumber program, jenis bantuan, dan wilayah.</p>
        </div>

        <button type="button" @click="openExportModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold shadow-sm transition">
            <i data-lucide="download" class="w-4 h-4"></i>
            Ekspor Rekap Dana
        </button>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('bantuan.rekap') }}" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Anggaran</label>
                <select name="tahun" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                    @for ($y = now()->year; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ (int) $tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Triwulan</label>
                <select name="triwulan" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                    <option value="">Semua Triwulan</option>
                    <option value="1" {{ (string) $triwulan === '1' ? 'selected' : '' }}>Triwulan 1 (Januari–Maret)</option>
                    <option value="2" {{ (string) $triwulan === '2' ? 'selected' : '' }}>Triwulan 2 (April–Juni)</option>
                    <option value="3" {{ (string) $triwulan === '3' ? 'selected' : '' }}>Triwulan 3 (Juli–September)</option>
                    <option value="4" {{ (string) $triwulan === '4' ? 'selected' : '' }}>Triwulan 4 (Oktober–Desember)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sumber Program</label>
                <select name="sumber_program" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                    <option value="">Semua Sumber Program</option>
                    @foreach (['APBD', 'APBN', 'CSR', 'Swadaya'] as $sp)
                        <option value="{{ $sp }}" {{ $sumberProgram === $sp ? 'selected' : '' }}>{{ $sp }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Wilayah</label>
                <select name="wilayah_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                    <option value="">Semua Wilayah</option>
                    @foreach ($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ (string) $wilayahId === (string) $w->id ? 'selected' : '' }}>
                            {{ ucfirst($w->tingkat) }} {{ $w->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold transition">
                    Terapkan
                </button>
                <a href="{{ route('bantuan.rekap') }}" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Kartu Ringkasan Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Dana Tersalurkan</p>
            <p class="text-2xl font-extrabold text-forest mt-1">Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Akumulasi sesuai filter aktif</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah Penerima Bantuan</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalPenerima }} KK</p>
            <p class="text-xs text-slate-400 mt-1">Rumah warga penerima dana</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-rata Bantuan / KK</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">
                Rp {{ $totalPenerima > 0 ? number_format($totalNominal / $totalPenerima, 0, ',', '.') : 0 }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Per kepala keluarga</p>
        </div>
    </div>

    {{-- Rekap per Sumber Program & Jenis Bantuan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Rekap Berdasarkan Sumber Program</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Sumber Program</th>
                            <th class="py-2.5 px-3 text-center">Jumlah KK</th>
                            <th class="py-2.5 px-3 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rekapSumber as $sumber => $data)
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $sumber }}</td>
                                <td class="py-2.5 px-3 text-center">{{ $data['jumlah'] }} KK</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-forest">Rp {{ number_format($data['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">Belum ada data penyaluran pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Rekap Berdasarkan Jenis Bantuan</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Jenis Bantuan</th>
                            <th class="py-2.5 px-3 text-center">Jumlah KK</th>
                            <th class="py-2.5 px-3 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rekapJenis as $jenis => $data)
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $jenis }}</td>
                                <td class="py-2.5 px-3 text-center">{{ $data['jumlah'] }} KK</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-forest">Rp {{ number_format($data['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">Belum ada data penyaluran pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Rincian Penyaluran --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">Rincian Penyaluran Dana Bantuan</h2>
            <p class="text-xs text-slate-500">Daftar detail penerima bantuan yang tercakup dalam filter rekapitulasi.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Kode Rumah</th>
                        <th class="py-3 px-4">Kepala Keluarga</th>
                        <th class="py-3 px-4">Wilayah</th>
                        <th class="py-3 px-4">Sumber / Jenis</th>
                        <th class="py-3 px-4">Nomor SK</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarBantuan as $b)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3 px-4 text-slate-600">{{ $b->tanggal_bantuan?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-3 px-4 font-mono font-medium text-slate-900">{{ $b->rumah?->kode_registrasi ?? '-' }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $b->rumah?->nama_kepala_keluarga ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $b->rumah?->wilayah?->nama ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full bg-forest/10 text-forest font-bold text-[10px]">{{ $b->sumber_program ?? 'APBD' }}</span>
                                <span class="text-slate-600 ml-1">{{ $b->jenis_bantuan }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $b->nomor_sk ?? '-' }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">Rp {{ number_format($b->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data penyaluran bantuan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ekspor Rekap Dana Sesuai Mockup Mutiara --}}
    <div x-show="openExportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div @click.away="openExportModal = false" class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-xl space-y-5">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Ekspor Rekap Dana</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih format file untuk mengunduh rekap sesuai filter yang digunakan.</p>
                </div>
                <button type="button" @click="openExportModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 space-y-2 text-xs">
                <p class="font-bold text-slate-700">Ringkasan filter</p>
                <div class="grid grid-cols-2 gap-2 text-slate-600">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tahun</span>
                        <span class="font-semibold text-slate-800">{{ $tahun }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Triwulan</span>
                        <span class="font-semibold text-slate-800">{{ $triwulan ? 'Triwulan ' . $triwulan : 'Semua Triwulan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Wilayah</span>
                        <span class="font-semibold text-slate-800">{{ $wilayahId ? ($wilayahList->firstWhere('id', $wilayahId)?->nama ?? 'Terpilih') : 'Semua Wilayah' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Sumber Program</span>
                        <span class="font-semibold text-slate-800">{{ $sumberProgram ?: 'Semua Sumber Program' }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="exportFormat = 'excel'" :class="exportFormat === 'excel' ? 'border-forest bg-forest/5 text-forest' : 'border-slate-200 text-slate-700'" class="p-3.5 rounded-xl border-2 text-left transition">
                    <p class="text-xs font-bold">Excel (.xlsx)</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Unduh tabel spreadsheet</p>
                </button>
                <button type="button" @click="exportFormat = 'pdf'" :class="exportFormat === 'pdf' ? 'border-forest bg-forest/5 text-forest' : 'border-slate-200 text-slate-700'" class="p-3.5 rounded-xl border-2 text-left transition">
                    <p class="text-xs font-bold">PDF (.pdf)</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Unduh rekap dalam format PDF</p>
                </button>
            </div>

            <p class="text-[11px] text-slate-500 bg-amber-50 border border-amber-200/80 rounded-lg px-3 py-2">
                NIK pada hasil ekspor ditampilkan tersamar. Aktivitas ini tercatat di audit log.
            </p>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="openExportModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="window.print(); openExportModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Ekspor
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
