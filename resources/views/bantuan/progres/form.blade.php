@extends('layouts.app')

@section('title', $progres ? 'Ubah Progres Renovasi' : 'Tambah Progres Renovasi')

@section('content')
<div class="space-y-4 max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">{{ $progres ? 'Ubah Progres Renovasi' : 'Tambah Progres Renovasi' }}</h1>
    <p class="text-sm text-slate-500">{{ $rumah->kode_registrasi }} &middot; {{ $rumah->nama_kepala_keluarga }}</p>

    @if ($errors->any())
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ $progres ? route('bantuan.progres.update', [$rumah, $progres]) : route('bantuan.progres.store', $rumah) }}"
          class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        @if ($progres)
            @method('PUT')
        @endif
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tahap Pekerjaan <span class="text-red-500">*</span></label>
            <input type="text" name="tahap_pekerjaan" value="{{ old('tahap_pekerjaan', $progres?->tahap_pekerjaan) }}" maxlength="150" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. Pondasi, Dinding, Atap">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Progres (%) <span class="text-red-500">*</span></label>
                <input type="number" name="persentase" value="{{ old('persentase', $progres?->persentase) }}" min="0" max="100" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_progres" value="{{ old('tanggal_progres', $progres?->tanggal_progres ?? now()->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
            <textarea name="catatan" rows="3" maxlength="1000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('catatan', $progres?->catatan) }}</textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Simpan</button>
            <a href="{{ route('bantuan.progres.index', $rumah) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Batal</a>
        </div>
    </form>
</div>
@endsection
