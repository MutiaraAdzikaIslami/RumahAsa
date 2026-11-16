@extends('layouts.app')

@section('title', 'RumahAsa - Data Wilayah')
@section('header', 'Wilayah')

@section('content')
<div class="space-y-6">
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">Gagal menyimpan data wilayah:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">Konfigurasi Administratif</p>
            <h1 class="mt-1 text-3xl font-bold text-forest-900">Data Wilayah</h1>
            <p class="mt-1 text-sm text-forest-600">Kelola hierarki wilayah dan cakupan penugasan petugas.</p>
        </div>
        <button type="button" onclick="openTambahModal()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest-700 px-5 py-2.5 text-sm font-semibold text-cream-50 shadow-sm hover:bg-forest-800">
            <i data-lucide="plus" class="h-4 w-4"></i> Tambah Wilayah
        </button>
    </div>

    <div class="rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Wilayah terpilih</p>
                <p class="text-lg font-bold text-forest-900">Semua wilayah</p>
                <p class="text-xs text-forest-500">Menampilkan hierarki wilayah administratif</p>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="border-l border-forest-100 pl-4">
                    <p class="text-xs text-forest-500">Kabupaten/Kota</p>
                    <p class="text-xl font-bold text-forest-900">{{ \App\Models\Wilayah::where('tingkat', \App\Models\Wilayah::TINGKAT_KABUPATEN_KOTA)->count() }}</p>
                </div>
                <div class="border-l border-forest-100 pl-4">
                    <p class="text-xs text-forest-500">Kecamatan</p>
                    <p class="text-xl font-bold text-forest-900">{{ \App\Models\Wilayah::where('tingkat', \App\Models\Wilayah::TINGKAT_KECAMATAN)->count() }}</p>
                </div>
                <div class="border-l border-forest-100 pl-4">
                    <p class="text-xs text-forest-500">Kelurahan</p>
                    <p class="text-xl font-bold text-forest-900">{{ \App\Models\Wilayah::where('tingkat', \App\Models\Wilayah::TINGKAT_KELURAHAN)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_auto_auto_auto]">
        <div class="relative">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-forest-400"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode wilayah..."
                   class="w-full rounded-xl border border-forest-100 bg-white py-2.5 pl-10 pr-4 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
        </div>
        <select name="tingkat" class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm text-forest-700 focus:border-forest-400 focus:outline-none">
            <option value="">Semua Tingkat</option>
            @foreach ($tingkatOptions as $t => $label)
                <option value="{{ $t }}" @selected(request('tingkat') === $t)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm text-forest-700 focus:border-forest-400 focus:outline-none">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-forest-700 px-4 py-2.5 text-sm font-medium text-cream-50 hover:bg-forest-800">
                <i data-lucide="filter" class="h-4 w-4"></i> Filter
            </button>
            <a href="{{ route('wilayah.index') }}" class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm font-medium text-forest-700 hover:bg-cream-50">Reset</a>
        </div>
    </form>

    <div class="rounded-2xl border border-forest-100 bg-white shadow-sm">
        <div class="border-b border-forest-100 px-6 py-4">
            <h3 class="font-bold text-forest-900">Hierarki Wilayah</h3>
            <p class="text-xs text-forest-500">Susunan wilayah administratif terurut berdasarkan hierarki path</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-forest-50 text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-forest-500">
                    <tr>
                        <th class="px-6 py-3">Nama Wilayah</th>
                        <th class="px-6 py-3">Tingkat</th>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Induk Wilayah</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-forest-50">
                    @forelse ($wilayah as $w)
                        <tr class="hover:bg-cream-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-9 w-9 place-items-center rounded-lg bg-forest-50">
                                        <i data-lucide="map-pin" class="h-4 w-4 text-forest-600"></i>
                                    </div>
                                    <p class="font-semibold text-forest-900">{{ $w->nama }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-medium text-forest-700">
                                    {{ $w->labelTingkat() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <code class="rounded bg-cream-50 px-2 py-0.5 font-mono text-xs text-forest-700">{{ $w->kode }}</code>
                            </td>
                            <td class="px-6 py-4 text-forest-600">{{ $w->parent?->nama ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if ($w->is_aktif)
                                    <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-semibold text-forest-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-semibold text-forest-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex gap-1">
                                    <button type="button"
                                            onclick="openEditModal({{ $w->id }}, @js($w->kode), @js($w->nama), @js($w->tingkat), {{ $w->parent_id ?? 'null' }}, {{ $w->is_aktif ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1 rounded-lg border border-forest-100 px-2.5 py-1.5 text-xs font-medium text-forest-700 hover:bg-forest-50" title="Edit">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <form method="POST" action="{{ route('wilayah.destroy', $w) }}" onsubmit="return confirm('Yakin ingin menghapus wilayah {{ $w->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50" title="Hapus">
                                            <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="grid h-12 w-12 place-items-center rounded-full bg-forest-50">
                                        <i data-lucide="map" class="h-6 w-6 text-forest-400"></i>
                                    </div>
                                    <p class="text-sm font-medium text-forest-700">Tidak ada wilayah</p>
                                    <p class="text-xs text-forest-500">Coba ubah filter atau tambah wilayah baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($wilayah->hasPages())
            <div class="flex items-center justify-between border-t border-forest-100 px-6 py-4">
                <p class="text-xs text-forest-500">Menampilkan {{ $wilayah->count() }} dari {{ $wilayah->total() }} wilayah</p>
                {{ $wilayah->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH WILAYAH --}}
<div id="modalTambahWilayah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-forest-100 pb-3">
            <h3 class="text-lg font-bold text-forest-900">Tambah Wilayah Baru</h3>
            <button type="button" onclick="closeModal('modalTambahWilayah')" class="text-forest-400 hover:text-forest-700">&times;</button>
        </div>
        <form method="POST" action="{{ route('wilayah.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-forest-900">Tingkat Wilayah <span class="text-red-500">*</span></label>
                <select name="tingkat" id="tambah_tingkat" required onchange="loadParentOptions(this.value, 'tambah_parent_id')"
                        class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
                    <option value="">-- Pilih Tingkat --</option>
                    @foreach ($tingkatOptions as $t => $label)
                        <option value="{{ $t }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-forest-900">Induk Wilayah (Parent)</label>
                <select name="parent_id" id="tambah_parent_id"
                        class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
                    <option value="">-- Tanpa Induk (Tingkat Paling Atas) --</option>
                </select>
                <p class="mt-1 text-xs text-forest-500">Otomatis memuat daftar wilayah 1 jenjang di atas tingkat yang dipilih.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-forest-900">Kode Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="kode" required maxlength="30" placeholder="Contoh: 35.78.01"
                       class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-forest-900">Nama Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="100" placeholder="Contoh: Kecamatan Gubeng"
                       class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_aktif" id="tambah_is_aktif" value="1" checked class="rounded border-forest-300 text-forest-700">
                <label for="tambah_is_aktif" class="text-sm text-forest-700">Status Aktif</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-forest-100 pt-4">
                <button type="button" onclick="closeModal('modalTambahWilayah')" class="rounded-xl border border-forest-200 px-4 py-2 text-sm font-medium text-forest-700">Batal</button>
                <button type="submit" class="rounded-xl bg-forest-700 px-4 py-2 text-sm font-semibold text-cream-50 hover:bg-forest-800">Simpan Wilayah</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT WILAYAH --}}
<div id="modalEditWilayah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-forest-100 pb-3">
            <h3 class="text-lg font-bold text-forest-900">Edit Data Wilayah</h3>
            <button type="button" onclick="closeModal('modalEditWilayah')" class="text-forest-400 hover:text-forest-700">&times;</button>
        </div>
        <form id="formEditWilayah" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-forest-900">Tingkat Wilayah</label>
                <input type="text" id="edit_tingkat_label" disabled class="mt-1 w-full rounded-xl border border-forest-100 bg-forest-50 px-3 py-2 text-sm text-forest-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-forest-900">Induk Wilayah (Parent)</label>
                <select name="parent_id" id="edit_parent_id"
                        class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
                    <option value="">-- Tanpa Induk --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-forest-900">Kode Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="kode" id="edit_kode" required maxlength="30"
                       class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-forest-900">Nama Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="edit_nama" required maxlength="100"
                       class="mt-1 w-full rounded-xl border border-forest-200 px-3 py-2 text-sm text-forest-900">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_aktif" id="edit_is_aktif" value="1" class="rounded border-forest-300 text-forest-700">
                <label for="edit_is_aktif" class="text-sm text-forest-700">Status Aktif</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-forest-100 pt-4">
                <button type="button" onclick="closeModal('modalEditWilayah')" class="rounded-xl border border-forest-200 px-4 py-2 text-sm font-medium text-forest-700">Batal</button>
                <button type="submit" class="rounded-xl bg-forest-700 px-4 py-2 text-sm font-semibold text-cream-50 hover:bg-forest-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTambahModal() {
        const el = document.getElementById('modalTambahWilayah');
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    async function loadParentOptions(tingkat, selectId, selectedParentId = null) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">-- Memuat induk wilayah... --</option>';

        if (!tingkat) {
            select.innerHTML = '<option value="">-- Tanpa Induk --</option>';
            return;
        }

        const res = await fetch(`{{ route('wilayah.parents') }}?tingkat=${encodeURIComponent(tingkat)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();
        const items = json.data || [];

        if (items.length === 0) {
            select.innerHTML = '<option value="">-- Tanpa Induk (Tingkat Paling Atas) --</option>';
            return;
        }

        select.innerHTML = '<option value="">-- Pilih Induk Wilayah --</option>';
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = `${item.kode} - ${item.nama}`;
            if (selectedParentId && Number(selectedParentId) === Number(item.id)) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    async function openEditModal(id, kode, nama, tingkat, parentId, isAktif) {
        document.getElementById('formEditWilayah').action = `/wilayah/${id}`;
        document.getElementById('edit_kode').value = kode;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_tingkat_label').value = tingkat.toUpperCase();
        document.getElementById('edit_is_aktif').checked = Boolean(isAktif);

        await loadParentOptions(tingkat, 'edit_parent_id', parentId);

        const el = document.getElementById('modalEditWilayah');
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
</script>
@endsection