@extends('layouts.app')

@section('title', 'Kelola Wilayah')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold tracking-widest text-forest uppercase">Master Data</p>
            <h1 class="text-4xl font-semibold text-slate-900 mt-1">Data Wilayah</h1>
        </div>
        <button @click="openForm()" class="flex items-center gap-2 px-6 py-3 bg-forest hover:bg-forest-dark text-white font-semibold rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-5 h-5"></i> Tambah Wilayah
        </button>
    </div>

    {{-- Summary Card --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
                <i data-lucide="map" class="w-6 h-6 text-forest"></i>
            </div>
            <div>
                <h2 class="font-bold text-slate-900 text-lg">Hierarki Wilayah</h2>
                <p class="text-sm text-slate-500">Data resmi Kemendagri &mdash; Provinsi hingga Kelurahan/Desa</p>
            </div>
        </div>
        <div class="flex items-center gap-8 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-8">
            <div>
                <p class="text-3xl font-bold text-slate-900">91K+</p>
                <p class="text-xs text-slate-500 mt-1">Total Baris</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-slate-900">6</p>
                <p class="text-xs text-slate-500 mt-1">Tingkat Hierarki</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode wilayah..." class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-50 border border-transparent focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20 outline-none transition">
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="layers" class="w-5 h-5 text-slate-400"></i>
                </div>
                <select name="tingkat" class="w-full appearance-none pl-12 pr-10 py-3 rounded-xl bg-slate-50 border border-transparent focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20 outline-none transition">
                    <option value="">Semua Tingkat</option>
                    @foreach ($tingkatList as $t)
                        <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400"></i>
                </div>
            </div>
            <button type="submit" class="flex items-center justify-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                <i data-lucide="filter" class="w-5 h-5"></i> Terapkan Filter
            </button>
        </form>
    </div>

    {{-- Tabel Wilayah --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-900 text-lg">Daftar Wilayah</h3>
                <p class="text-sm text-slate-500">Menampilkan {{ $wilayah->count() }} dari {{ $wilayah->total() }} data</p>
            </div>
        </div>
        
        @if ($wilayah->isEmpty())
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                    <i data-lucide="map-x" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Tidak ada data ditemukan</h3>
                <p class="text-sm text-slate-500">Coba ubah filter pencarian Anda.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left">
                        <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Nama Wilayah</th>
                        <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Tingkat</th>
                        <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Kode</th>
                        <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Induk Wilayah</th>
                        <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Status</th>
                        <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($wilayah as $w)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-forest"></i>
                                </div>
                                <span class="font-semibold text-slate-900">{{ $w->nama }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-6">
                            <span class="text-slate-600 capitalize">{{ $w->tingkat }}</span>
                        </td>
                        <td class="py-5 px-6">
                            <span class="px-2.5 py-1 rounded-md bg-green-50 text-forest font-mono text-xs">{{ $w->kode }}</span>
                        </td>
                        <td class="py-5 px-6 text-slate-500 text-xs">
                            {{ $w->parent?->nama ?? '-' }}
                        </td>
                        <td class="py-5 px-6">
                            @if($w->is_aktif)
                                <span class="px-3 py-1 rounded-full bg-green-50 text-forest text-xs font-semibold">Aktif</span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-5 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="edit({{ $w->id }})" class="p-2 rounded-lg border border-slate-200 text-forest hover:bg-slate-50 transition">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <button @click="remove({{ $w->id }})" class="p-2 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $wilayah->links() }}
        </div>
    </div>

    {{-- Form Modal (Stub) --}}
    <div x-show="showForm" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4" x-text="editingId ? 'Edit Wilayah' : 'Tambah Wilayah'"></h3>
            <p class="text-sm text-slate-500">Form create/edit lengkap bisa diimplementasi di sprint berikutnya.</p>
            <div class="flex justify-end mt-4">
                <button @click="showForm = false" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function wilayahManager() {
    return {
        showForm: false,
        editingId: null,

        openForm() {
            this.editingId = null;
            this.showForm = true;
        },

        edit(id) {
            this.editingId = id;
            this.showForm = true;
        },

        async remove(id) {
            if (!confirm('Yakin hapus wilayah ini?')) return;
            const res = await fetch(`/wilayah/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            if (res.ok) {
                window.location.reload();
            } else {
                const err = await res.json();
                alert('Gagal hapus: ' + (err.message || 'Unknown error'));
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>
@endsection