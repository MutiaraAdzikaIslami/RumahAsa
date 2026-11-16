@extends('layouts.app')

@section('title', 'Catat Penyaluran Bantuan')

@section('content')
<div class="space-y-4 max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900">Catat Penyaluran Bantuan</h1>

    <div class="bg-white rounded-lg shadow p-4 text-sm">
        <p><span class="text-slate-500">Kode:</span> <span class="font-mono">{{ $rumah->kode_registrasi }}</span></p>
        <p><span class="text-slate-500">Kepala keluarga:</span> <span class="font-medium">{{ $rumah->nama_kepala_keluarga }}</span></p>
        <p><span class="text-slate-500">Alamat:</span> {{ $rumah->alamat }}</p>
    </div>

    @if ($errors->any())
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('bantuan.salurkan.store', $rumah) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Penyaluran <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_bantuan" value="{{ old('tanggal_bantuan', now()->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Bantuan <span class="text-red-500">*</span></label>
            <input type="text" name="jenis_bantuan" value="{{ old('jenis_bantuan') }}" maxlength="100" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. Bantuan Stimulan Perumahan Swadaya">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="nominal" value="{{ old('nominal') }}" min="0" step="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nomor SK <span class="text-red-500">*</span></label>
            <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" maxlength="100" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <p class="text-xs text-slate-500">Setelah disimpan, status rumah berubah menjadi Dalam Renovasi.</p>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Simpan Penyaluran</button>
            <a href="{{ route('bantuan.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Batal</a>
        </div>
    </form>
</div>
@endsection
