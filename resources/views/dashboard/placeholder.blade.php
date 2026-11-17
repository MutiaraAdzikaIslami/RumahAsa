@extends('layouts.app')

@php
    $today = now();
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$today->dayOfWeek];
    $bulan = [
        '',
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];
    $tanggalStr = strtoupper(
        $hari . ', ' . $today->day . ' ' . $bulan[$today->month] . ' ' . $today->year,
    );
@endphp

@section('title', 'RumahAsa - Dashboard')
@section('header', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">
                    {{ $tanggalStr }}</p>
                <h1 class="mt-1 text-3xl font-bold text-forest-900 sm:text-4xl">
                    @if (auth()->user()->isAdmin())
                        Dashboard Admin
                    @else
                        Dashboard {{ ucfirst(auth()->user()->role->kode) }}
                    @endif
                </h1>
                <p class="mt-1 text-sm text-forest-600">Kelola pengguna, wilayah, data, dan laporan
                    RumahAsa.</p>
            </div>
            <div
                class="inline-flex items-center gap-2 rounded-full border border-forest-100 bg-white px-3 py-1.5 text-xs">
                <span class="h-2 w-2 rounded-full bg-forest-500"></span>
                <span class="font-medium text-forest-700">Sistem berjalan normal</span>
                <span class="text-forest-400">&middot;</span>
                <span class="text-forest-500">Sinkronisasi {{ $today->format('H.i') }} WIB</span>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-forest-50">
                    <i
                        data-lucide="users"
                        class="h-6 w-6 text-forest-600"
                    ></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Total User
                    </p>
                    <p class="text-2xl font-bold text-forest-900">{{ \App\Models\User::count() }}</p>
                    <p class="text-xs text-forest-500">
                        {{ \App\Models\User::where('is_aktif', true)->count() }} aktif</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-forest-50">
                    <i
                        data-lucide="map"
                        class="h-6 w-6 text-forest-600"
                    ></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Surveyor</p>
                    <p class="text-2xl font-bold text-forest-900">
                        {{ \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'surveyor'))->count() }}
                    </p>
                    <p class="text-xs text-forest-500">Petugas lapangan</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-clay-100">
                    <i
                        data-lucide="shield-check"
                        class="h-6 w-6 text-clay-600"
                    ></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Verifikator
                    </p>
                    <p class="text-2xl font-bold text-forest-900">
                        {{ \App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'verifikator'))->count() }}
                    </p>
                    <p class="text-xs text-forest-500">Petugas verifikasi</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-forest-50">
                    <i
                        data-lucide="home"
                        class="h-6 w-6 text-forest-600"
                    ></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Total Rumah
                    </p>
                    <p class="text-2xl font-bold text-forest-900">{{ \App\Models\RumahWarga::count() }}
                    </p>
                    <p class="text-xs text-forest-500">Rumah terdaftar</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-clay-100">
                    <i
                        data-lucide="file-text"
                        class="h-6 w-6 text-clay-600"
                    ></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Menunggu
                        Survei</p>
                    <p class="text-2xl font-bold text-forest-900">
                        {{ \App\Models\RumahWarga::where('status', 'Menunggu Disurvei')->count() }}</p>
                    <p class="text-xs text-forest-500">Menunggu surveyor</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-forest-50">
                    <i
                        data-lucide="check-circle"
                        class="h-6 w-6 text-forest-600"
                    ></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Selesai</p>
                    <p class="text-2xl font-bold text-forest-900">
                        {{ \App\Models\RumahWarga::where('status', 'Selesai')->count() }}</p>
                    <p class="text-xs text-forest-500">Renovasi selesai</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-forest-900">Data Rumah berdasarkan Status</h3>
                        <p class="text-xs text-forest-500">Distribusi status kategori rumah</p>
                    </div>
                    <select
                        class="rounded-lg border border-forest-100 bg-white px-3 py-1.5 text-xs text-forest-700 focus:outline-none"
                    >
                        <option>{{ $today->format('F Y') }}</option>
                    </select>
                </div>

                <div class="mt-5 space-y-3 text-xs">
                    @php
                        $statuses = [
                            [
                                'label' => 'Menunggu Disurvei',
                                'value' => \App\Models\RumahWarga::where(
                                    'status',
                                    'Menunggu Disurvei',
                                )->count(),
                                'color' => '#9CA3AF',
                            ],
                            [
                                'label' => 'Verifikasi',
                                'value' => \App\Models\RumahWarga::where(
                                    'status',
                                    'Diajukan',
                                )->count(),
                                'color' => '#64748B',
                            ],
                            [
                                'label' => 'Perlu Perbaikan',
                                'value' => \App\Models\RumahWarga::where(
                                    'status',
                                    'Perlu Perbaikan',
                                )->count(),
                                'color' => '#D97706',
                            ],
                            [
                                'label' => 'Dalam Renovasi',
                                'value' => \App\Models\RumahWarga::where(
                                    'status',
                                    'Dalam Renovasi',
                                )->count(),
                                'color' => '#92400E',
                            ],
                            [
                                'label' => 'Selesai',
                                'value' => \App\Models\RumahWarga::where(
                                    'status',
                                    'Selesai',
                                )->count(),
                                'color' => '#3E6B57',
                            ],
                        ];
                        $total = array_sum(array_column($statuses, 'value'));
                    @endphp
                    @foreach ($statuses as $s)
                        @php $pct = $total > 0 ? ($s['value'] / $total) * 100 : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-forest-700">{{ $s['label'] }}</span>
                                <span class="font-semibold text-forest-900">{{ $s['value'] }}</span>
                            </div>
                            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-cream-100">
                                <div
                                    class="h-full rounded-full"
                                    style="background: {{ $s['color'] }}; width: {{ $pct }}%;"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div
                    class="mt-4 flex items-center gap-2 rounded-lg bg-forest-50 px-3 py-2 text-xs text-forest-700">
                    <i
                        data-lucide="info"
                        class="h-4 w-4 text-forest-500"
                    ></i>
                    <span>Status kategori merupakan keputusan manual Verifikator setelah survei
                        lapangan.</span>
                </div>
            </div>

            <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-forest-900">Akses Cepat</h3>
                        <p class="text-xs text-forest-500">Pintasan ke modul yang sering dipakai</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @can('manage-users')
                        <a
                            href="{{ route('users.index') }}"
                            class="group flex items-center gap-3 rounded-xl border border-forest-100 p-4 transition hover:border-forest-300 hover:bg-cream-50"
                        >
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-50">
                                <i
                                    data-lucide="users"
                                    class="h-5 w-5 text-forest-600"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-forest-900">Manajemen User</p>
                                <p class="text-xs text-forest-500">Kelola akun pengguna</p>
                            </div>
                            <i
                                data-lucide="arrow-right"
                                class="h-4 w-4 text-forest-400 transition group-hover:translate-x-1"
                            ></i>
                        </a>

                        <a
                            href="{{ route('rumah.index') }}"
                            class="group flex items-center gap-3 rounded-xl border border-forest-100 p-4 transition hover:border-forest-300 hover:bg-cream-50"
                        >
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-50">
                                <i
                                    data-lucide="home"
                                    class="h-5 w-5 text-forest-600"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-forest-900">Data Rumah</p>
                                <p class="text-xs text-forest-500">Lihat &amp; input data rumah</p>
                            </div>
                            <i
                                data-lucide="arrow-right"
                                class="h-4 w-4 text-forest-400 transition group-hover:translate-x-1"
                            ></i>
                        </a>

                        <a
                            href="{{ route('wilayah.index') }}"
                            class="group flex items-center gap-3 rounded-xl border border-forest-100 p-4 transition hover:border-forest-300 hover:bg-cream-50"
                        >
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-50">
                                <i
                                    data-lucide="map"
                                    class="h-5 w-5 text-forest-600"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-forest-900">Kelola Wilayah</p>
                                <p class="text-xs text-forest-500">Hierarki wilayah administratif</p>
                            </div>
                            <i
                                data-lucide="arrow-right"
                                class="h-4 w-4 text-forest-400 transition group-hover:translate-x-1"
                            ></i>
                        </a>
                    @endcan

                    @can('access-surveyor')
                        <a
                            href="{{ route('survei.index') }}"
                            class="group flex items-center gap-3 rounded-xl border border-forest-100 p-4 transition hover:border-forest-300 hover:bg-cream-50"
                        >
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-50">
                                <i
                                    data-lucide="clipboard-list"
                                    class="h-5 w-5 text-forest-600"
                                ></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-forest-900">Tugas Survei</p>
                                <p class="text-xs text-forest-500">Rumah yang ditugaskan ke Anda</p>
                            </div>
                            <i
                                data-lucide="arrow-right"
                                class="h-4 w-4 text-forest-400 transition group-hover:translate-x-1"
                            ></i>
                        </a>
                    @endcan

                    <a
                        href="{{ route('password.change') }}"
                        class="group flex items-center gap-3 rounded-xl border border-forest-100 p-4 transition hover:border-forest-300 hover:bg-cream-50"
                    >
                        <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-50">
                            <i
                                data-lucide="key-round"
                                class="h-5 w-5 text-forest-600"
                            ></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-forest-900">Ganti Password</p>
                            <p class="text-xs text-forest-500">Perbarui password akun Anda</p>
                        </div>
                        <i
                            data-lucide="arrow-right"
                            class="h-4 w-4 text-forest-400 transition group-hover:translate-x-1"
                        ></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection