@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $wilayahPath = $user->wilayah?->path;
    $surveyorRumahQuery = \App\Models\RumahWarga::with(['wilayah.parent.parent.parent', 'survei.komponenSurvei', 'penerimaBantuan.progres']);
    if ($wilayahPath) {
        $surveyorRumahQuery->where(function ($q) use ($wilayahPath, $user) {
            $q->where('surveyor_id', $user->id)
              ->orWhereHas('wilayah', function ($qw) use ($wilayahPath) {
                  $qw->where('path', $wilayahPath)->orWhere('path', 'like', $wilayahPath . '/%');
              });
        });
    }

    $allSurveyorRumah = (clone $surveyorRumahQuery)->get();

    $statusAlurCounts = array_fill_keys(\App\Models\RumahWarga::STATUS_ALUR_LIST, 0);
    foreach ($allSurveyorRumah as $rm) {
        $stAlur = $rm->statusAlur();
        if (isset($statusAlurCounts[$stAlur])) {
            $statusAlurCounts[$stAlur]++;
        }
    }

    $statusIcons = [
        'Menunggu Disurvei' => 'clock',
        'Draf'              => 'file-text',
        'Diajukan'          => 'arrow-right',
        'Perlu Perbaikan'   => 'shield-alert',
        'Layak Huni'        => 'home',
        'Penerima Bantuan'  => 'hand-heart',
        'Dalam Renovasi'    => 'building-2',
        'Selesai'           => 'check-circle-2',
    ];

    $daftarTugas = (clone $surveyorRumahQuery)->latest('updated_at')->limit(6)->get();
    $daftarProgresRenovasi = (clone $surveyorRumahQuery)
        ->whereIn('status', ['Dalam Renovasi', 'Penerima Bantuan', 'Selesai'])
        ->latest('updated_at')
        ->limit(4)
        ->get();
@endphp

<div class="space-y-6">
    {{-- Header Dashboard Sesuai Mockup Page 6 --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="text-xs font-extrabold text-amber-600 uppercase tracking-wider mb-1">
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">Selamat datang, {{ $user->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pendataan rumah dan pantau progres renovasi di wilayah tugas Anda (<strong class="text-slate-700">{{ $user->wilayah?->namaWilayahTugasSidebar() ?? 'Semua Wilayah' }}</strong>).
            </p>
        </div>
    </div>

    {{-- Banner Keputusan Manual --}}
    <div x-data="{ showBanner: true }" x-show="showBanner" class="bg-emerald-50/80 border border-emerald-200/80 rounded-2xl px-5 py-3.5 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-forest flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-900">Keputusan kategori dilakukan secara manual</p>
                <p class="text-[11px] text-slate-600 mt-0.5">RumahAsa membantu pencatatan dan verifikasi data. Penetapan kelayakan tetap dilakukan Verifikator berdasarkan pedoman kebijakan pemerintah.</p>
            </div>
        </div>
        <button type="button" @click="showBanner = false" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- 8 Kartu Status Alur Lengkap --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        @foreach ($statusAlurCounts as $stLabel => $stCount)
            <a href="{{ route('rumah.index', ['status' => $stLabel]) }}" class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs hover:shadow-md hover:border-forest/40 transition flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs {{ \App\Models\RumahWarga::STATUS_BADGE_CLASSES[$stLabel] ?? 'bg-slate-100 text-slate-700' }}">
                        <i data-lucide="{{ $statusIcons[$stLabel] ?? 'circle' }}" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <p class="text-[11px] font-medium text-slate-500 leading-tight">{{ $stLabel }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stCount }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Grid 2 Kolom: Data Rumah Terbaru (Kiri) & Kartu Progres Renovasi (Kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Kolom Kiri (2/3): Data Rumah Terbaru --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Data Rumah Terbaru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rumah di wilayah tugas Anda dan tindakan yang diperlukan.</p>
                </div>
                <a href="{{ route('rumah.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                    Lihat semua
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50/70 text-slate-400 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Nama Pemilik</th>
                            <th class="py-3 px-4">Alamat</th>
                            <th class="py-3 px-4">Wilayah</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Terakhir Diperbarui</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($daftarTugas as $item)
                            @php
                                $stAlur = $item->statusAlur();
                                $progresPct = $item->penerimaBantuan?->progresTerakhir() ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900">{{ $item->nama_kepala_keluarga ?? '-' }}</p>
                                    <p class="font-mono text-[10px] text-slate-400 mt-0.5">{{ $item->kode_registrasi }}</p>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 max-w-[180px]">
                                    {{ $item->alamatLengkap() }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $item->wilayahSingkat() }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-semibold text-[11px] {{ $item->badgeStatusClass($stAlur) }}">
                                        {{ $stAlur }}
                                    </span>
                                    @if ($stAlur === 'Dalam Renovasi' && $progresPct === 0)
                                        <p class="text-[10px] text-amber-600 mt-0.5">Belum ada progres</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $item->terakhirDiperbaruiLabel() }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if ($stAlur === 'Menunggu Disurvei')
                                        <a href="{{ route('survei.create', $item) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                            Mulai Survei
                                        </a>
                                    @elseif ($stAlur === 'Draf')
                                        <a href="{{ route('survei.create', $item) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                            Lanjutkan
                                        </a>
                                    @elseif ($stAlur === 'Perlu Perbaikan')
                                        <a href="{{ route('survei.create', $item) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                            Perbaiki
                                        </a>
                                    @elseif ($stAlur === 'Dalam Renovasi')
                                        <a href="{{ route('bantuan.progres.index', $item) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 bg-forest hover:bg-forest-light text-white font-semibold rounded-xl text-xs shadow-xs">
                                            Isi Progres
                                        </a>
                                    @else
                                        <a href="{{ route('rumah.show', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-xs">
                                            Lihat &rarr;
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada rumah di wilayah tugas Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom Kanan (1/3): Kartu Progres Renovasi per Kepala Keluarga --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Progres Renovasi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rumah yang sedang ditangani</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-forest flex items-center justify-center">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($daftarProgresRenovasi as $rmProg)
                    @php
                        $bantuanProg = $rmProg->penerimaBantuan;
                        $pctTerakhir = $bantuanProg ? $bantuanProg->progresTerakhir() : 0;
                        $itemProgresTerbaru = $bantuanProg?->progres?->sortByDesc('tanggal_progres')->first();
                        if ($itemProgresTerbaru && $itemProgresTerbaru->updated_at) {
                            $dtProg = $itemProgresTerbaru->updated_at->timezone('Asia/Jakarta');
                            $labelUpdateProg = $dtProg->isToday()
                                ? 'Diperbarui hari ini'
                                : ($dtProg->isYesterday() ? 'Diperbarui kemarin' : 'Diperbarui ' . $rmProg->terakhirDiperbaruiLabel());
                        } else {
                            $labelUpdateProg = $rmProg->updated_at?->isToday()
                                ? 'Diperbarui hari ini'
                                : 'Diperbarui ' . $rmProg->terakhirDiperbaruiLabel();
                        }
                    @endphp
                    <a href="{{ route('bantuan.progres.index', $rmProg) }}" class="block p-3.5 rounded-xl border border-slate-200/80 hover:border-forest/40 hover:bg-slate-50/70 transition space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $rmProg->nama_kepala_keluarga ?? '-' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $rmProg->wilayahSingkat() }}</p>
                            </div>
                            <span class="text-xs font-extrabold text-forest">{{ $pctTerakhir }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-forest rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $pctTerakhir)) }}%;"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>{{ $labelUpdateProg }}</span>
                            <span class="font-semibold text-forest">{{ $rmProg->status }}</span>
                        </div>
                    </a>
                @empty
                    <div class="p-6 text-center rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-400">
                        Belum ada rumah dalam tahap renovasi di wilayah tugas Anda.
                    </div>
                @endforelse
            </div>

            <a href="{{ route('rumah.index', ['status' => 'Dalam Renovasi']) }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-bold text-slate-700 transition">
                Lihat semua progres &rarr;
            </a>
        </div>
    </div>

    {{-- FR-31: Komponen Heatmap Status Rumah (Sesuai Mockup Page 6) --}}
    @include('dashboard.components.heatmap', [
        'role' => 'surveyor',
        'subtitle' => 'Sebaran rumah di wilayah tugas Anda.',
        'wilayahList' => $wilayahList,
        'sumberProgramList' => $sumberProgramList,
        'surveyorWilayah' => $wilayah,
    ])
</div>
@endsection
