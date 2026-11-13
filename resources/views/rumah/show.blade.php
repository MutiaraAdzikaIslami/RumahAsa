@extends('layouts.app')

@section('title', 'Detail Rumah')

@section('content')
<div class="space-y-4 max-w-4xl">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Detail Rumah</h1>
        <div class="space-x-2">
            @if ($rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN && auth()->user()?->isVerifikator())
                <a href="{{ route('verifikator.survei.show', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm inline-flex items-center gap-1.5">
                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                    Review Survei
                </a>
            @endif
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
            <div class="space-x-2">
                @if ($rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN && auth()->user()?->isVerifikator())
                    <a href="{{ route('verifikator.survei.show', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm inline-flex items-center gap-1.5">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        Review Survei
                    </a>
                @endif
                @if ($rumah->bisaDiedit())
                    <a href="{{ route('survei.create', $rumah) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Isi Survei</a>
                @endif
            </div>
        </div>
        @if ($rumah->survei->isEmpty())
            <p class="text-slate-500 text-sm">Belum ada survei.</p>
        @else
            @if ($rumah->status === \App\Models\RumahWarga::STATUS_DIAJUKAN)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-xs">Survei Berstatus Diajukan</p>
                            <p class="text-xs text-blue-700 mt-0.5">Survei kelayakan 7 komponen telah diajukan dan menunggu peninjauan Verifikator.</p>
                        </div>
                    </div>
                    @if (auth()->user()?->isVerifikator())
                        <a href="{{ route('verifikator.survei.show', $rumah) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 hover:text-blue-900 underline whitespace-nowrap">
                            Buka Halaman Review &rarr;
                        </a>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-600">Survei sudah dibuat. Klik "Isi Survei" untuk melanjutkan pengisian.</p>
            @endif
        @endif
    </div>

    {{-- Riwayat Status (FR-24: SCRUM-108 & SCRUM-109) --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Status</h2>
            <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full font-medium">
                {{ $rumah->riwayatStatus->count() }} Perubahan
            </span>
        </div>

        @if ($rumah->riwayatStatus->isEmpty())
            <p class="text-slate-500 text-sm">Belum ada riwayat perubahan status.</p>
        @else
            <div class="relative pl-6 border-l-2 border-slate-200 space-y-6">
                @foreach ($rumah->riwayatStatus as $riwayat)
                    @php
                        $colorDari = $badgeColors[$riwayat->status_dari] ?? 'bg-slate-100 text-slate-700';
                        $colorKe = $badgeColors[$riwayat->status_ke] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <div class="relative group">
                        <!-- Dot pada timeline -->
                        <div class="absolute -left-[31px] top-1.5 w-3.5 h-3.5 rounded-full bg-white border-2 border-emerald-500 group-hover:bg-emerald-500 transition-colors"></div>

                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-100 hover:border-slate-200 transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorDari }}">{{ $riwayat->status_dari }}</span>
                                    <span class="text-slate-400 text-sm">→</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $colorKe }}">{{ $riwayat->status_ke }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $riwayat->created_at ? $riwayat->created_at->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200/60 mt-2">
                                <div>
                                    <span class="text-slate-400">Oleh:</span>
                                    <span class="font-medium text-slate-700">
                                        {{ $riwayat->user?->nama ?? 'Sistem' }}
                                    </span>
                                    @if ($riwayat->user?->role)
                                        <span class="text-slate-500 text-[11px] bg-slate-200/70 px-1.5 py-0.5 rounded ml-1">
                                            {{ $riwayat->user->role->nama }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-slate-400">Catatan:</span>
                                    <span class="text-slate-700 italic">
                                        {{ $riwayat->catatan ?: '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection