@extends('layouts.app')

@section('title', 'Dashboard Verifikator')

@section('content')
<div class="space-y-6">
    {{-- Header Dashboard --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Verifikator</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Antrean aktif {{ $antreanAktifCount }} pengajuan
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Tinjau pengajuan dan pantau penerima bantuan.</p>
        </div>
    </div>

    {{-- Row 6 Kartu Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        {{-- Pengajuan Baru --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center mb-2.5">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $pengajuanBaruCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Pengajuan Baru</div>
        </div>

        {{-- Perlu Ditinjau --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center mb-2.5">
                <i data-lucide="eye" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $perluDitinjauCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Perlu Ditinjau</div>
        </div>

        {{-- Perlu Perbaikan --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mb-2.5">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $perluPerbaikanCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Perlu Perbaikan</div>
        </div>

        {{-- Tidak Layak / Layak Huni --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center mb-2.5">
                <i data-lucide="x" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $tidakLayakCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Layak Huni</div>
        </div>

        {{-- Dalam Renovasi --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-2.5">
                <i data-lucide="wrench" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $dalamRenovasiCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Dalam Renovasi</div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2.5">
                <i data-lucide="check" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $selesaiCount }}</div>
            <div class="text-[11px] text-slate-500 font-medium">Selesai</div>
        </div>
    </div>

    {{-- Banner Keputusan Manual --}}
    <div class="bg-emerald-50/60 border border-emerald-200/70 rounded-xl p-4 flex items-start gap-3">
        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i data-lucide="check" class="w-3.5 h-3.5"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-emerald-900">Keputusan verifikasi dilakukan secara manual</div>
            <div class="text-[11px] text-emerald-700 mt-0.5 leading-relaxed">
                RumahAsa tidak menghitung skor atau merekomendasikan kategori. Gunakan penilaian kelayakan pemerintah sebagai acuan internal.
            </div>
        </div>
    </div>

    {{-- Middle Section: Antrean Verifikasi & Target/Aktivitas --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Pengajuan Menunggu Verifikasi --}}
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pengajuan Menunggu Verifikasi</h2>
                    <p class="text-xs text-slate-500">Urut berdasarkan tanggal pengajuan terbaru</p>
                </div>
                <a href="{{ route('verifikator.survei.index') }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 flex items-center gap-1 transition">
                    Lihat semua
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if ($pengajuanMenunggu->isEmpty())
                <div class="py-12 text-center text-slate-400 text-xs">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                    Belum ada pengajuan yang menunggu verifikasi.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider text-[10px]">
                                <th class="py-3 font-semibold">Nama Pemilik</th>
                                <th class="py-3 font-semibold">Wilayah</th>
                                <th class="py-3 font-semibold">Tanggal Pengajuan</th>
                                <th class="py-3 font-semibold text-center">Foto</th>
                                <th class="py-3 font-semibold">Status</th>
                                <th class="py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($pengajuanMenunggu as $item)
                                @php
                                    $sVerif = $item->survei->first();
                                    $fotoCountVerif = $sVerif && $sVerif->relationLoaded('komponenSurvei')
                                        ? $sVerif->komponenSurvei->sum(fn ($k) => $k->foto->count())
                                        : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 pr-3">
                                        <div class="font-semibold text-slate-900">{{ $item->nama_kepala_keluarga ?: 'Warga' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $item->kode_registrasi }}</div>
                                    </td>
                                    <td class="py-3.5 pr-3 text-slate-600">
                                        {{ $item->wilayahSingkat() }}
                                    </td>
                                    <td class="py-3.5 pr-3 text-slate-500">
                                        {{ $item->updated_at ? $item->updated_at->format('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3.5 pr-3 text-center text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                            {{ $fotoCountVerif }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $item->badgeStatusClass($item->status) }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <a href="{{ route('verifikator.survei.show', $item) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-forest text-white hover:bg-forest-light transition">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Right Column: Target Hari ini & Aktivitas Terbaru --}}
        <div class="lg:col-span-4 space-y-4">
            {{-- Target Hari Ini --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Target Hari Ini</div>
                        <div class="text-base font-bold text-slate-900">{{ $targetSelesai }} dari {{ $targetTotal }}</div>
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                    <div class="bg-emerald-700 h-2 rounded-full" style="width: {{ $targetTotal > 0 ? round(($targetSelesai / $targetTotal) * 100) : 0 }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] text-slate-400 font-medium">
                    <span>{{ max(0, $targetTotal - $targetSelesai) }} pengajuan tersisa</span>
                    <span>{{ $targetTotal > 0 ? round(($targetSelesai / $targetTotal) * 100) : 0 }}%</span>
                </div>
            </div>

            {{-- Aktivitas Terbaru --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Aktivitas Terbaru</div>
                @if ($aktivitasTerbaru->isEmpty())
                    <div class="text-xs text-slate-400 italic py-3">Belum ada aktivitas baru.</div>
                @else
                    <div class="space-y-3.5">
                        @foreach ($aktivitasTerbaru as $act)
                            <div class="flex items-start gap-2.5 text-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 mt-1.5 flex-shrink-0"></span>
                                <div>
                                    <div class="font-semibold text-slate-800">{{ $act->catatan ?: ($act->status_dari . ' → ' . $act->status_ke) }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $act->rumah?->nama_kepala_keluarga ?: 'Warga' }} • {{ $act->created_at ? $act->created_at->diffForHumans() : '-' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- FR-31: Heatmap Status Rumah Component --}}
    @include('dashboard.components.heatmap', [
        'role' => 'verifikator',
        'subtitle' => 'Sebaran rumah menurut status: sedang disurvei, sedang renovasi, dan selesai.',
        'wilayahList' => $wilayahList,
        'sumberProgramList' => $sumberProgramList,
    ])

    {{-- Bottom Section: Progres Renovasi Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Progres Renovasi Terbaru</h2>
                <p class="text-xs text-slate-500">Pembaruan lapangan dari penerima bantuan</p>
            </div>
            <a href="{{ route('bantuan.index', ['mode' => 'progres']) }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 flex items-center gap-1 transition">
                Lihat semua
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if ($progresTerbaru->isEmpty())
            <div class="py-8 text-center text-slate-400 text-xs">
                Belum ada data renovasi yang sedang berjalan.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($progresTerbaru as $prog)
                    @php
                        $pctProg = $prog->penerimaBantuan ? $prog->penerimaBantuan->progresTerakhir() : ($prog->status === 'Selesai' ? 100 : 0);
                    @endphp
                    <div class="border border-slate-100 rounded-xl p-4 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center">
                                    <i data-lucide="home" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 text-xs">{{ $prog->nama_kepala_keluarga }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $prog->wilayahSingkat() }}</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-900">{{ $pctProg }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden mb-2">
                            <div class="bg-emerald-800 h-1.5 rounded-full" style="width: {{ $pctProg }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>{{ $prog->terakhirDiperbaruiLabel() }}</span>
                            <a href="{{ route('bantuan.show', $prog) }}" class="font-semibold text-emerald-800 hover:underline">Pantau &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
