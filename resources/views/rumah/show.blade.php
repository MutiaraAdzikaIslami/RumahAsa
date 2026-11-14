@extends('layouts.app')

@section('title', 'Detail Rumah')

@section('content')
<div class="space-y-4 max-w-4xl">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Detail Rumah</h1>
        <div class="space-x-2">
            @if ($rumah->bisaDiedit())
                <a href="{{ route('rumah.edit', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Edit Rumah</a>
            @endif
            <a href="{{ route('rumah.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Kembali</a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-4">
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
            $color = $badgeColors[$rumah->status] ?? 'bg-slate-100 text-slate-700';
        @endphp
        <div class="flex items-center gap-4">
            <span class="font-mono text-sm text-slate-500">{{ $rumah->kode_registrasi }}</span>
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $color }}">{{ $rumah->status }}</span>
        </div>
    </div>

    {{-- Data Warga --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Data Warga</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-500 text-xs">Nama Kepala Keluarga</p>
                <p class="font-medium">{{ $rumah->nama_kepala_keluarga ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">NIK</p>
                <p class="font-mono">
                    @if ($rumah->nik_enc)
                        @can('view-audit-log')
                            {{ app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNik($rumah->nik_enc) }}
                        @else
                            {{ app(\App\Modules\Core\Services\NikEncryptor::class)->tersamar($rumah->nik_enc) }}
                        @endcan
                    @else
                        -
                    @endif
                </p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">No. Kartu Keluarga</p>
                <p class="font-mono">
                    @if ($rumah->no_kk_enc)
                        @can('view-audit-log')
                            {{ app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNoKk($rumah->no_kk_enc) }}
                        @else
                            {{ substr(app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNoKk($rumah->no_kk_enc), 0, 4) . '********' . substr(app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNoKk($rumah->no_kk_enc), -4) }}
                        @endcan
                    @else
                        -
                    @endif
                </p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Jumlah Anggota</p>
                <p>{{ $rumah->jumlah_anggota ?? '-' }} orang</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Pekerjaan</p>
                <p>{{ $rumah->pekerjaan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Penghasilan</p>
                <p>{{ $rumah->penghasilan ? 'Rp ' . number_format($rumah->penghasilan) : '-' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Status Kepemilikan</p>
                <p>{{ str_replace('_', ' ', ucfirst($rumah->status_kepemilikan)) }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Luas Rumah</p>
                <p>{{ $rumah->luas_rumah ? $rumah->luas_rumah . ' m²' : '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Data Lokasi --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Lokasi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-500 text-xs">Wilayah</p>
                <p class="font-medium">{{ $rumah->wilayah?->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Alamat</p>
                <p>{{ $rumah->alamat ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Latitude</p>
                <p class="font-mono">{{ $rumah->latitude ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs">Longitude</p>
                <p class="font-mono">{{ $rumah->longitude ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Info Survei --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Survei Kelayakan</h2>
            @if ($rumah->bisaDiedit())
                <a href="{{ route('survei.create', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Isi Survei</a>
            @endif
        </div>
        @if ($rumah->survei->isEmpty())
            <p class="text-slate-500 text-sm">Belum ada survei.</p>
        @else
            <p class="text-sm text-slate-600">Survei sudah dibuat. Klik "Isi Survei" untuk melanjutkan pengisian.</p>
        @endif
    </div>

    {{-- Riwayat Status --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Riwayat Status</h2>
        @if ($rumah->riwayatStatus->isEmpty())
            <p class="text-slate-500 text-sm">Belum ada riwayat perubahan status.</p>
        @else
            <ul class="space-y-2">
                @foreach ($rumah->riwayatStatus as $riwayat)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="text-slate-400 text-xs font-mono whitespace-nowrap">{{ $riwayat->created_at->format('d/m/Y H:i') }}</span>
                        <span class="text-slate-600">{{ $riwayat->status_dari }} → <strong>{{ $riwayat->status_ke }}</strong></span>
                        @if ($riwayat->catatan)
                            <span class="text-slate-400 text-xs">({{ $riwayat->catatan }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection