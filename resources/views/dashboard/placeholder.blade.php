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
                        Petugas Lapangan &middot; Wilayah Penugasan: <strong class="text-slate-700">{{ $user->wilayah?->nama ?? 'Semua Wilayah' }}</strong>
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
    {{-- 1. KONTEN KHUSUS SURVEYOR                                --}}
    {{-- ======================================================== --}}
    @if ($roleKode === 'surveyor')
        @php
            $wilayahPath = $user->wilayah?->path;
            $surveyorRumahQuery = \App\Models\RumahWarga::query();
            if ($wilayahPath) {
                $surveyorRumahQuery->whereHas('wilayah', function ($q) use ($wilayahPath) {
                    $q->where('path', $wilayahPath)->orWhere('path', 'like', $wilayahPath . '/%');
                });
            }

            $tugasSurveiAktif = (clone $surveyorRumahQuery)->whereIn('status', ['Draf', 'Perlu Perbaikan'])->count();
            $surveiTerkirim   = (clone $surveyorRumahQuery)->where('status', 'Diajukan')->count();
            $dalamRenovasi    = (clone $surveyorRumahQuery)->where('status', 'Dalam Renovasi')->count();

            $daftarTugas = (clone $surveyorRumahQuery)->with(['wilayah', 'survei'])
                ->latest('updated_at')
                ->limit(6)
                ->get();
        @endphp

        {{-- Kartu Ringkasan Surveyor --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tugas Survei Aktif</p>
                    <p class="text-2xl font-extrabold text-slate-900">{{ $tugasSurveiAktif }}</p>
                    <p class="text-xs text-slate-400">Status Draf &amp; Perlu Perbaikan</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="hammer" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rumah Dalam Renovasi</p>
                    <p class="text-2xl font-extrabold text-slate-900">{{ $dalamRenovasi }}</p>
                    <p class="text-xs text-slate-400">Perlu input progres &amp; foto berkala</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="send" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Survei Terkirim</p>
                    <p class="text-2xl font-extrabold text-slate-900">{{ $surveiTerkirim }}</p>
                    <p class="text-xs text-slate-400">Menunggu tinjauan Verifikator</p>
                </div>
            </div>
        </div>

        {{-- Tabel Tugas Lapangan Surveyor --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Rumah Tugas Lapangan</h2>
                    <p class="text-xs text-slate-500">Rumah di wilayah tugas Anda yang membutuhkan survei atau pencatatan progres renovasi.</p>
                </div>
                <a href="{{ route('rumah.index') }}" class="text-xs font-semibold text-forest hover:underline flex items-center gap-1">
                    Lihat Semua Data Rumah &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Kepala Keluarga</th>
                            <th class="py-3 px-4">Wilayah</th>
                            <th class="py-3 px-4">Status Rumah</th>
                            <th class="py-3 px-4 text-right">Aksi Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($daftarTugas as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-medium text-slate-900">{{ $item->kode_registrasi }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $item->nama_kepala_keluarga ?? '-' }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $item->wilayah?->nama ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium text-[11px]
                                        @if ($item->status === 'Draf') bg-slate-100 text-slate-700
                                        @elseif ($item->status === 'Diajukan') bg-blue-100 text-blue-700
                                        @elseif ($item->status === 'Perlu Perbaikan') bg-amber-100 text-amber-800
                                        @elseif ($item->status === 'Dalam Renovasi') bg-purple-100 text-purple-700
                                        @elseif ($item->status === 'Selesai') bg-emerald-100 text-emerald-700
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1.5">
                                    @if ($item->bisaDiedit())
                                        <a href="{{ route('survei.create', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg text-xs shadow-sm">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            Isi Survei
                                        </a>
                                    @elseif ($item->status === 'Dalam Renovasi')
                                        <a href="{{ route('bantuan.progres.index', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg text-xs shadow-sm">
                                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                            Input Progres
                                        </a>
                                    @endif
                                    <a href="{{ route('rumah.show', $item) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-xs">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada rumah di wilayah tugas Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                                <td class="py-3 px-4 text-slate-600">{{ $item->wilayah?->nama ?? '-' }}</td>
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

            $statuses = [
                ['label' => 'Draf Baru', 'value' => \App\Models\RumahWarga::where('status', 'Draf')->count(), 'color' => '#94A3B8'],
                ['label' => 'Diajukan', 'value' => \App\Models\RumahWarga::where('status', 'Diajukan')->count(), 'color' => '#3B82F6'],
                ['label' => 'Perlu Perbaikan', 'value' => \App\Models\RumahWarga::where('status', 'Perlu Perbaikan')->count(), 'color' => '#F59E0B'],
                ['label' => 'Penerima Bantuan', 'value' => \App\Models\RumahWarga::where('status', 'Penerima Bantuan')->count(), 'color' => '#EF4444'],
                ['label' => 'Dalam Renovasi', 'value' => \App\Models\RumahWarga::where('status', 'Dalam Renovasi')->count(), 'color' => '#8B5CF6'],
                ['label' => 'Selesai', 'value' => \App\Models\RumahWarga::where('status', 'Selesai')->count(), 'color' => '#10B981'],
            ];
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

    {{-- ======================================================== --}}
    {{-- PETA SEBARAN & HEATMAP RENOVASI RTLH (FR-31)              --}}
    {{-- ======================================================== --}}
    @if (in_array($roleKode, ['verifikator', 'surveyor', 'pimpinan']))
        @php
            $mapQuery = \App\Models\RumahWarga::with('wilayah')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($roleKode === 'surveyor' && $user->wilayah?->path) {
                $wPath = $user->wilayah->path;
                $mapQuery->whereHas('wilayah', function ($q) use ($wPath) {
                    $q->where('path', $wPath)->orWhere('path', 'like', $wPath . '/%');
                });
            }

            $mapPoints = $mapQuery->get()->map(function ($r) {
                $layer = match ($r->status) {
                    'Dalam Renovasi' => 'renovasi',
                    'Selesai'        => 'selesai',
                    'Penerima Bantuan' => 'penerima',
                    default          => 'survei',
                };
                return [
                    'lat'    => (float) $r->latitude,
                    'lng'    => (float) $r->longitude,
                    'kode'   => $r->kode_registrasi,
                    'status' => $r->status,
                    'wilayah'=> $r->wilayah?->nama ?? '-',
                    'layer'  => $layer,
                ];
            })->values();
        @endphp

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900">Peta Sebaran &amp; Heatmap Renovasi RTLH</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $roleKode === 'surveyor' ? 'Wilayah Tugas' : 'Seluruh Wilayah' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Visualisasi kepadatan rumah berdasarkan status penanganan (tanpa menampilkan data pribadi).</p>
                </div>

                <div class="flex flex-wrap items-center gap-1.5" id="heatmapFilterBtns">
                    <button type="button" data-layer="all" class="hm-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-forest text-white">Semua Lapisan</button>
                    <button type="button" data-layer="survei" class="hm-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Sedang Disurvei</button>
                    <button type="button" data-layer="renovasi" class="hm-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Sedang Renovasi</button>
                    <button type="button" data-layer="selesai" class="hm-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Selesai Renovasi</button>
                </div>
            </div>

            <div id="rtlhHeatmapContainer" class="w-full h-80 z-0"></div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Sedang Disurvei</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span> Dalam Renovasi</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Selesai Renovasi</span>
                </div>
                <span>Titik terdata dengan GPS: <strong>{{ $mapPoints->count() }}</strong> rumah</span>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('rtlhHeatmapContainer');
                if (!container || typeof L === 'undefined') return;

                const rawPoints = @json($mapPoints);
                const fallbackPoints = [
                    { lat: -7.4478, lng: 112.7183, kode: 'Area Sidoarjo Kota', status: 'Dalam Renovasi', wilayah: 'Sidoarjo', layer: 'renovasi' },
                    { lat: -7.4385, lng: 112.6250, kode: 'Area Wonoayu', status: 'Sedang Disurvei', wilayah: 'Wonoayu', layer: 'survei' },
                    { lat: -7.3500, lng: 112.7200, kode: 'Area Waru', status: 'Selesai', wilayah: 'Waru', layer: 'selesai' }
                ];
                const points = rawPoints.length > 0 ? rawPoints : fallbackPoints;

                const centerLat = points[0]?.lat || -7.4478;
                const centerLng = points[0]?.lng || 112.7183;

                const map = L.map('rtlhHeatmapContainer').setView([centerLat, centerLng], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                let heatLayer = null;
                let markerGroup = L.layerGroup().addTo(map);

                const colorMap = {
                    survei: '#F59E0B',
                    penerima: '#EF4444',
                    renovasi: '#8B5CF6',
                    selesai: '#10B981'
                };

                function renderLayer(filterLayer) {
                    markerGroup.clearLayers();
                    if (heatLayer) {
                        map.removeLayer(heatLayer);
                    }

                    const filtered = filterLayer === 'all'
                        ? points
                        : points.filter(p => p.layer === filterLayer);

                    const heatCoords = filtered.map(p => [p.lat, p.lng, 0.85]);
                    if (typeof L.heatLayer === 'function' && heatCoords.length > 0) {
                        heatLayer = L.heatLayer(heatCoords, { radius: 28, blur: 18, maxZoom: 15 }).addTo(map);
                    }

                    filtered.forEach(p => {
                        const marker = L.circleMarker([p.lat, p.lng], {
                            radius: 7,
                            fillColor: colorMap[p.layer] || '#3B82F6',
                            color: '#ffffff',
                            weight: 2,
                            fillOpacity: 0.9
                        });
                        marker.bindPopup(`<div class="text-xs"><strong>${p.kode}</strong><br>Wilayah: ${p.wilayah}<br>Status: ${p.status}</div>`);
                        markerGroup.addLayer(marker);
                    });
                }

                renderLayer('all');

                document.querySelectorAll('#heatmapFilterBtns .hm-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        document.querySelectorAll('#heatmapFilterBtns .hm-btn').forEach(b => {
                            b.className = 'hm-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200';
                        });
                        this.className = 'hm-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-forest text-white';
                        renderLayer(this.dataset.layer);
                    });
                });
            });
        </script>
    @endif

</div>
@endsection