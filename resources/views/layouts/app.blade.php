<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
    <title>{{ config('app.name', 'RumahAsa') }} — @yield('title', 'Dashboard')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: { DEFAULT: '#1D4034', light: '#2A5244', dark: '#152C23', accent: '#3E6B5A' },
                        cream: { DEFAULT: '#F2EFE8', light: '#FFFFFF', dark: '#E8E4DC' },
                        terracotta: { DEFAULT: '#E8B87D', light: '#F0C99A' },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-cream font-sans">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-forest text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="px-6 py-5 border-b border-forest-light/60">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.svg') }}" alt="RumahAsa Logo" class="w-8 h-8">
                    <div>
                        <h1 class="text-lg font-extrabold leading-none tracking-tight">Rumah<span class="text-terracotta">Asa</span></h1>
                    </div>
                </div>
            </div>

            {{-- Kartu Wilayah Tugas / Unit Peran di Sidebar Kiri (Sesuai Mockup) --}}
            @php
                $currUser = auth()->user();
                $currRoleKode = $currUser?->role?->kode;
                $unitTitle = match ($currRoleKode) {
                    'surveyor'    => 'Survei Lapangan',
                    'verifikator' => 'Verifikasi Kelayakan',
                    'admin'       => 'Administrasi Sistem',
                    'pimpinan'    => 'Monitoring Eksekutif',
                    default       => 'Petugas RumahAsa',
                };
                $unitBadge = match ($currRoleKode) {
                    'surveyor'    => 'SA',
                    'verifikator' => 'VF',
                    'admin'       => 'AD',
                    'pimpinan'    => 'PM',
                    default       => 'RA',
                };
                $wilayahSidebarLabel = $currUser?->wilayah
                    ? $currUser->wilayah->namaWilayahTugasSidebar()
                    : ($currRoleKode === 'admin' ? 'Akses Penuh' : 'Seluruh Wilayah');
            @endphp
            <div class="mx-4 mt-4 rounded-xl bg-forest-light/80 border border-white/10 p-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cream text-forest font-extrabold text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                        {{ $unitBadge }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ $unitTitle }}</p>
                        <p class="text-[11px] text-green-200/90 truncate mt-0.5">{{ $wilayahSidebarLabel }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-1">
                <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-green-200/60">Menu Utama</p>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('dashboard') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    Dashboard
                </a>

                {{-- Khusus Verifikator: Review Pengajuan Survei --}}
                @can('access-verifikator')
                <a href="{{ route('verifikator.survei.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('verifikator.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        <span>Review Survei</span>
                    </div>
                    @php
                        $antreanDiajukanCount = \App\Models\RumahWarga::where('status', \App\Models\RumahWarga::STATUS_DIAJUKAN)->count();
                    @endphp
                    @if ($antreanDiajukanCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-slate-900">{{ $antreanDiajukanCount }}</span>
                    @endif
                </a>
                @endcan

                {{-- Data Rumah: Semua Peran --}}
                <a href="{{ route('rumah.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ (request()->routeIs('rumah.index') && request()->query('status') !== 'Dalam Renovasi') || request()->routeIs('rumah.show') || request()->routeIs('rumah.edit') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    Data Rumah
                </a>

                {{-- Khusus Admin: Input Rumah Baru --}}
                @can('input-rumah')
                <a href="{{ route('rumah.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('rumah.create') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Input Rumah
                </a>
                @endcan

                {{-- Khusus Surveyor: Progres Renovasi di Wilayah Tugas --}}
                @can('access-surveyor')
                <a href="{{ route('rumah.index', ['status' => 'Dalam Renovasi']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->query('status') === 'Dalam Renovasi' || request()->routeIs('bantuan.progres.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    Progres Renovasi
                </a>
                @endcan

                {{-- Khusus Verifikator, Admin, Pimpinan: Bantuan & Renovasi --}}
                @canany(['verifikator', 'admin', 'pimpinan'])
                <a href="{{ route('bantuan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('bantuan.index') && request()->query('mode') !== 'progres' ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="hand-heart" class="w-4 h-4"></i>
                    Daftar Penerima Bantuan
                </a>
                <a href="{{ route('bantuan.index', ['mode' => 'progres']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ (request()->routeIs('bantuan.index') && request()->query('mode') === 'progres') || request()->routeIs('bantuan.show') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    Progres Renovasi
                </a>
                <a href="{{ route('bantuan.rekap') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('bantuan.rekap') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    Rekap Dana
                </a>
                @endcanany

                {{-- Menu Khusus Admin --}}
                @can('manage-wilayah')
                <a href="{{ route('wilayah.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('wilayah.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    Kelola Wilayah
                </a>
                @endcan
                @can('manage-users')
                <a href="{{ route('users.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('users.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Kelola User
                </a>
                @endcan
                @can('view-audit-log')
                <a href="{{ route('audit.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('audit.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Audit Log
                </a>
                @endcan

                {{-- Profil (Sesuai Mockup) --}}
                <a href="{{ route('password.change') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('password.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    Profil
                </a>
            </nav>

            {{-- Kartu Butuh Bantuan Sesuai Mockup --}}
            <div class="mx-4 mb-3 rounded-xl bg-forest-light/70 p-3.5 border border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0 text-terracotta">
                        <i data-lucide="shield-question" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Butuh bantuan?</p>
                        <p class="text-[11px] text-green-200/90 mt-0.5 leading-snug">Hubungi admin wilayah</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-forest-light/60">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition text-green-100 hover:text-white">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
        
        <main class="flex-1 overflow-x-auto">
            <div class="bg-cream/90 backdrop-blur-xs border-b border-cream-dark px-8 py-4 flex items-center justify-between gap-4 sticky top-0 z-10">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <span>RumahAsa</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="font-bold text-slate-900">@yield('title', 'Dashboard')</span>
                </div>

                {{-- Kanan Header: Pencarian Data Rumah + Lonceng Notifikasi + Profil (Sesuai Mockup) --}}
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('rumah.index') }}" class="relative hidden sm:block w-64 lg:w-72">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari data rumah..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border border-slate-200/90 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-forest/30 focus:border-forest"
                        >
                    </form>

                    {{-- Lonceng Notifikasi --}}
                    <div x-data="{ openNotif: false }" class="relative">
                        <button type="button" @click="openNotif = !openNotif" class="relative w-10 h-10 rounded-xl bg-white border border-slate-200/90 flex items-center justify-center text-slate-700 hover:bg-slate-50 transition">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span class="w-2 h-2 rounded-full bg-amber-500 absolute top-2 right-2.5"></span>
                        </button>
                        <div x-show="openNotif" @click.away="openNotif = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-slate-200 p-4 z-50 text-xs space-y-2.5">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span class="font-bold text-slate-900">Notifikasi Sistem</span>
                                <span class="text-[10px] font-semibold text-forest bg-forest/10 px-2 py-0.5 rounded-full">Terbaru</span>
                            </div>
                            <p class="text-slate-600 leading-relaxed">
                                Wilayah Tugas: <strong>{{ $wilayahSidebarLabel }}</strong>. Pastikan data survei dan dokumentasi foto selalu diperbarui.
                            </p>
                        </div>
                    </div>

                    {{-- Avatar & Nama Profil --}}
                    <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 pl-1 pr-2 py-1 rounded-xl hover:bg-white/70 transition">
                        <div class="w-10 h-10 rounded-xl bg-forest text-white flex items-center justify-center text-xs font-extrabold shadow-xs">
                            {{ strtoupper(substr(auth()->user()?->nama, 0, 2)) }}
                        </div>
                        <div class="text-xs hidden sm:block">
                            <p class="font-bold text-slate-900 leading-tight">{{ auth()->user()?->nama }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ auth()->user()?->role?->nama }}</p>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 hidden sm:block"></i>
                    </a>
                </div>
            </div>
            
            <div class="p-8">
                @if (session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('warning'))
                    <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        {{ session('warning') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>