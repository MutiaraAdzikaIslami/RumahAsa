@extends('layouts.app')

@section('title', 'Penerima Bantuan')

@section('content')
@php
    $badgeColors = [
        'Penerima Bantuan' => 'bg-red-100 text-red-700',
        'Dalam Renovasi'   => 'bg-purple-100 text-purple-700',
        'Selesai'          => 'bg-teal-100 text-teal-700',
    ];
@endphp
<div class="space-y-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Penerima Bantuan &amp; Renovasi</h1>
        <p class="text-sm text-slate-500">Rumah yang ditetapkan sebagai penerima bantuan beserta progres renovasinya.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode / Nama KK" class="w-full text-sm border-slate-300 rounded">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full text-sm border-slate-300 rounded">
                    <option value="">Semua</option>
                    @foreach (\App\Modules\Bantuan\Http\Controllers\BantuanController::STATUS_BANTUAN as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium rounded-lg w-full">Filter</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        @if ($rumah->isEmpty())
            <p class="p-4 text-slate-500 text-sm text-center">Belum ada rumah penerima bantuan.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left border-b border-slate-200">
                    <th class="py-2 px-3">Kode</th>
                    <th class="py-2 px-3">Nama KK</th>
                    <th class="py-2 px-3">NIK</th>
                    <th class="py-2 px-3">Wilayah</th>
                    <th class="py-2 px-3">Status</th>
                    <th class="py-2 px-3">Progres</th>
                    <th class="py-2 px-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rumah as $r)
                @php
                    $bantuan = $r->penerimaBantuan;
                    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
                @endphp
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-2 px-3 font-mono text-xs">{{ $r->kode_registrasi }}</td>
                    <td class="py-2 px-3 font-medium">{{ $r->nama_kepala_keluarga }}</td>
                    <td class="py-2 px-3 font-mono text-xs text-slate-500">{{ $r->nik_enc ? $r->nikTersamar() : '-' }}</td>
                    <td class="py-2 px-3 text-xs">{{ $r->wilayah?->nama ?? '-' }}</td>
                    <td class="py-2 px-3">
                        <span class="px-2 py-0.5 rounded text-xs {{ $badgeColors[$r->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $r->status }}</span>
                    </td>
                    <td class="py-2 px-3">
                        @if ($bantuan)
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-slate-200 rounded h-2"><div class="bg-emerald-600 h-2 rounded" style="width: {{ $persen }}%"></div></div>
                                <span class="text-xs">{{ $persen }}%</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-400">Belum disalurkan</span>
                        @endif
                    </td>
                    <td class="py-2 px-3 space-x-2 whitespace-nowrap">
                        {{-- Tautan di bawah aktif otomatis begitu rutenya tersedia (tiket berikutnya) --}}
                        @if (\Illuminate\Support\Facades\Route::has('bantuan.show'))
                            <a href="{{ route('bantuan.show', $r) }}" class="text-emerald-600 text-xs hover:underline">Pantau</a>
                        @else
                            <a href="{{ route('rumah.show', $r) }}" class="text-emerald-600 text-xs hover:underline">Detail</a>
                        @endif
                        @if ($r->status === 'Penerima Bantuan' && \Illuminate\Support\Facades\Route::has('bantuan.salurkan.create'))
                            @can('verifikator')
                                <a href="{{ route('bantuan.salurkan.create', $r) }}" class="text-purple-600 text-xs hover:underline">Salurkan</a>
                            @endcan
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div class="flex justify-center">
        {{ $rumah->links() }}
    </div>
</div>
@endsection
