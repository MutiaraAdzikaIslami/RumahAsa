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
            <div class="p-6 border-b border-forest-light">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.svg') }}" alt="RumahAsa Logo" class="w-8 h-8">
                    <div>
                        <h1 class="text-lg font-bold leading-none">Rumah<span class="text-terracotta">Asa</span></h1>
                        <p class="text-[10px] text-green-200 mt-1 uppercase tracking-wider">{{ auth()->user()?->role?->nama }}</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('dashboard') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Dashboard
                </a>

                {{-- Khusus Verifikator: Review Pengajuan Survei --}}
                @can('access-verifikator')
                <a href="{{ route('verifikator.survei.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('verifikator.*') ? 'bg-forest-light' : '' }}">
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
                <a href="{{ route('rumah.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('rumah.index') || request()->routeIs('rumah.show') || request()->routeIs('rumah.edit') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    Data Rumah
                </a>

                {{-- Khusus Admin: Input Rumah Baru --}}
                @can('input-rumah')
                <a href="{{ route('rumah.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('rumah.create') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Input Rumah
                </a>
                @endcan

                {{-- Khusus Surveyor: Progres Renovasi di Wilayah Tugas --}}
                @can('access-surveyor')
                <a href="{{ route('rumah.index', ['status' => 'Dalam Renovasi']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->query('status') === 'Dalam Renovasi' ? 'bg-forest-light' : '' }}">
                    <i data-lucide="hammer" class="w-4 h-4"></i>
                    Progres Renovasi
                </a>
                @endcan

                {{-- Khusus Verifikator, Admin, Pimpinan: Bantuan & Renovasi --}}
                @canany(['verifikator', 'admin', 'pimpinan'])
                <a href="{{ route('bantuan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('bantuan.index') && request()->query('status') !== 'Dalam Renovasi' ? 'bg-forest-light' : '' }}">
                    <i data-lucide="hand-heart" class="w-4 h-4"></i>
                    Daftar Penerima Bantuan
                </a>
                <a href="{{ route('bantuan.index', ['status' => 'Dalam Renovasi']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->query('status') === 'Dalam Renovasi' ? 'bg-forest-light' : '' }}">
                    <i data-lucide="hammer" class="w-4 h-4"></i>
                    Progres Renovasi
                </a>
                <a href="{{ route('bantuan.rekap') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('bantuan.rekap') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    Rekap Dana
                </a>
                @endcanany

                {{-- Menu Khusus Admin --}}
                @can('manage-wilayah')
                <a href="{{ route('wilayah.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('wilayah.*') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    Kelola Wilayah
                </a>
                @endcan
                @can('manage-users')
                <a href="{{ route('users.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('users.*') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Kelola User
                </a>
                @endcan
                @can('view-audit-log')
                <a href="{{ route('audit.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('audit.*') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Audit Log
                </a>
                @endcan

                {{-- Profil / Ganti Password --}}
                <div class="pt-2 border-t border-forest-light/60 my-2"></div>
                <a href="{{ route('password.change') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition {{ request()->routeIs('password.*') ? 'bg-forest-light' : '' }}">
                    <i data-lucide="user-cog" class="w-4 h-4"></i>
                    Profil &amp; Keamanan
                </a>
            </nav>

            {{-- Kartu Butuh Bantuan Sesuai Mockup --}}
            <div class="mx-4 mb-3 rounded-xl bg-forest-light/70 p-3.5 border border-forest-light">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-terracotta/20 flex items-center justify-center flex-shrink-0 text-terracotta">
                        <i data-lucide="life-buoy" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-white">Butuh Bantuan?</p>
                        <p class="text-[11px] text-green-200 mt-0.5 leading-snug">Hubungi helpdesk teknis RumahAsa atau admin kelurahan.</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-forest-light">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-forest-light transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>
        
        <main class="flex-1 overflow-x-auto">
            <div class="bg-white border-b border-cream-dark px-8 py-4 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <span>RumahAsa</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    <span class="font-medium text-slate-900">@yield('title', 'Dashboard')</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-forest text-white flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr(auth()->user()?->nama, 0, 2)) }}
                    </div>
                    <div class="text-sm">
                        <p class="font-medium text-slate-900">{{ auth()->user()?->nama }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()?->role?->nama }}</p>
                    </div>
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