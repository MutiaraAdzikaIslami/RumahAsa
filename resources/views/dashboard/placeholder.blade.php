@extends('layouts.app')

@php
    $today = now();
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$today->dayOfWeek];
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggalStr = $hari . ', ' . $today->day . ' ' . $bulan[$today->month] . ' ' . $today->year;

    $roleKode = $user->role?->kode;
@endphp

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- HEADER SAMBUTAN SESUAI MOCKUP --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-forest/10 border border-forest/20 flex items-center justify-center text-forest flex-shrink-0">
                @if ($roleKode === 'surveyor')
                    <i data-lucide="clipboard-list" class="w-7 h-7"></i>
                @elseif ($roleKode === 'verifikator')
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                @elseif ($roleKode === 'pimpinan')
                    <i data-lucide="bar-chart-3" class="w-7 h-7"></i>
                @else
                    <i data-lucide="shield" class="w-7 h-7"></i>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">{{ $tanggalStr }}</p>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-0.5">
                    Selamat Datang, {{ $user->nama }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if ($roleKode === 'surveyor')
                        Kelola pendataan rumah dan pantau progres renovasi di wilayah tugas Anda (<strong class="text-slate-700">{{ $user->wilayah?->namaWilayahTugasSidebar() ?? 'Semua Wilayah' }}</strong>).
                    @elseif ($roleKode === 'verifikator')
                        Petugas Verifikasi &middot; Menilai kelayakan rumah warga &amp; pemantauan renovasi
                    @elseif ($roleKode === 'admin')
                        Administrator Sistem &middot; Hak kelola pengguna, wilayah, audit log, dan data rumah
                    @else
                        Pimpinan &middot; Pemantauan rekapitulasi bantuan dan progres renovasi RTLH
                    @endif
                </p>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-xs text-slate-600 self-start sm:self-auto">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-medium text-slate-700">Sistem Berjalan Normal</span>
            <span class="text-slate-400">&middot;</span>
            <span>{{ $today->format('H.i') }} WIB</span>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- 1. KONTEN KHUSUS SURVEYOR (SESUAI MOCKUP HALAMAN 6)      --}}
    {{-- ======================================================== --}}
    @if ($roleKode === 'surveyor')
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

            $statusAlurCounts = [
                'Menunggu Disurvei' => 0,
                'Draf'              => 0,
                'Diajukan'          => 0,
                'Perlu Perbaikan'   => 0,
                'Layak Huni'        => 0,
                'Penerima Bantuan'  => 0,
                'Dalam Renovasi'    => 0,
                'Selesai'           => 0,
            ];
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

            $daftarTugas = (clone $surveyorRumahQuery)
                ->latest('updated_at')
                ->limit(6)
                ->get();

            $daftarProgresRenovasi = (clone $surveyorRumahQuery)
                ->whereIn('status', ['Dalam Renovasi', 'Penerima Bantuan', 'Selesai'])
                ->latest('updated_at')
                ->limit(4)
                ->get();
        @endphp

        {{-- Banner Informasi Keputusan Manual (Sesuai Mockup) --}}
        <div x-data="{ showBanner: true }" x-show="showBanner" class="bg-emerald-50/90 border border-emerald-200/80 rounded-2xl px-5 py-3.5 flex items-center justify-between gap-4">
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

        {{-- 8 Kartu Status Alur Lengkap (Sesuai Draf Rancangan Terkini) --}}
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

        {{-- Grid 2 Kolom: Data Rumah Terbaru (Kiri) & Kartu Progres Renovasi (Kanan) Sesuai Mockup --}}
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

            {{-- Kolom Kanan (1/3): Kartu Progres Renovasi per Kepala Keluarga (Sesuai Mockup) --}}
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

    {{-- ======================================================== --}}
    {{-- 2. KONTEN KHUSUS VERIFIKATOR                             --}}
    {{-- ======================================================== --}}
    @elseif ($roleKode === 'verifikator')
        @php
            $antreanDiajukan = \App\Models\RumahWarga::where('status', 'Diajukan')->count();
            $penerimaCount   = \App\Models\RumahWarga::where('status', 'Penerima Bantuan')->count();
            $renovasiCount   = \App\Models\RumahWarga::where('status', 'Dalam Renovasi')->count();
            $selesaiCount    = \App\Models\RumahWarga::where('status', 'Selesai')->count();

            $antreanList = \App\Models\RumahWarga::with(['wilayah', 'surveyor', 'survei.komponenSurvei.foto'])
                ->where('status', 'Diajukan')
                ->latest('updated_at')
                ->limit(6)
                ->get();
        @endphp

        {{-- 4 Kartu Ringkasan Verifikator --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('verifikator.survei.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden group">
                @if ($antreanDiajukan > 0)
                    <span class="absolute top-3 right-3 bg-amber-500 text-slate-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                        {{ $antreanDiajukan }} MENUNGGU
                    </span>
                @endif
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                    <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Antrean Review Survei</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $antreanDiajukan }}</p>
                <p class="text-[11px] text-amber-600 font-medium mt-1">Siap dinilai &rarr;</p>
            </a>

            <a href="{{ route('bantuan.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3">
                    <i data-lucide="hand-heart" class="w-6 h-6"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Penerima Bantuan</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $penerimaCount }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Menunggu penyaluran dana</p>
            </a>

            <a href="{{ route('bantuan.index', ['status' => 'Dalam Renovasi']) }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                    <i data-lucide="hammer" class="w-6 h-6"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dalam Renovasi</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $renovasiCount }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Pemantauan fisik berjalan</p>
            </a>

            <a href="{{ route('bantuan.index', ['status' => 'Selesai']) }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Renovasi Selesai</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $selesaiCount }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Hunian layak huni tercapai</p>
            </a>
        </div>

        {{-- Tabel Antrean Pengajuan Survei Siap Review --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Antrean Survei Masuk (Menunggu Verifikasi)</h2>
                    <p class="text-xs text-slate-500">Pengajuan survei lapangan dari Surveyor yang siap diperiksa kelengkapannya dan ditetapkan statusnya.</p>
                </div>
                <a href="{{ route('verifikator.survei.index') }}" class="text-xs font-semibold text-forest hover:underline flex items-center gap-1">
                    Buka Halaman Review Survei &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Kepala Keluarga</th>
                            <th class="py-3 px-4">Wilayah</th>
                            <th class="py-3 px-4">Surveyor</th>
                            <th class="py-3 px-4 text-center">Foto Bukti</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($antreanList as $item)
                            @php
                                $s = $item->survei()->first();
                                $fotoTotal = $s ? $s->komponenSurvei->sum(fn ($k) => $k->foto->count()) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-medium text-slate-900">{{ $item->kode_registrasi }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $item->nama_kepala_keluarga ?? '-' }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $item->wilayahSingkat() }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $item->surveyor?->nama ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                        {{ $fotoTotal }} Foto
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('verifikator.survei.show', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg text-xs shadow-sm">
                                        <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                                        Review Survei
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada survei yang menunggu peninjauan saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ======================================================== --}}
    {{-- 3. KONTEN KHUSUS ADMIN                                   --}}
    {{-- ======================================================== --}}
    @elseif ($roleKode === 'admin')
        @php
            $totalUser      = \App\Models\User::count();
            $userAktif      = \App\Models\User::where('is_aktif', true)->count();
            $totalSurveyor  = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'surveyor'))->count();
            $totalVerif     = \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'verifikator'))->count();
            $totalRumah     = \App\Models\RumahWarga::count();
            $totalWilayah   = \App\Models\Wilayah::count();

            $allAdminRumah = \App\Models\RumahWarga::with('survei.komponenSurvei')->get();
            $adminCounts = array_fill_keys(\App\Models\RumahWarga::STATUS_ALUR_LIST, 0);
            foreach ($allAdminRumah as $rmAdm) {
                $stA = $rmAdm->statusAlur();
                if (isset($adminCounts[$stA])) {
                    $adminCounts[$stA]++;
                }
            }
            $statuses = [];
            foreach (\App\Models\RumahWarga::STATUS_ALUR_LIST as $stKey) {
                $statuses[] = [
                    'label' => $stKey,
                    'value' => $adminCounts[$stKey] ?? 0,
                    'color' => \App\Models\RumahWarga::STATUS_DOT_COLORS[$stKey] ?? '#64748B',
                ];
            }
            $totalStatusRumah = array_sum(array_column($statuses, 'value'));
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-forest/10 text-forest flex items-center justify-center">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pengguna</p>
                        <p class="text-2xl font-extrabold text-slate-900">{{ $totalUser }}</p>
                        <p class="text-xs text-slate-400">{{ $userAktif }} Akun Aktif</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="user-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Surveyor &amp; Verif</p>
                        <p class="text-2xl font-extrabold text-slate-900">{{ $totalSurveyor }} / {{ $totalVerif }}</p>
                        <p class="text-xs text-slate-400">Petugas Operasional</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="home" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Rumah</p>
                        <p class="text-2xl font-extrabold text-slate-900">{{ $totalRumah }}</p>
                        <p class="text-xs text-slate-400">Rumah Terdata</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Master Wilayah</p>
                        <p class="text-2xl font-extrabold text-slate-900">{{ number_format($totalWilayah) }}</p>
                        <p class="text-xs text-slate-400">Data Kemendagri</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribusi Status & Akses Cepat Admin --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-900">Distribusi Status Rumah Warga</h2>
                <div class="space-y-3">
                    @foreach ($statuses as $s)
                        @php $pct = $totalStatusRumah > 0 ? round(($s['value'] / $totalStatusRumah) * 100, 1) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-600 font-medium">{{ $s['label'] }}</span>
                                <span class="text-slate-900 font-bold">{{ $s['value'] }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="background-color: {{ $s['color'] }}; width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-900">Pintasan Cepat Administrator</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('rumah.create') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-forest/40 hover:bg-slate-50/80 transition flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Input Data Rumah</p>
                            <p class="text-[11px] text-slate-400">Tambah data sasaran</p>
                        </div>
                    </a>

                    <a href="{{ route('users.view') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-forest/40 hover:bg-slate-50/80 transition flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Kelola Pengguna</p>
                            <p class="text-[11px] text-slate-400">Role &amp; wilayah tugas</p>
                        </div>
                    </a>

                    <a href="{{ route('wilayah.view') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-forest/40 hover:bg-slate-50/80 transition flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="map" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Data Wilayah</p>
                            <p class="text-[11px] text-slate-400">Hierarki Kemendagri</p>
                        </div>
                    </a>

                    <a href="{{ route('audit.view') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-forest/40 hover:bg-slate-50/80 transition flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Audit Log</p>
                            <p class="text-[11px] text-slate-400">Jejak audit sistem</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    {{-- ======================================================== --}}
    {{-- 4. KONTEN KHUSUS PIMPINAN                                --}}
    {{-- ======================================================== --}}
    @else
        @php
            $totalDanaBantuan = \App\Models\PenerimaBantuan::sum('nominal');
            $totalPenerima    = \App\Models\PenerimaBantuan::count();
            $renovasiBerjalan = \App\Models\RumahWarga::where('status', 'Dalam Renovasi')->count();
            $renovasiSelesai  = \App\Models\RumahWarga::where('status', 'Selesai')->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Anggaran Disalurkan</p>
                <p class="text-2xl font-extrabold text-forest mt-1">Rp {{ number_format($totalDanaBantuan, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">APBD &amp; APBN</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Penerima Bantuan</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalPenerima }} KK</p>
                <p class="text-xs text-slate-400 mt-1">Target Penanganan</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sedang Renovasi</p>
                <p class="text-2xl font-extrabold text-purple-700 mt-1">{{ $renovasiBerjalan }} Rumah</p>
                <p class="text-xs text-slate-400 mt-1">Pekerjaan Lapangan</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Selesai 100%</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $renovasiSelesai }} Rumah</p>
                <p class="text-xs text-slate-400 mt-1">Layak Huni</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Rekapitulasi Laporan Program Bantuan</h2>
                <p class="text-xs text-slate-500">Tinjau seluruh daftar penerima dana per triwulan dan status renovasi fisik.</p>
            </div>
            <a href="{{ route('bantuan.rekap') }}" class="px-4 py-2 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl shadow-sm transition">
                Buka Rekap Dana &rarr;
            </a>
        </div>
    @endif

</div>
@endsection