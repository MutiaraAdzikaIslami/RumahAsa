@extends('layouts.app')

@php
    $user   = $user ?? null;
    $isEdit = (bool) ($user?->id);
@endphp

@section('title', $isEdit ? 'RumahAsa - Edit User' : 'RumahAsa - Tambah User')
@section('header', $isEdit ? 'Edit User' : 'Tambah User')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-lg">
        {{-- Header --}}
        <div class="border-b border-forest-100 px-6 py-5">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-forest-900">{{ $isEdit ? 'Edit User' : 'Tambah User' }}</h2>
                    <p class="mt-0.5 text-sm text-forest-600">
                        {{ $isEdit ? 'Update data user yang ada di sistem.' : 'Lengkapi data untuk menambahkan user baru ke sistem.' }}
                    </p>
                </div>
                <a href="{{ route('users.index') }}" class="grid h-9 w-9 place-items-center rounded-lg bg-forest-50 text-forest-700 hover:bg-forest-100" title="Tutup">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </a>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}" class="px-6 py-5 space-y-6" id="form-user">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="h-4 w-4"></i>
                        <span class="font-semibold">Ada kesalahan input:</span>
                    </div>
                    <ul class="mt-2 list-disc pl-8 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===== Section: Data Akun ===== --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gold-600">Data Akun</h3>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="nama" class="block text-sm font-semibold text-forest-900">Nama lengkap <span class="text-red-500">*</span></label>
                        <input id="nama" name="nama" type="text" required value="{{ old('nama', $user?->nama ?? '') }}"
                               placeholder="Masukkan nama lengkap"
                               class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-semibold text-forest-900">Username <span class="text-red-500">*</span></label>
                        <input id="username" name="username" type="text" required
                               value="{{ old('username', $user?->username ?? '') }}"
                               pattern="[A-Za-z0-9._-]+"
                               placeholder="contoh: surveyor01"
                               class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                        <p class="mt-1 text-xs text-forest-500">Hanya huruf, angka, titik, dash, underscore. Disimpan lowercase.</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-forest-900">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user?->email ?? '') }}"
                               placeholder="nama@rumahasa.id"
                               class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                    </div>

                    <div>
                        <label for="role_id" class="block text-sm font-semibold text-forest-900">Role <span class="text-red-500">*</span></label>
                        <select id="role_id" name="role_id" required
                                class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                            <option value="">Pilih role</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r['id'] }}" @selected(old('role_id', $user?->role_id) == $r['id'])>{{ $r['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="is_aktif" class="block text-sm font-semibold text-forest-900">Status akun</label>
                        <select id="is_aktif" name="is_aktif"
                                class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                            <option value="1" @selected(old('is_aktif', $user?->is_aktif ?? true))>Aktif</option>
                            <option value="0" @selected(old('is_aktif') === '0' || ($user && !$user->is_aktif))>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ===== Section: Wilayah Tugas (cascading) ===== --}}
            <div class="border-t border-forest-100 pt-5" id="section-wilayah">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gold-600">Wilayah Tugas</h3>
                <p class="text-xs text-forest-500">Pilih hierarki wilayah tugas (untuk Surveyor & Verifikator). Admin & Pimpinan boleh kosong (akses semua wilayah).</p>

                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    {{-- Kabupaten/Kota --}}
                    <div>
                        <label for="kabupaten_id" class="block text-sm font-semibold text-forest-900">Kabupaten/Kota</label>
                        <select id="kabupaten_id" name="kabupaten_id"
                                class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                            <option value="">Pilih kabupaten/kota</option>
                            @foreach ($kabupatenList as $kab)
                                <option value="{{ $kab->id }}" @selected(old('kabupaten_id') == $kab->id)>{{ $kab->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kecamatan --}}
                    <div>
                        <label for="kecamatan_id" class="block text-sm font-semibold text-forest-900">Kecamatan</label>
                        <select id="kecamatan_id" name="kecamatan_id" disabled
                                class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-forest-50 px-3.5 py-2.5 text-sm text-forest-400 focus:border-forest-400 focus:outline-none">
                            <option value="">Pilih kecamatan</option>
                        </select>
                    </div>

                    {{-- Kelurahan --}}
                    <div>
                        <label for="wilayah_id" class="block text-sm font-semibold text-forest-900">Kelurahan/Desa</label>
                        <select id="wilayah_id" name="wilayah_id" disabled
                                class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-forest-50 px-3.5 py-2.5 text-sm text-forest-400 focus:border-forest-400 focus:outline-none">
                            <option value="">Pilih kelurahan</option>
                        </select>
                    </div>

                    {{-- RT text input --}}
                    <div>
                        <label for="rt_text" class="block text-sm font-semibold text-forest-900">RT</label>
                        <input id="rt_text" name="rt_text" type="text" maxlength="10"
                               value="{{ old('rt_text') }}"
                               placeholder="contoh: 01"
                               class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                        <p class="mt-1 text-xs text-forest-500">Ketik nomor RT langsung.</p>
                    </div>

                    {{-- RW text input --}}
                    <div>
                        <label for="rw_text" class="block text-sm font-semibold text-forest-900">RW</label>
                        <input id="rw_text" name="rw_text" type="text" maxlength="10"
                               value="{{ old('rw_text') }}"
                               placeholder="contoh: 01"
                               class="mt-1.5 block w-full rounded-lg border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
                        <p class="mt-1 text-xs text-forest-500">Ketik nomor RW langsung.</p>
                    </div>
                </div>
            </div>

            {{-- Info box --}}
            <div class="flex items-start gap-3 rounded-lg bg-cream-50 p-4">
                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-forest-50">
                    <i data-lucide="info" class="h-4 w-4 text-forest-600"></i>
                </div>
                <div class="text-xs text-forest-700">
                    @if ($isEdit)
                        <p class="font-semibold">Password tidak diubah pada form ini.</p>
                        <p class="mt-0.5 text-forest-500">Untuk reset password, gunakan tombol "Reset Password" di daftar user.</p>
                    @else
                        <p class="font-semibold">Password sementara akan digenerate otomatis.</p>
                        <p class="mt-0.5 text-forest-500">Password akan ditampilkan sekali setelah user dibuat. Sampaikan ke user segera.</p>
                    @endif
                </div>
            </div>

            {{-- Footer tombol --}}
            <div class="flex items-center justify-end gap-3 border-t border-forest-100 pt-5">
                <a href="{{ route('users.index') }}" class="rounded-lg border border-forest-100 bg-white px-4 py-2.5 text-sm font-medium text-forest-700 hover:bg-cream-50">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-forest-700 px-5 py-2.5 text-sm font-semibold text-cream-50 shadow-sm hover:bg-forest-800">
                    <i data-lucide="check" class="h-4 w-4"></i>
                    {{ $isEdit ? 'Simpan' : 'Tambah User' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const kabSelect = document.getElementById('kabupaten_id');
    const kecSelect = document.getElementById('kecamatan_id');
    const kelSelect = document.getElementById('wilayah_id');

    if (!kabSelect || !kecSelect || !kelSelect) return;

    async function fetchWilayah(url) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
        });
        return await res.json();
    }

    function fillOptions(selectEl, items, placeholderText) {
        selectEl.innerHTML = `<option value="">${placeholderText}</option>`;
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nama;
            selectEl.appendChild(opt);
        });
        selectEl.disabled = false;
        selectEl.classList.remove('bg-forest-50', 'text-forest-400');
        selectEl.classList.add('bg-white', 'text-forest-900');
    }

    function resetSelect(selectEl, placeholderText) {
        selectEl.innerHTML = `<option value="">${placeholderText}</option>`;
        selectEl.disabled = true;
        selectEl.classList.add('bg-forest-50', 'text-forest-400');
        selectEl.classList.remove('bg-white', 'text-forest-900');
    }

    kabSelect.addEventListener('change', async function() {
        const kabupatenId = this.value;
        resetSelect(kecSelect, 'Pilih kecamatan');
        resetSelect(kelSelect, 'Pilih kelurahan');
        if (!kabupatenId) return;

        try {
            const data = await fetchWilayah(`/api/wilayah/kabupaten/${kabupatenId}/kecamatan`);
            if (data.success) {
                fillOptions(kecSelect, data.data, 'Pilih kecamatan');
            }
        } catch (e) {
            console.error('Gagal load kecamatan:', e);
        }
    });

    kecSelect.addEventListener('change', async function() {
        const kecamatanId = this.value;
        resetSelect(kelSelect, 'Pilih kelurahan');
        if (!kecamatanId) return;

        try {
            const data = await fetchWilayah(`/api/wilayah/kecamatan/${kecamatanId}/kelurahan`);
            if (data.success) {
                fillOptions(kelSelect, data.data, 'Pilih kelurahan');
            }
        } catch (e) {
            console.error('Gagal load kelurahan:', e);
        }
    });
})();
</script>
@endpush
@endsection