<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RumahAsa') }} — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-slate-800 text-slate-100 flex-shrink-0 hidden md:flex flex-col" x-data="{ open: false }">
            <div class="p-4 border-b border-slate-700">
                <h1 class="text-lg font-bold">RumahAsa</h1>
                <p class="text-xs text-slate-400 mt-1">{{ auth()->user()?->role?->nama }}</p>
            </div>
            <nav class="flex-1 p-2 space-y-1">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded text-sm hover:bg-slate-700 {{ request()->routeIs('dashboard') ? 'bg-slate-700' : '' }}">Dashboard</a>
                @can('manage-wilayah')
                <a href="{{ route('wilayah.view') }}" class="block px-3 py-2 rounded text-sm hover:bg-slate-700 {{ request()->routeIs('wilayah.*') ? 'bg-slate-700' : '' }}">Kelola Wilayah</a>
                @endcan
            </nav>
            <div class="p-2 border-t border-slate-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded text-sm hover:bg-slate-700">Logout</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 overflow-x-auto">
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 p-3 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="mb-4 p-3 rounded bg-amber-50 border border-amber-200 text-amber-700 text-sm">{{ session('warning') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>