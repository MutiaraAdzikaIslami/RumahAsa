@extends('layouts.app')

@section('title', 'Progres Renovasi')

@section('content')
@php
    $dalamRenovasi = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI;
@endphp
<div class="space-y-4 max-w-4xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Progres Renovasi</h1>
            <p class="text-sm text-slate-500">{{ $rumah->kode_registrasi }} &middot; {{ $rumah->nama_kepala_keluarga }} &middot; {{ $rumah->status }}</p>
        </div>
        <div class="space-x-2">
            @can('verifikator')
                @if ($dalamRenovasi)
                    <a href="{{ route('bantuan.progres.create', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">+ Tambah Progres</a>
                @endif
            @endcan
            <a href="{{ route('bantuan.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Kembali</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        @if ($daftar->isEmpty())
            <p class="p-4 text-slate-500 text-sm text-center">Belum ada progres yang dicatat.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left border-b border-slate-200">
                    <th class="py-2 px-3">Tanggal</th>
                    <th class="py-2 px-3">Tahap Pekerjaan</th>
                    <th class="py-2 px-3">Progres</th>
                    <th class="py-2 px-3">Catatan</th>
                    <th class="py-2 px-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftar as $p)
                <tr class="border-b border-slate-100">
                    <td class="py-2 px-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($p->tanggal_progres)->format('d/m/Y') }}</td>
                    <td class="py-2 px-3 font-medium">{{ $p->tahap_pekerjaan }}</td>
                    <td class="py-2 px-3">{{ $p->persentase }}%</td>
                    <td class="py-2 px-3 text-slate-500">{{ $p->catatan ?? '-' }}</td>
                    <td class="py-2 px-3 whitespace-nowrap">
                        @can('verifikator')
                            @if ($dalamRenovasi)
                                <a href="{{ route('bantuan.progres.edit', [$rumah, $p]) }}" class="text-emerald-600 text-xs hover:underline">Ubah</a>
                                <form method="POST" action="{{ route('bantuan.progres.destroy', [$rumah, $p]) }}" class="inline" onsubmit="return confirm('Hapus progres ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-xs hover:underline ml-2">Hapus</button>
                                </form>
                            @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
