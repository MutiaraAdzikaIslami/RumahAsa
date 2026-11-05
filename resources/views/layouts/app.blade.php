<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RumahAsa - Dashboard')</title>
    
    <!-- Alpine.js untuk interaksi Dropdown & Modal -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Memuat Tailwind CSS melalui Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans flex h-screen overflow-hidden">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-[#1B4D3E] text-white flex flex-col h-full flex-shrink-0">
        <!-- Logo -->
        <div class="p-6 flex items-center gap-3">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L2 12h3v8h14v-8h3L12 3zm0 2.83l7 6.17v6h-4v-4h-6v4H5v-6l7-6.17z"/></svg>
            <span class="text-2xl font-bold tracking-wide">RumahAsa</span>
        </div>

        <!-- Profil Singkat di Sidebar -->
        <div class="px-6 mb-6">
            <div class="bg-[#255C4D] rounded-lg p-3 flex items-center gap-3 border border-[#3A7363]">
                <div class="w-10 h-10 rounded-md bg-white text-[#1B4D3E] font-bold flex items-center justify-center text-sm">
                    {{ strtoupper(substr(auth()->user()->nama ?? 'BS', 0, 2)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold truncate">{{ auth()->user()->nama ?? 'Administrator' }}</p>
                    <p class="text-[10px] text-[#A5C9B3] truncate">{{ auth()->user()->wilayah->nama ?? 'Kabupaten Sukamaju' }}</p>
                </div>
            </div>
        </div>

        <!-- Menu Navigasi -->
        <nav class="flex-1 px-4 space-y-1 overflow-y-auto mt-2">
            <p class="px-2 text-[10px] font-semibold text-[#A5C9B3] tracking-wider uppercase mb-2">Menu Utama</p>
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-[#255C4D] text-white' : 'text-[#D0E3D8] hover:bg-[#255C4D] hover:text-white transition-colors' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            
            <!-- Menu Data User (Aktif jika rutenya adalah admin.users.*) -->
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-[#255C4D] text-white border-l-4 border-white' : 'text-[#D0E3D8] hover:bg-[#255C4D] hover:text-white transition-colors' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Data User
            </a>
            
            <!-- Tambahkan link menu lain di sini (Data Rumah, Laporan, dll) -->
        </nav>

        <!-- Bagian Bawah Sidebar -->
        <div class="p-4 border-t border-[#255C4D]">
            <!-- Tombol Bantuan -->
            <div class="bg-[#153D31] rounded-lg p-4 mb-4 border border-[#255C4D]">
                <div class="flex items-center gap-2 text-amber-400 mb-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span class="font-bold text-sm">Butuh bantuan?</span>
                </div>
                <p class="text-xs text-[#A5C9B3]">Hubungi admin wilayah</p>
            </div>

            <!-- Tombol Keluar / Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2.5 w-full text-left rounded-lg text-sm font-medium text-[#D0E3D8] hover:bg-[#255C4D] hover:text-white transition-colors">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- AREA KONTEN KANAN -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-white">
        
        <!-- Header Atas (Topbar) -->
        <header class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-8 flex-shrink-0">
            
            <!-- Breadcrumbs -->
            <div class="flex items-center text-sm">
                <span class="text-gray-400">RumahAsa</span>
                <svg class="w-4 h-4 mx-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-semibold text-gray-900">@yield('breadcrumb', 'Dashboard')</span>
            </div>

            <!-- Bagian Kanan Topbar (Pencarian & Profil) -->
            <div class="flex items-center gap-6">
                <!-- Search Bar Placeholder -->
                <div class="relative hidden md:block">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Cari data rumah..." class="bg-gray-50 border border-gray-200 rounded-full pl-9 pr-4 py-1.5 text-sm w-64 focus:outline-none focus:ring-1 focus:ring-[#1B4D3E]">
                </div>

                <!-- Notifikasi -->
                <button class="text-gray-400 hover:text-gray-600 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                </button>

                <!-- Dropdown Profil Kanan Atas -->
                <div class="flex items-center gap-3 border-l border-gray-200 pl-6 cursor-pointer">
                    <div class="w-8 h-8 rounded-md bg-[#1B4D3E] text-white font-bold flex items-center justify-center text-xs">
                         {{ strtoupper(substr(auth()->user()->nama ?? 'BS', 0, 2)) }}
                    </div>
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-gray-900 leading-tight">{{ auth()->user()->nama ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role->nama_role ?? 'Administrator' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </header>

        <!-- Area Konten Utama Dinamis (Diambil dari View Manajemen User) -->
        <div class="flex-1 overflow-y-auto">
            @yield('content')
        </div>
        
    </main>

</body>
</html>