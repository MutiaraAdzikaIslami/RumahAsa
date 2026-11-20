@extends($mustChange ? 'layouts.guest' : 'layouts.app')

@section('title', $mustChange ? 'Ganti Password' : 'Profil')

@section('content')
@php
    $u = auth()->user();
    $roleKode = $u?->role?->kode ?? 'user';
    $nomorPetugas = match ($roleKode) {
        'surveyor'    => 'SVY',
        'verifikator' => 'VRF',
        'admin'       => 'ADM',
        'pimpinan'    => 'PMP',
        default       => 'USR',
    } . '-' . date('Y') . '-' . str_pad((string) ($u?->id ?? 1), 4, '0', STR_PAD_LEFT);

    $jabatanLabel = match ($roleKode) {
        'surveyor'    => 'Surveyor Lapangan',
        'verifikator' => 'Verifikator Kelayakan',
        'admin'       => 'Administrator Sistem',
        'pimpinan'    => 'Pimpinan Eksekutif',
        default       => $u?->role?->nama ?? 'Petugas',
    };

    $wilayahTugasLengkap = $u?->wilayah
        ? $u->wilayah->namaLengkap()
        : ($roleKode === 'admin' ? 'Akses Penuh Seluruh Wilayah' : 'Seluruh Wilayah');

    $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $nowWib = now()->timezone('Asia/Jakarta');
    $terakhirMasukStr = $nowWib->day . ' ' . $bulanIndo[$nowWib->month] . ' ' . $nowWib->year . ', ' . $nowWib->format('H.i') . ' WIB';

    $initialOpenForm = ($mustChange || $errors->any() || request()->boolean('ubah'));
@endphp

<div x-data="{
    openPasswordForm: {{ $initialOpenForm ? 'true' : 'false' }},
    showCurrent: false,
    showPassword: false,
    showConfirm: false
}" class="space-y-6">

    {{-- TAMPILAN PROFIL SAYA (Hanya tampil jika bukan mode wajib ganti password pertama kali) --}}
    @if (!$mustChange)
        {{-- Header Profil Sesuai Mockup Page 8 --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-amber-600">Akun Petugas</p>
                <h1 class="text-3xl font-extrabold text-slate-900 mt-1">Profil Saya</h1>
                <p class="text-sm text-slate-500 mt-1">Informasi akun dan cakupan wilayah penugasan Anda.</p>
            </div>

            <button
                type="button"
                @click="openPasswordForm = !openPasswordForm"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-slate-200/90 hover:border-forest hover:bg-slate-50 text-xs font-bold text-slate-800 shadow-xs transition self-start sm:self-auto"
            >
                <i data-lucide="key-round" class="w-4 h-4 text-forest"></i>
                <span x-text="openPasswordForm ? 'Tutup Form Password' : 'Ubah Password'">Ubah Password</span>
            </button>
        </div>

        @if (session('status') || session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>{{ session('status') ?? session('success') }}</span>
            </div>
        @endif

        {{-- Grid 2 Kartu Profil Sesuai Mockup Page 8 --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            {{-- Kartu Kiri: Avatar, Nama, Jabatan, Status Akun --}}
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 p-8 shadow-xs flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 rounded-3xl bg-forest text-white flex items-center justify-center text-3xl font-extrabold shadow-sm">
                    {{ strtoupper(substr($u?->nama ?? 'RA', 0, 2)) }}
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-5">{{ $u?->nama }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $jabatanLabel }}</p>

                <span class="mt-4 inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-100/80 text-forest border border-emerald-200">
                    {{ $u?->is_aktif ? 'Akun aktif' : 'Nonaktif' }}
                </span>
            </div>

            {{-- Kartu Kanan: Informasi Petugas --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 p-8 shadow-xs flex flex-col justify-between">
                <h3 class="text-lg font-extrabold text-slate-900 mb-4">Informasi Petugas</h3>

                <div class="divide-y divide-slate-100 text-sm">
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <span class="text-slate-400 text-xs font-medium">Email</span>
                        <span class="font-bold text-slate-900">{{ $u?->email ?? '-' }}</span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <span class="text-slate-400 text-xs font-medium">Nomor petugas</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $nomorPetugas }} <span class="text-slate-400 font-sans font-normal text-xs">({{ $u?->username }})</span></span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <span class="text-slate-400 text-xs font-medium">Wilayah tugas</span>
                        <span class="font-bold text-slate-900 sm:text-right max-w-md">{{ $wilayahTugasLengkap }}</span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <span class="text-slate-400 text-xs font-medium">Terakhir masuk</span>
                        <span class="font-bold text-slate-900">{{ $terakhirMasukStr }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM UBAH PASSWORD (Hanya muncul saat tombol Ubah Password diklik ATAU wajib ganti password pertama kali) --}}
    <div x-show="openPasswordForm" x-cloak class="bg-white rounded-3xl shadow-sm p-8 border border-slate-200/90 max-w-2xl {{ $mustChange ? 'mx-auto shadow-xl' : '' }}">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3.5">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 text-forest">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">Ganti Password</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui password akun Anda untuk menjaga keamanan data.</p>
                </div>
            </div>

            @if (!$mustChange)
                <button type="button" @click="openPasswordForm = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            @endif
        </div>

        @if ($mustChange || session('warning'))
            <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="font-semibold text-amber-900">Perhatian: Wajib Mengganti Password</p>
                    <p class="mt-0.5 text-xs text-amber-700">
                        {{ session('warning') ?? 'Anda menggunakan password sementara yang dibuatkan Admin. Anda wajib mengganti password sebelum dapat mengakses fitur sistem lainnya.' }}
                    </p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
                <p class="font-semibold mb-1 text-red-800">Gagal memperbarui password:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Password Lama --}}
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">
                    Password Lama / Sementara <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        :type="showCurrent ? 'text' : 'password'"
                        id="current_password"
                        name="current_password"
                        required
                        class="w-full px-4 py-2.5 pr-10 rounded-xl border border-slate-300 focus:border-forest focus:ring-2 focus:ring-forest/20 outline-none transition text-slate-800 text-sm"
                        placeholder="Masukkan password saat ini"
                    >
                    <button
                        type="button"
                        @click="showCurrent = !showCurrent"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        tabindex="-1"
                    >
                        <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showCurrent" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-2.5 pr-10 rounded-xl border border-slate-300 focus:border-forest focus:ring-2 focus:ring-forest/20 outline-none transition text-slate-800 text-sm"
                        placeholder="8-64 karakter huruf & angka"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        tabindex="-1"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        :type="showConfirm ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full px-4 py-2.5 pr-10 rounded-xl border border-slate-300 focus:border-forest focus:ring-2 focus:ring-forest/20 outline-none transition text-slate-800 text-sm"
                        placeholder="Ulangi password baru"
                    >
                    <button
                        type="button"
                        @click="showConfirm = !showConfirm"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        tabindex="-1"
                    >
                        <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Ketentuan Kebijakan Password --}}
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                <p class="font-semibold text-slate-800">Ketentuan Password:</p>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Panjang 8 sampai 64 karakter</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Hanya kombinasi huruf dan angka (alfanumerik)</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tidak boleh mengandung spasi atau simbol khusus (@, #, $, dll)</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tidak boleh sama dengan password lama</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                @if (!$mustChange)
                    <button
                        type="button"
                        @click="openPasswordForm = false"
                        class="py-2.5 px-4 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition"
                    >
                        Batal
                    </button>
                @endif
                <button
                    type="submit"
                    class="{{ $mustChange ? 'w-full' : '' }} py-2.5 px-5 bg-forest hover:bg-forest-light text-white text-xs font-bold rounded-xl transition shadow-xs"
                >
                    Simpan Password Baru
                </button>
            </div>
        </form>

        @if ($mustChange)
            <div class="mt-6 pt-5 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <span class="text-slate-400">Ganti password wajib diselesaikan</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                        Keluar
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection
