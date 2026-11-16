<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TITLE: pakai @yield('title') bukan @yield('title_suffix') — lebih robust --}}
    <title>@yield('title', 'RumahAsa')</title>

    {{-- FAVICON dengan cache buster (force browser reload saat file berubah) --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}?v=2">

    <meta name="application-name" content="RumahAsa">
    <meta name="description" content="RumahAsa - Sistem Informasi Pendataan Rumah Tidak Layak Huni">
    <meta name="theme-color" content="#1d3320">
    <meta name="color-scheme" content="light">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        forest: {
                            50: '#f3f7f3', 100: '#e3ece1', 200: '#c8d8c4',
                            300: '#a0b89c', 400: '#6d8b68', 500: '#4a6a45',
                            600: '#355630', 700: '#1d3320', 800: '#162819', 900: '#0f1c10',
                        },
                        cream: { 50: '#fdfbf6', 100: '#faf5e8', 200: '#f3ead0' },
                        gold: { 400: '#e6b35a', 500: '#d99f3a', 600: '#b8862a' },
                        clay: { 100: '#f5e3c8', 600: '#9a5333' },
                    },
                },
            },
        }
    </script>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @stack('head')
</head>
<body class="h-full bg-cream-50 font-sans text-forest-900 antialiased">
@auth
<div class="flex min-h-screen">
    {{-- SIDEBAR --}}
    <aside class="hidden w-72 shrink-0 flex-col bg-forest-700 text-cream-100 lg:flex">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-forest-600/50">
            <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-600">
                <svg class="h-6 w-6 text-cream-50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 3l9 6.5V21a1 1 0 01-1 1h-5v-7h-6v7H4a1 1 0 01-1-1V9.5z" />
                </svg>
            </div>
            <div>
                <p class="text-lg font-extrabold leading-tight">Rumah<span class="text-gold-500">Asa</span></p>
                <p class="text-[10px] uppercase tracking-wider text-cream-100/60">Sistem Pendataan RTLH</p>
            </div>
        </div>

        <div class="mx-4 mt-4 rounded-xl bg-forest-600/60 p-3">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-lg bg-forest-500 text-sm font-bold text-cream-50">
                    {{ strtoupper(substr(auth()->user()->nama, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-cream-50">{{ auth()->user()->nama }}</p>
                    <p class="truncate text-[11px] text-cream-100/60">{{ ucfirst(auth()->user()->role->kode ?? 'user') }} &middot; Akses penuh</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <p class="px-3 py-2 text-[10px] font-semibold uppercase tracking-wider text-cream-100/40">Menu Utama</p>
            <ul class="space-y-1 text-sm">
                @php
                    $menuItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'gate' => null],
                        ['route' => 'rumah.index', 'label' => 'Data Rumah', 'icon' => 'home', 'gate' => 'manage-users'],
                        ['route' => 'users.index', 'label' => 'Data User', 'icon' => 'users', 'gate' => 'manage-users'],
                        ['route' => 'wilayah.index', 'label' => 'Wilayah', 'icon' => 'map', 'gate' => 'manage-users'],
                        ['route' => 'survei.index', 'label' => 'Tugas Survei', 'icon' => 'clipboard-list', 'gate' => 'access-surveyor'],
                    ];
                @endphp
                @foreach ($menuItems as $item)
                    @if (!$item['gate'] || \Illuminate\Support\Facades\Gate::forUser(auth()->user())->allows($item['gate']))
                        @php
                            $isActive = $item['route'] === 'dashboard'
                                ? request()->routeIs('dashboard')
                                : request()->routeIs(explode('.', $item['route'])[0] . '*');
                        @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ $isActive ? 'bg-forest-600 text-cream-50 font-semibold' : 'text-cream-100/70 hover:bg-forest-600/60 hover:text-cream-50' }}">
                                @if ($isActive)
                                    <span class="absolute left-0 top-1/2 h-7 w-1 -translate-y-1/2 rounded-r bg-gold-500"></span>
                                @endif
                                <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 {{ $isActive ? 'text-gold-500' : 'text-cream-100/60' }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        <div class="mx-4 mb-4 rounded-xl bg-forest-600/60 p-3">
            <div class="flex items-start gap-3">
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gold-500/20">
                    <i data-lucide="life-buoy" class="h-4 w-4 text-gold-500"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-cream-50">Butuh bantuan?</p>
                    <p class="text-[11px] text-cream-100/60">Pusat bantuan &amp; panduan</p>
                </div>
            </div>
        </div>

        <div class="border-t border-forest-600/50 px-3 py-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-cream-100/70 transition hover:bg-forest-600/60 hover:text-cream-50">
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex flex-1 flex-col overflow-hidden">
        <header class="flex h-16 shrink-0 items-center gap-4 border-b border-forest-100 bg-white px-6">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('dashboard') }}" class="text-forest-500 hover:text-forest-700">RumahAsa</a>
                <i data-lucide="chevron-right" class="h-4 w-4 text-forest-300"></i>
                <span class="font-semibold text-forest-900">@yield('header', 'Dashboard')</span>
            </nav>

            <div class="flex-1"></div>

            <div class="relative hidden md:block">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-forest-400"></i>
                <input type="text" placeholder="Cari data rumah..."
                       class="w-72 rounded-full border border-forest-100 bg-cream-50 py-2 pl-10 pr-4 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
            </div>

            <button class="relative grid h-10 w-10 place-items-center rounded-full border border-forest-100 bg-white hover:bg-cream-50">
                <i data-lucide="bell" class="h-5 w-5 text-forest-700"></i>
                <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>

            <div class="flex items-center gap-3 rounded-full border border-forest-100 bg-white px-2 py-1.5">
                <div class="grid h-8 w-8 place-items-center rounded-full bg-forest-700 text-xs font-bold text-cream-50">
                    {{ strtoupper(substr(auth()->user()->nama, 0, 2)) }}
                </div>
                <div class="hidden text-left sm:block">
                    <p class="text-xs font-semibold text-forest-900">{{ auth()->user()->nama }}</p>
                    <p class="text-[10px] uppercase text-forest-500">{{ auth()->user()->role->kode ?? '' }}</p>
                </div>
                <i data-lucide="chevron-down" class="h-4 w-4 text-forest-400"></i>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto px-6 py-6 lg:px-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

@stack('scripts')
</body>
</html>