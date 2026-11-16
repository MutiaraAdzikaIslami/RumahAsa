@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            {{ isset($rumah) ? 'Edit Data Rumah' : 'Input Data Rumah' }}
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            @if (isset($rumah))
                Perbarui data rumah {{ $rumah->kode_registrasi }}
            @else
                Lengkapi data rumah warga untuk pendataan RTLH
            @endif
        </p>
    </div>

    <livewire:rumah-form :rumah-id="isset($rumah) ? $rumah->id : null" />
</div>
@endsection
