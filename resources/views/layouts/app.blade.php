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
                        forest: {
                            DEFAULT: '#1D4034',
                            light: '#2A5244',
                            dark: '#152C23',
                            accent: '#3E6B5A',
                            50: '#EEF5F1',
                            100: '#D6E6DE',
                            200: '#B0CEBF',
                            300: '#84B19C',
                            400: '#589378',
                            500: '#3B785D',
                            600: '#2D604A',
                            700: '#244E3C',
                            800: '#1D4034',
                            900: '#152E25',
                            950: '#0C1C16',
                        },
                        cream: {
                            DEFAULT: '#F2EFE8',
                            light: '#FFFFFF',
                            dark: '#E8E4DC',
                            50: '#FAF9F6',
                            100: '#F2EFE8',
                            200: '#E6E2D8',
                        },
                        terracotta: { DEFAULT: '#E8B87D', light: '#F0C99A' },
                        gold: {
                            400: '#E8B87D',
                            500: '#D49A55',
                            600: '#B87A35',
                        },
                        clay: {
                            100: '#F7EFE5',
                            600: '#9A5B25',
                        },
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
    <style>
        [x-cloak] { display: none !important; }

        /* ===================================================================== */
        /* STANDAR ANIMASI & TRANSISI RUMAHASA (KONSISTEN DI SELURUH ROLE)       */
        /* ===================================================================== */
        @keyframes raFadeSlideUp {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes raCardPop {
            0% {
                opacity: 0;
                transform: translateY(8px) scale(0.99);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes raPulseSubtle {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.18); opacity: 0.78; }
        }

        /* Halaman utama masuk secara halus */
        .ra-page-container > *,
        .ra-page-enter {
            animation: raFadeSlideUp 0.36s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* Staggered animation untuk grid kartu ringkasan di semua dashboard & halaman */
        .ra-page-container .grid > div,
        .ra-page-container .grid > a,
        .ra-stagger > * {
            animation: raCardPop 0.38s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .ra-page-container .grid > :nth-child(1) { animation-delay: 0.02s; }
        .ra-page-container .grid > :nth-child(2) { animation-delay: 0.05s; }
        .ra-page-container .grid > :nth-child(3) { animation-delay: 0.08s; }
        .ra-page-container .grid > :nth-child(4) { animation-delay: 0.11s; }
        .ra-page-container .grid > :nth-child(5) { animation-delay: 0.14s; }
        .ra-page-container .grid > :nth-child(6) { animation-delay: 0.17s; }
        .ra-page-container .grid > :nth-child(7) { animation-delay: 0.20s; }
        .ra-page-container .grid > :nth-child(8) { animation-delay: 0.23s; }

        /* Hover elevation pada kartu di seluruh halaman */
        .ra-card-hover,
        .ra-page-container .bg-white.rounded-2xl,
        .ra-page-container .bg-white.rounded-xl,
        .ra-page-container .bg-white.rounded-3xl {
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1),
                        box-shadow 0.22s cubic-bezier(0.22, 1, 0.36, 1),
                        border-color 0.22s ease;
        }
        .ra-card-hover:hover,
        .ra-page-container a.bg-white.rounded-2xl:hover,
        .ra-page-container .grid > div.bg-white.rounded-xl:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px -6px rgba(29, 64, 52, 0.09), 0 4px 10px -4px rgba(29, 64, 52, 0.05);
        }

        /* Micro-interaction tombol */
        .ra-btn-press,
        .ra-page-container button,
        .ra-page-container a.rounded-xl {
            transition: transform 0.16s cubic-bezier(0.22, 1, 0.36, 1),
                        background-color 0.18s ease,
                        box-shadow 0.18s ease,
                        border-color 0.18s ease;
        }
        .ra-btn-press:active,
        .ra-page-container button:active,
        .ra-page-container a.rounded-xl:active {
            transform: scale(0.97);
        }

        /* Animasi baris tabel */
        .ra-page-container tbody tr {
            transition: background-color 0.16s ease, transform 0.16s ease;
        }

        /* Menu navigasi sidebar kiri */
        .ra-nav-link {
            transition: background-color 0.18s ease, transform 0.18s cubic-bezier(0.22, 1, 0.36, 1), color 0.18s ease;
        }
        .ra-nav-link:hover {
            transform: translateX(3px);
        }

        /* Indikator lonceng notifikasi */
        .ra-bell-dot {
            animation: raPulseSubtle 2.4s infinite ease-in-out;
        }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="h-full bg-cream font-sans" x-data="{ openHelpModal: false }" @keydown.escape.window="openHelpModal = false">
    @php
        $currUser = auth()->user();
        $currRoleKode = $currUser?->role?->kode;
        $unitTitle = match ($currRoleKode) {
            'surveyor'    => 'Survei Lapangan',
            'verifikator' => 'Verifikator Wilayah',
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
        $antreanDiajukanCount = \App\Models\RumahWarga::where('status', \App\Models\RumahWarga::STATUS_DIAJUKAN)->count();
    @endphp

    <div class="min-h-screen flex">
        <aside class="w-64 bg-forest text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="px-6 py-5 border-b border-forest-light/60">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('logo.svg') }}" alt="RumahAsa Logo" class="w-8 h-8 group-hover:scale-105 transition duration-200">
                    <div>
                        <h1 class="text-lg font-extrabold leading-none tracking-tight">Rumah<span class="text-terracotta">Asa</span></h1>
                    </div>
                </a>
            </div>

            {{-- Kartu Wilayah Tugas / Unit Peran di Sidebar Kiri (Sesuai Mockup) --}}
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
                <a href="{{ route('dashboard') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('dashboard') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    Dashboard
                </a>

                {{-- Khusus Verifikator: Review Pengajuan Survei --}}
                @can('access-verifikator')
                <a href="{{ route('verifikator.survei.index') }}" class="ra-nav-link flex items-center justify-between px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('verifikator.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        <span>Review Survei</span>
                    </div>
                    @if ($antreanDiajukanCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-slate-900">{{ $antreanDiajukanCount }}</span>
                    @endif
                </a>
                @endcan

                {{-- Data Rumah: Semua Peran --}}
                <a href="{{ route('rumah.index') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ (request()->routeIs('rumah.index') && request()->query('status') !== 'Dalam Renovasi') || request()->routeIs('rumah.show') || request()->routeIs('rumah.edit') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    Data Rumah
                </a>

                {{-- Khusus Admin: Input Rumah Baru --}}
                @can('input-rumah')
                <a href="{{ route('rumah.create') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('rumah.create') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Input Rumah
                </a>
                @endcan

                {{-- Khusus Surveyor: Progres Renovasi di Wilayah Tugas --}}
                @can('access-surveyor')
                <a href="{{ route('rumah.index', ['status' => 'Dalam Renovasi']) }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->query('status') === 'Dalam Renovasi' || request()->routeIs('bantuan.progres.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    Progres Renovasi
                </a>
                @endcan

                {{-- Khusus Verifikator, Admin, Pimpinan: Daftar Penerima Bantuan, Progres Renovasi, Rekap Dana --}}
                @canany(['verifikator', 'admin', 'pimpinan'])
                <a href="{{ route('bantuan.index') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ (request()->routeIs('bantuan.index') && request()->query('mode') !== 'progres') || request()->routeIs('bantuan.penerima.show') || request()->routeIs('bantuan.salurkan.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="hand-heart" class="w-4 h-4"></i>
                    Daftar Penerima Bantuan
                </a>
                <a href="{{ route('bantuan.index', ['mode' => 'progres']) }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ (request()->routeIs('bantuan.index') && request()->query('mode') === 'progres') || request()->routeIs('bantuan.show') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    Progres Renovasi
                </a>
                <a href="{{ route('bantuan.rekap') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('bantuan.rekap') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    Rekap Dana
                </a>
                @endcanany

                {{-- Menu Khusus Admin --}}
                @can('manage-wilayah')
                <a href="{{ route('wilayah.view') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('wilayah.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    Kelola Wilayah
                </a>
                @endcan
                @can('manage-users')
                <a href="{{ route('users.view') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('users.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Kelola User
                </a>
                @endcan
                @can('view-audit-log')
                <a href="{{ route('audit.view') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('audit.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Audit Log
                </a>
                @endcan

                {{-- Profil (Sesuai Mockup) --}}
                <a href="{{ route('password.change') }}" class="ra-nav-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light {{ request()->routeIs('password.*') ? 'bg-forest-light font-semibold shadow-2xs' : '' }}">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    Profil
                </a>
            </nav>

            {{-- Kartu Butuh Bantuan Sesuai Mockup (Klik memunculkan Pop-up Bantuan) --}}
            <button type="button"
                    @click="openHelpModal = true"
                    class="mx-4 mb-3 rounded-xl bg-forest-light/70 hover:bg-forest-light p-3.5 border border-white/10 text-left transition group">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 group-hover:bg-white/15 flex items-center justify-center flex-shrink-0 text-terracotta transition">
                        <i data-lucide="shield-question" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Butuh bantuan?</p>
                        <p class="text-[11px] text-green-200/90 mt-0.5 leading-snug">Hubungi admin wilayah</p>
                    </div>
                </div>
            </button>

            <div class="p-4 border-t border-forest-light/60">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ra-nav-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light text-green-100 hover:text-white">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
        
        <main class="flex-1 overflow-x-auto">
            <div class="bg-cream/90 backdrop-blur-xs border-b border-cream-dark px-8 py-4 flex items-center justify-between gap-4 sticky top-0 z-20">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <span>RumahAsa</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="font-bold text-slate-900">@yield('title', 'Dashboard')</span>
                </div>

                {{-- Kanan Header: Pencarian Data Rumah + Pop-up Lonceng Notifikasi + Pop-up Menu Profil --}}
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('rumah.index') }}" class="relative hidden sm:block w-64 lg:w-72">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari data rumah..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border border-slate-200/90 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-forest/30 focus:border-forest transition"
                        >
                    </form>

                    {{-- Pop-up Lonceng Notifikasi --}}
                    <div x-data="{ openNotif: false }" class="relative">
                        <button type="button"
                                @click="openNotif = !openNotif"
                                class="relative w-10 h-10 rounded-xl bg-white border border-slate-200/90 flex items-center justify-center text-slate-700 hover:bg-slate-50 transition ra-btn-press">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span class="w-2 h-2 rounded-full bg-amber-500 absolute top-2 right-2.5 ra-bell-dot"></span>
                        </button>
                        <div x-show="openNotif"
                             @click.away="openNotif = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 text-xs space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <span class="font-extrabold text-slate-900">Notifikasi Sistem</span>
                                <span class="text-[10px] font-bold text-forest bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full">Aktif</span>
                            </div>

                            <div class="space-y-2.5">
                                @if ($currRoleKode === 'verifikator')
                                    <a href="{{ route('verifikator.survei.index') }}" class="flex items-start gap-2.5 p-2.5 rounded-xl bg-amber-50/70 hover:bg-amber-50 border border-amber-200/60 transition">
                                        <i data-lucide="clipboard-check" class="w-4 h-4 text-amber-700 flex-shrink-0 mt-0.5"></i>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $antreanDiajukanCount }} Pengajuan Menunggu Review</p>
                                            <p class="text-[11px] text-slate-600 mt-0.5">Klik untuk membuka antrean verifikasi kelayakan survei.</p>
                                        </div>
                                    </a>
                                    <a href="{{ route('bantuan.index', ['mode' => 'progres']) }}" class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200/60 transition">
                                        <i data-lucide="building-2" class="w-4 h-4 text-forest flex-shrink-0 mt-0.5"></i>
                                        <div>
                                            <p class="font-bold text-slate-900">Monitoring Progres Renovasi</p>
                                            <p class="text-[11px] text-slate-600 mt-0.5">Pantau laporan progres 100% yang siap ditetapkan Selesai.</p>
                                        </div>
                                    </a>
                                @elseif ($currRoleKode === 'surveyor')
                                    <a href="{{ route('rumah.index') }}" class="flex items-start gap-2.5 p-2.5 rounded-xl bg-emerald-50/70 hover:bg-emerald-50 border border-emerald-200/60 transition">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-forest flex-shrink-0 mt-0.5"></i>
                                        <div>
                                            <p class="font-bold text-slate-900">Wilayah Tugas: {{ $wilayahSidebarLabel }}</p>
                                            <p class="text-[11px] text-slate-600 mt-0.5">Pastikan 7 komponen survei dan foto dokumentasi lapangan terisi lengkap.</p>
                                        </div>
                                    </a>
                                @else
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60">
                                        <p class="font-bold text-slate-900">Cakupan Akses: {{ $wilayahSidebarLabel }}</p>
                                        <p class="text-[11px] text-slate-600 mt-0.5">Seluruh aktivitas tercatat secara otomatis di Audit Log sistem.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Pop-up Dropdown Menu Profil Petugas --}}
                    <div x-data="{ openProfileMenu: false }" class="relative">
                        <button type="button"
                                @click="openProfileMenu = !openProfileMenu"
                                class="flex items-center gap-2.5 pl-1 pr-2.5 py-1 rounded-xl hover:bg-white/80 transition ra-btn-press text-left">
                            <div class="w-10 h-10 rounded-xl bg-forest text-white flex items-center justify-center text-xs font-extrabold shadow-xs">
                                {{ strtoupper(substr($currUser?->nama, 0, 2)) }}
                            </div>
                            <div class="text-xs hidden sm:block">
                                <p class="font-bold text-slate-900 leading-tight">{{ $currUser?->nama }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ $currUser?->role?->nama }}</p>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 hidden sm:block transition-transform duration-200" :class="openProfileMenu ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openProfileMenu"
                             @click.away="openProfileMenu = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 z-50 text-xs space-y-2">
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <p class="font-extrabold text-slate-900 truncate">{{ $currUser?->nama }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $currUser?->email ?: ($currUser?->username . '@rumahasa.go.id') }}</p>
                                <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-forest border border-emerald-200/70">
                                    {{ $currUser?->role?->nama }} &middot; {{ $wilayahSidebarLabel }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-semibold transition">
                                    <i data-lucide="user" class="w-4 h-4 text-forest"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="{{ route('password.change', ['ubah' => 1]) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-semibold transition">
                                    <i data-lucide="key-round" class="w-4 h-4 text-forest"></i>
                                    <span>Ubah Password</span>
                                </a>
                            </div>

                            <div class="pt-1 border-t border-slate-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold transition">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 ra-page-container">
                @if (session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-3 shadow-2xs">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('warning'))
                    <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-3 shadow-2xs">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        {{ session('warning') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL POP-UP: BUTUH BANTUAN? HUBUNGI ADMIN WILAYAH                      --}}
    {{-- ======================================================================= --}}
    <div x-show="openHelpModal"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div @click.away="openHelpModal = false"
             x-show="openHelpModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-7 shadow-2xl space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-forest flex items-center justify-center flex-shrink-0">
                        <i data-lucide="shield-question" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Pusat Bantuan Petugas</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Panduan layanan &amp; kontak Admin Wilayah RumahAsa.</p>
                    </div>
                </div>
                <button type="button" @click="openHelpModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs text-slate-600">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-1">
                    <p class="font-extrabold text-slate-900">Kontak Admin Wilayah</p>
                    <p>Email: <span class="font-semibold text-forest">helpdesk@rumahasa.go.id</span></p>
                    <p>Layanan Teknis: <span class="font-semibold text-slate-800">Senin–Jumat, 08.00–16.00 WIB</span></p>
                </div>

                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/70 space-y-1 text-emerald-950">
                    <p class="font-bold">Hak Akses &amp; Alur Kerja ({{ $currUser?->role?->nama }})</p>
                    <p class="text-[11px] text-emerald-800 leading-relaxed">
                        @if ($currRoleKode === 'verifikator')
                            Verifikator meninjau pengajuan survei secara manual, mencatat penyaluran bantuan pada halaman Detail Penerima Bantuan, dan menetapkan status Selesai pada halaman Review Penyelesaian Renovasi saat progres mencapai 100%.
                        @elseif ($currRoleKode === 'surveyor')
                            Surveyor mengisi 7 komponen kelayakan rumah beserta foto bukti di wilayah tugasnya, serta melaporkan progres renovasi secara berkala.
                        @elseif ($currRoleKode === 'pimpinan')
                            Pimpinan memantau sebaran heatmap status rumah serta mengekspor laporan Rekap Dana Bantuan per triwulan (Excel/PDF).
                        @else
                            Admin mengelola data rumah awal, master wilayah berjenjang, manajemen akun petugas, dan memantau Audit Log sistem.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end pt-1">
                <button type="button"
                        @click="openHelpModal = false"
                        class="px-5 py-2.5 rounded-xl bg-forest hover:bg-forest-light text-white text-xs font-bold shadow-sm transition ra-btn-press">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
