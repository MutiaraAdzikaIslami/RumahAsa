@extends('layouts.app')

@section('title', 'Kelola Wilayah')

@section('content')
<div x-data="wilayahManager()" x-init="loadTree()" class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Wilayah</h1>
        <button @click="openForm()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
            + Tambah Wilayah
        </button>
    </div>

    {{-- Tree --}}
    <div class="bg-white rounded-lg shadow p-4">
        <template x-if="loading">
            <p class="text-slate-500 text-sm">Memuat data...</p>
        </template>
        <template x-if="!loading && tree.length === 0">
            <p class="text-slate-500 text-sm">Belum ada wilayah. Klik "Tambah Wilayah" untuk memulai.</p>
        </template>
        <ul class="space-y-1">
            <template x-for="node in tree" :key="node.id">
                <li class="border-l-2 border-slate-200 pl-3">
                    <div class="flex items-center justify-between py-1.5">
                        <div>
                            <span class="text-xs font-mono text-slate-500 mr-2" x-text="node.kode"></span>
                            <span class="font-medium text-slate-800" x-text="node.nama"></span>
                            <span class="ml-2 text-xs text-slate-400" x-text="node.tingkat"></span>
                        </div>
                        <div class="space-x-2 text-xs">
                            <button @click="edit(node)" class="text-emerald-600 hover:underline">Edit</button>
                            <button @click="remove(node)" class="text-red-600 hover:underline">Hapus</button>
                        </div>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    {{-- Form Modal (stub — kerjakan sendiri) --}}
    <div x-show="showForm" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4" x-text="editingId ? 'Edit Wilayah' : 'Tambah Wilayah'"></h3>
            <p class="text-sm text-slate-500">Form create/edit — kerjakan di frontend (Blade partial atau Alpine inline).</p>
            <div class="flex justify-end mt-4 space-x-2">
                <button @click="showForm = false" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function wilayahManager() {
    return {
        tree: [],
        loading: true,
        showForm: false,
        editingId: null,

        async loadTree() {
            this.loading = true;
            const res = await fetch('{{ route("wilayah.tree") }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            this.tree = await res.json();
            this.loading = false;
        },

        openForm() {
            this.editingId = null;
            this.showForm = true;
        },

        edit(node) {
            this.editingId = node.id;
            this.showForm = true;
        },

        async remove(node) {
            if (!confirm('Hapus ' + node.nama + '?')) return;
            const res = await fetch('/wilayah/' + node.id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            if (res.ok) this.loadTree();
            else alert('Gagal hapus: ' + (await res.json()).message);
        },
    };
}
</script>
@endsection