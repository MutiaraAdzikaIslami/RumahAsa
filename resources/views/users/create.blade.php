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
                        {{ $isEdit ? 'Perbarui data akun, role, dan wilayah tugas pengguna.' : 'Lengkapi data untuk menambahkan user baru ke sistem.' }}
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
                               class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-semibold text-forest-900">Username <span class="text-red-500">*</span></label>
                        <input id="username" name="username" type="text" required
                               value="{{ old('username', $user?->username ?? '') }}"
                               pattern="[A-Za-z0-9._-]+"
                               placeholder="contoh: surveyor01"
                               class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                        <p class="mt-1 text-xs text-forest-500">Hanya huruf, angka, titik, dash, underscore.</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-forest-900">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user?->email ?? '') }}"
                               placeholder="nama@rumahasa.id"
                               class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                    </div>

                    <div>
                        <label for="role_id" class="block text-sm font-semibold text-forest-900">Role <span class="text-red-500">*</span></label>
                        <select id="role_id" name="role_id" required
                                class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                            <option value="" data-kode="">Pilih role</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r['id'] }}" data-kode="{{ $r['kode'] }}" @selected(old('role_id', $user?->role_id) == $r['id'])>{{ $r['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="is_aktif" class="block text-sm font-semibold text-forest-900">Status akun</label>
                        <select id="is_aktif" name="is_aktif"
                                class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                            <option value="1" @selected(old('is_aktif', $user?->is_aktif ?? true))>Aktif</option>
                            <option value="0" @selected(old('is_aktif') === '0' || ($user && !$user->is_aktif))>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ===== Section: Wilayah Tugas (cascading) ===== --}}
            <div class="border-t border-forest-100 pt-5" id="section-wilayah">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gold-600">Wilayah Tugas</h3>
                    <span id="wilayah-badge" class="rounded-full bg-forest-50 px-2.5 py-0.5 text-[11px] font-medium text-forest-700">Pilih role Surveyor untuk mengaktifkan</span>
                </div>
                <p class="mt-1 text-xs text-forest-500">Wajib diisi khusus untuk role <strong>Surveyor</strong>. Role Admin, Verifikator, dan Pimpinan memiliki akses semua wilayah.</p>

                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    {{-- Kabupaten/Kota --}}
                    <div>
                        <label for="kabupaten_id" class="block text-sm font-semibold text-forest-900">Kabupaten/Kota</label>
                        <select id="kabupaten_id" name="kabupaten_id"
                                class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
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
                                class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-forest-50 px-3.5 py-2.5 text-sm text-forest-400 focus:border-forest-500 focus:outline-none">
                            <option value="">Pilih kecamatan</option>
                        </select>
                    </div>

                    {{-- Kelurahan --}}
                    <div>
                        <label for="wilayah_id" class="block text-sm font-semibold text-forest-900">Kelurahan/Desa</label>
                        <select id="wilayah_id" name="wilayah_id" disabled
                                class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-forest-50 px-3.5 py-2.5 text-sm text-forest-400 focus:border-forest-500 focus:outline-none">
                            <option value="">Pilih kelurahan</option>
                        </select>
                    </div>

                    {{-- RT text input --}}
                    <div>
                        <label for="rt_text" class="block text-sm font-semibold text-forest-900">RT</label>
                        <input id="rt_text" name="rt_text" type="text" maxlength="10"
                               value="{{ old('rt_text') }}"
                               placeholder="contoh: 01"
                               class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                        <p class="mt-1 text-xs text-forest-500">Opsional, ketik nomor RT.</p>
                    </div>

                    {{-- RW text input --}}
                    <div>
                        <label for="rw_text" class="block text-sm font-semibold text-forest-900">RW</label>
                        <input id="rw_text" name="rw_text" type="text" maxlength="10"
                               value="{{ old('rw_text') }}"
                               placeholder="contoh: 01"
                               class="mt-1.5 block w-full rounded-xl border border-forest-100 bg-white px-3.5 py-2.5 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/20">
                        <p class="mt-1 text-xs text-forest-500">Opsional, ketik nomor RW.</p>
                    </div>
                </div>
            </div>

            {{-- Info box --}}
            <div class="flex items-start gap-3 rounded-xl bg-cream-50 p-4 border border-forest-100/60">
                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-forest-50">
                    <i data-lucide="info" class="h-4 w-4 text-forest-700"></i>
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
                <a href="{{ route('users.index') }}" class="rounded-xl border border-forest-200 bg-white px-5 py-2.5 text-sm font-semibold text-forest-700 hover:bg-cream-50 transition">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-forest-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-forest-800 transition">
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
    const roleSelect = document.getElementById('role_id');
    const kabSelect = document.getElementById('kabupaten_id');
    const kecSelect = document.getElementById('kecamatan_id');
    const kelSelect = document.getElementById('wilayah_id');
    const rtInput = document.getElementById('rt_text');
    const rwInput = document.getElementById('rw_text');
    const wilayahBadge = document.getElementById('wilayah-badge');

    if (!kabSelect || !kecSelect || !kelSelect) return;

    async function fetchWilayah(url) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json',
            },
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const rawText = await res.text();
        return JSON.parse(rawText.replace(/^\uFEFF/, '').trim());
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
        selectEl.classList.remove('bg-forest-50', 'text-forest-400', 'cursor-not-allowed');
        selectEl.classList.add('bg-white', 'text-forest-900');
    }

    function resetSelect(selectEl, placeholderText) {
        selectEl.innerHTML = `<option value="">${placeholderText}</option>`;
        selectEl.disabled = true;
        selectEl.classList.add('bg-forest-50', 'text-forest-400', 'cursor-not-allowed');
        selectEl.classList.remove('bg-white', 'text-forest-900');
    }

    function syncWilayahByRole() {
        if (!roleSelect) return;
        const selectedOpt = roleSelect.options[roleSelect.selectedIndex];
        const kode = selectedOpt ? (selectedOpt.dataset.kode || '') : '';
        const isSurveyor = kode === 'surveyor';

        kabSelect.disabled = !isSurveyor;
        if (rtInput) rtInput.disabled = !isSurveyor;
        if (rwInput) rwInput.disabled = !isSurveyor;

        if (!isSurveyor) {
            kabSelect.value = '';
            kabSelect.classList.add('bg-forest-50', 'text-forest-400', 'cursor-not-allowed');
            kabSelect.classList.remove('bg-white', 'text-forest-900');
            resetSelect(kecSelect, 'Pilih kecamatan');
            resetSelect(kelSelect, 'Pilih kelurahan');
            if (rtInput) {
                rtInput.value = '';
                rtInput.classList.add('bg-forest-50', 'cursor-not-allowed');
            }
            if (rwInput) {
                rwInput.value = '';
                rwInput.classList.add('bg-forest-50', 'cursor-not-allowed');
            }
            if (wilayahBadge) {
                wilayahBadge.textContent = kode ? 'Semua wilayah (Non-Surveyor)' : 'Pilih role Surveyor untuk mengaktifkan';
                wilayahBadge.className = 'rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-600';
            }
        } else {
            kabSelect.classList.remove('bg-forest-50', 'text-forest-400', 'cursor-not-allowed');
            kabSelect.classList.add('bg-white', 'text-forest-900');
            if (rtInput) rtInput.classList.remove('bg-forest-50', 'cursor-not-allowed');
            if (rwInput) rwInput.classList.remove('bg-forest-50', 'cursor-not-allowed');
            if (wilayahBadge) {
                wilayahBadge.textContent = 'Wajib untuk Surveyor';
                wilayahBadge.className = 'rounded-full bg-forest-50 px-2.5 py-0.5 text-[11px] font-semibold text-forest-700';
            }
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', syncWilayahByRole);
        syncWilayahByRole();
    }

    const allKecamatan = @json($kecamatanList ?? []);
    const kelurahanUrlTemplate = @json(route('api.wilayah.kelurahan', ['kecamatanId' => 999999999], false));

    kabSelect.addEventListener('change', function() {
        const kabupatenId = this.value;
        resetSelect(kecSelect, 'Pilih kecamatan');
        resetSelect(kelSelect, 'Pilih kelurahan');
        if (!kabupatenId) return;

        const list = allKecamatan.filter(item => String(item.parent_id) === String(kabupatenId));
        fillOptions(kecSelect, list, 'Pilih kecamatan');
    });

    kecSelect.addEventListener('change', async function() {
        const kecamatanId = this.value;
        resetSelect(kelSelect, 'Memuat kelurahan...');
        if (!kecamatanId) {
            resetSelect(kelSelect, 'Pilih kelurahan');
            return;
        }

        try {
            const url = kelurahanUrlTemplate.replace('999999999', encodeURIComponent(kecamatanId));
            const data = await fetchWilayah(url);
            const list = Array.isArray(data) ? data : (data.data || []);
            fillOptions(kelSelect, list, 'Pilih kelurahan');
        } catch (e) {
            console.error('Gagal load kelurahan:', e);
            resetSelect(kelSelect, `Gagal memuat kelurahan (${e.message})`);
        }
    });
})();
</script>
@endpush
@endsection