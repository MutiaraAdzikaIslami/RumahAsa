@extends('layouts.app')

@section('title', 'Pantau Renovasi')

@section('content')
@php
    $badgeColors = [
        'Penerima Bantuan' => 'bg-red-100 text-red-700',
        'Dalam Renovasi'   => 'bg-purple-100 text-purple-700',
        'Selesai'          => 'bg-teal-100 text-teal-700',
    ];
    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
    $bisaSelesai = $rumah->status === \App\Models\RumahWarga::STATUS_DALAM_RENOVASI && $persen >= 100;
@endphp
<div class="space-y-4 max-w-4xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pantau Renovasi</h1>
            <p class="text-sm text-slate-500">{{ $rumah->kode_registrasi }} &middot; {{ $rumah->nama_kepala_keluarga }}</p>
        </div>
        <div class="space-x-2">
            @if ($bantuan)
                <a href="{{ route('bantuan.progres.index', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Kelola Progres</a>
            @endif
            <a href="{{ route('bantuan.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 space-y-3">
        <div class="flex items-center justify-between">
            <span class="px-2 py-0.5 rounded text-xs {{ $badgeColors[$rumah->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $rumah->status }}</span>
            <span class="text-sm text-slate-500">{{ $rumah->wilayah?->nama ?? '-' }} &middot; {{ $rumah->alamat }}</span>
        </div>
        @if ($bantuan)
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="font-medium text-slate-700">Progres renovasi</span>
                    <span class="font-semibold">{{ $persen }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded h-3"><div class="bg-emerald-600 h-3 rounded" style="width: {{ $persen }}%"></div></div>
            </div>
        @else
            <p class="text-sm text-slate-500">Bantuan belum disalurkan.</p>
        @endif
    </div>

    @if ($bantuan)
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Penyaluran Bantuan</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div><dt class="text-slate-500">Jenis</dt><dd>{{ $bantuan->jenis_bantuan }}</dd></div>
            <div><dt class="text-slate-500">Nominal</dt><dd>Rp {{ number_format((float) $bantuan->nominal, 0, ',', '.') }}</dd></div>
            <div><dt class="text-slate-500">Tanggal</dt><dd>{{ $bantuan->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan)->format('d/m/Y') : '-' }}</dd></div>
            <div><dt class="text-slate-500">Nomor SK</dt><dd>{{ $bantuan->nomor_sk }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Riwayat Progres</h2>
        @if ($bantuan->progres->isEmpty())
            <p class="text-sm text-slate-500">Belum ada progres yang dicatat.</p>
        @else
            <ul class="space-y-2 text-sm">
                @foreach ($bantuan->progres as $p)
                    <li class="flex items-center justify-between border-b border-slate-100 pb-1">
                        <span>{{ \Carbon\Carbon::parse($p->tanggal_progres)->format('d/m/Y') }} &middot; {{ $p->tahap_pekerjaan }}</span>
                        <span class="font-medium">{{ $p->persentase }}%</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif

    @can('verifikator')
        @if ($bisaSelesai)
            <form method="POST" action="{{ route('bantuan.selesai', $rumah) }}" class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 space-y-2" onsubmit="return confirm('Tetapkan rumah ini Selesai? Progres akan terkunci.')">
                @csrf
                <p class="text-sm text-emerald-800">Progres sudah 100%. Rumah dapat ditetapkan Selesai.</p>
                <textarea name="catatan" rows="2" maxlength="500" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Catatan (opsional)"></textarea>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Tetapkan Selesai</button>
            </form>
        @endif
    @endcan

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Riwayat Status</h2>
        <ul class="space-y-1 text-sm">
            @foreach ($rumah->riwayatStatus->sortByDesc('id') as $r)
                <li>{{ $r->created_at?->format('d/m/Y H:i') }} &middot; {{ $r->status_dari }} &rarr; {{ $r->status_ke }}@if ($r->catatan) <span class="text-slate-500">({{ $r->catatan }})</span>@endif</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
