<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg?v=4') }}">
    <link rel="shortcut icon" href="{{ asset('logo.svg?v=4') }}">
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
                            DEFAULT: '#F5F3EE',
                            light: '#FFFFFF',
                            dark: '#E6E2D8',
                            50: '#FAF9F6',
                            100: '#F2EFE8',
                            200: '#E6E2D8',
                        },
                        terracotta: {
                            DEFAULT: '#E8B87D',
                            light: '#F0C99A',
                        },
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
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-cream font-sans text-forest-900">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-forest text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 border-b border-forest-light">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.svg?v=4') }}" alt="RumahAsa Logo" class="w-9 h-9 rounded-xl">
                    <div>
                        <h1 class="text-lg font-bold leading-none">Rumah<span class="text-terracotta">Asa</span></h1>
                        <p class="text-[10px] text-green-200 mt-1 uppercase tracking-wider">{{ auth()->user()?->role?->nama }}</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('dashboard') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Dashboard
                </a>
                <a href="{{ route('rumah.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('rumah.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="list" class="w-4 h-4"></i>
                    Data Rumah
                </a>
                @can('access-verifikator')
                <a href="{{ route('verifikator.survei.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('verifikator.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-terracotta"></i>
                        <span>Antrean Survei</span>
                    </div>
                </a>
                @endcan
                @can('input-rumah')
                <a href="{{ route('rumah.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('rumah.create') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Input Rumah
                </a>
                @endcan
                @can('manage-wilayah')
                <a href="{{ route('wilayah.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('wilayah.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    Wilayah
                </a>
                @endcan
                @can('manage-users')
                <a href="{{ route('users.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('users.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Data User
                </a>
                @endcan
                @can('view-audit-log')
                <a href="{{ route('audit.view') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-forest-light transition {{ request()->routeIs('audit.*') ? 'bg-forest-light font-semibold' : '' }}">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Audit Log
                </a>
                @endcan
            </nav>
            <div class="p-4 border-t border-forest-light">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-green-100 hover:bg-forest-light hover:text-white transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-x-auto">
            <div class="bg-white border-b border-cream-dark px-8 py-4 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <span>RumahAsa</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="font-semibold text-forest-900">@yield('header', 'Dashboard')</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-forest text-white flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()?->nama ?? 'US', 0, 2)) }}
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold text-slate-900 leading-tight">{{ auth()->user()?->nama }}</p>
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
                @if (session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>