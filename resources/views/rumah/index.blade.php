@extends('layouts.app')

@section('title', 'Daftar Rumah')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Daftar Rumah</h1>
        @can('input-rumah')
        <a href="{{ route('rumah.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">+ Input Rumah</a>
        @endcan
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode / Nama KK" class="w-full text-sm border-slate-300 rounded">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Wilayah</label>
                <select name="wilayah_id" class="w-full text-sm border-slate-300 rounded">
                    <option value="">Semua</option>
                    @foreach ($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>[{{ strtoupper($w->tingkat) }}] {{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full text-sm border-slate-300 rounded">
                    <option value="">Semua</option>
                    @foreach (\App\Models\RumahWarga::STATUS_LIST as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium rounded-lg w-full">Filter</button>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        @if ($rumah->isEmpty())
            <p class="p-4 text-slate-500 text-sm text-center">Belum ada data rumah.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left border-b border-slate-200">
                    <th class="py-2 px-3">Kode</th>
                    <th class="py-2 px-3">Nama KK</th>
                    <th class="py-2 px-3">NIK</th>
                    <th class="py-2 px-3">Wilayah</th>
                    <th class="py-2 px-3">Status</th>
                    <th class="py-2 px-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rumah as $r)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-2 px-3 font-mono text-xs">{{ $r->kode_registrasi }}</td>
                    <td class="py-2 px-3 font-medium">{{ $r->nama_kepala_keluarga }}</td>
                    <td class="py-2 px-3 font-mono text-xs text-slate-500">
                        @if ($r->nik_enc)
                            @can('view-audit-log')
                                {{ app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNik($r->nik_enc) }}
                            @else
                                {{ app(\App\Modules\Core\Services\NikEncryptor::class)->tersamar($r->nik_enc) }}
                            @endcan
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-2 px-3 text-xs">{{ $r->wilayah?->nama ?? '-' }}</td>
                    <td class="py-2 px-3">
                        @php
                            $badgeColors = [
                                'Draf' => 'bg-slate-100 text-slate-700',
                                'Diajukan' => 'bg-blue-100 text-blue-700',
                                'Perlu Perbaikan' => 'bg-amber-100 text-amber-700',
                                'Layak Huni' => 'bg-emerald-100 text-emerald-700',
                                'Penerima Bantuan' => 'bg-red-100 text-red-700',
                                'Dalam Renovasi' => 'bg-purple-100 text-purple-700',
                                'Selesai' => 'bg-teal-100 text-teal-700',
                            ];
                            $color = $badgeColors[$r->status] ?? 'bg-slate-100 text-slate-700';
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs {{ $color }}">{{ $r->status }}</span>
                    </td>
                    <td class="py-2 px-3 whitespace-nowrap space-x-2">
                        <a href="{{ route('rumah.show', $r) }}" class="text-emerald-600 text-xs hover:underline">Detail</a>
                        @if ($r->status === \App\Models\RumahWarga::STATUS_DIAJUKAN && auth()->user()?->isVerifikator())
                            <a href="{{ route('verifikator.survei.show', $r) }}" class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold rounded shadow-sm">
                                Review
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $rumah->links() }}
    </div>
</div>
@endsection