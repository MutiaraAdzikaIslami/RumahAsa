<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - RumahAsa</title>
    <!-- Pastikan Anda sudah mengonfigurasi Vite untuk Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased h-screen flex">

    <!-- Sisi Kiri: Banner Hijau (Sesuai Desain) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#1B4D3E] text-white flex-col justify-between p-12 relative overflow-hidden">
        <!-- Lingkaran Hiasan Background (Opsional, meniru desain) -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#255C4D] rounded-full -mr-32 -mt-32 opacity-50"></div>
        <div class="absolute bottom-0 right-10 w-64 h-64 bg-[#255C4D] rounded-full -mb-32 opacity-50"></div>

        <div class="relative z-10">
            <!-- Logo -->
            <div class="flex items-center gap-2 mb-16">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L2 12h3v8h14v-8h3L12 3zm0 2.83l7 6.17v6h-4v-4h-6v4H5v-6l7-6.17z"/></svg>
                <span class="text-xl font-bold">RumahAsa</span>
            </div>

            <p class="text-sm font-semibold tracking-wider text-[#A5C9B3] mb-2 uppercase">Sistem Layanan Perumahan Terpadu</p>
            <h1 class="text-5xl font-bold leading-tight mb-6">Pendataan Rumah,<br>Lebih Terarah.</h1>
            <p class="text-[#D0E3D8] text-lg max-w-md">Kelola pendataan, verifikasi, dan pemantauan renovasi rumah warga dalam satu sistem.</p>
        </div>

        <!-- Ilustrasi Rumah & Badge Aman -->
        <div class="relative z-10 mt-auto">
            <!-- Tempat untuk menaruh ilustrasi SVG rumah (Bisa diganti image export dari Figma) -->
            <div class="flex items-end gap-6 mb-8 opacity-80">
                 <!-- Icon Rumah Dummy meniru desain -->
                 <div class="w-12 h-12 bg-[#DCE2DF] clip-path-house"></div>
                 <div class="w-20 h-20 bg-white clip-path-house"></div>
                 <div class="w-16 h-16 bg-[#DCE2DF] clip-path-house"></div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="bg-[#255C4D] p-2 rounded-md">
                    <svg class="w-5 h-5 text-[#A5C9B3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <p class="font-bold text-sm">Data terlindungi</p>
                    <p class="text-xs text-[#D0E3D8]">Akses aman sesuai peran petugas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sisi Kanan: Form Dinamis -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white p-8 sm:p-12 lg:p-24 relative">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
        
        <!-- Footer Form -->
        <div class="absolute bottom-8 text-center text-xs text-gray-400 w-full left-0">
            &copy; {{ date('Y') }} RumahAsa - Pemerintah Kabupaten Sukamaju
        </div>
    </div>

</body>
</html>