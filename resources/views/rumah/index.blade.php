@extends('layouts.app')

@section('title_suffix', ' - Data Rumah')
@section('header', 'Data Rumah')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">Pendataan RTLH
                </p>
                <h1 class="mt-1 text-3xl font-bold text-forest-900">Data Rumah</h1>
                <p class="mt-1 text-sm text-forest-600">Daftar rumah yang sudah diinput oleh admin,
                    lengkap dengan status dan penugasan surveyor.</p>
            </div>
            <a
                href="{{ route('rumah.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest-700 px-5 py-2.5 text-sm font-semibold text-cream-50 shadow-sm hover:bg-forest-800"
            >
                <i
                    data-lucide="plus"
                    class="h-4 w-4"
                ></i>
                Input Data Rumah
            </a>
        </div>

        <div class="rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Total Rumah
                    </p>
                    <p class="text-2xl font-bold text-forest-900">{{ $rumah->total() }}</p>
                </div>
                <div class="border-l border-forest-100 pl-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Menunggu
                        Survei</p>
                    <p class="text-2xl font-bold text-amber-600">
                        {{ \App\Models\RumahWarga::where('status', 'Menunggu Survei')->count() }}</p>
                </div>
                <div class="border-l border-forest-100 pl-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Verifikasi
                    </p>
                    <p class="text-2xl font-bold text-forest-600">
                        {{ \App\Models\RumahWarga::where('status', 'Diajukan')->count() }}</p>
                </div>
                <div class="border-l border-forest-100 pl-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Selesai</p>
                    <p class="text-2xl font-bold text-forest-700">
                        {{ \App\Models\RumahWarga::where('status', 'Selesai')->count() }}</p>
                </div>
            </div>
        </div>

        <form
            method="GET"
            class="grid gap-3 sm:grid-cols-[1fr_auto_auto]"
        >
            <div class="relative">
                <i
                    data-lucide="search"
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-forest-400"
                ></i>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari kode, nama, atau alamat..."
                    class="w-full rounded-xl border border-forest-100 bg-white py-2.5 pl-10 pr-4 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30"
                >
            </div>
            <select
                name="status"
                class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm text-forest-700 focus:border-forest-400 focus:outline-none"
            >
                <option value="">Semua Status</option>
                @foreach ($statusList as $s)
                    <option
                        value="{{ $s }}"
                        @selected(request('status') === $s)
                    >{{ $s }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-forest-700 px-4 py-2.5 text-sm font-medium text-cream-50 hover:bg-forest-800"
                >
                    <i
                        data-lucide="filter"
                        class="h-4 w-4"
                    ></i> Filter
                </button>
                <a
                    href="{{ route('rumah.index') }}"
                    class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm font-medium text-forest-700 hover:bg-cream-50"
                >Reset</a>
            </div>
        </form>

        <div class="rounded-2xl border border-forest-100 bg-white shadow-sm">
            <div class="border-b border-forest-100 px-6 py-4">
                <h3 class="font-bold text-forest-900">Daftar Rumah</h3>
                <p class="text-xs text-forest-500">{{ $rumah->total() }} rumah terdaftar</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-forest-50 text-sm">
                    <thead
                        class="text-left text-xs font-semibold uppercase tracking-wide text-forest-500"
                    >
                        <tr>
                            <th class="px-6 py-3">Kode Reg</th>
                            <th class="px-6 py-3">Kepala Keluarga</th>
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">Surveyor</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-forest-50">
                        @forelse ($rumah as $r)
                            <tr class="hover:bg-cream-50/50">
                                <td class="px-6 py-4">
                                    <code
                                        class="rounded bg-cream-50 px-2 py-0.5 font-mono text-xs text-forest-700"
                                    >{{ $r->kode_registrasi }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="grid h-8 w-8 place-items-center rounded-lg bg-forest-700 text-xs font-bold text-cream-50">
                                            {{ strtoupper(substr($r->nama_kepala_keluarga, 0, 2)) }}
                                        </div>
                                        <span
                                            class="font-semibold text-forest-900">{{ $r->nama_kepala_keluarga }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-forest-600">
                                    {{ \Illuminate\Support\Str::limit($r->alamat, 50) }}</td>
                                <td class="px-6 py-4 text-forest-600">{{ $r->surveyor?->nama ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $r->badgeColorClass() }}"
                                    >
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('rumah.show', $r) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-forest-100 px-2.5 py-1.5 text-xs font-medium text-forest-700 hover:bg-forest-50"
                                    >
                                        <i
                                            data-lucide="eye"
                                            class="h-3.5 w-3.5"
                                        ></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center"
                                >
                                    <div class="flex flex-col items-center gap-2">
                                        <div
                                            class="grid h-12 w-12 place-items-center rounded-full bg-forest-50">
                                            <i
                                                data-lucide="home"
                                                class="h-6 w-6 text-forest-400"
                                            ></i>
                                        </div>
                                        <p class="text-sm font-medium text-forest-700">Belum ada data
                                            rumah</p>
                                        <p class="text-xs text-forest-500">Klik tombol "Input Data
                                            Rumah" untuk menambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rumah->hasPages())
                <div class="flex items-center justify-between border-t border-forest-100 px-6 py-4">
                    <p class="text-xs text-forest-500">Menampilkan {{ $rumah->count() }} dari
                        {{ $rumah->total() }} rumah</p>
                    {{ $rumah->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
