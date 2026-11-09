@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
<div x-data="userManager()" x-init="loadUsers()" class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Kelola User</h1>
        <button @click="openForm()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">+ Tambah User</button>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <template x-if="loading"><p class="text-slate-500 text-sm">Memuat...</p></template>
        <template x-if="!loading && users.data.length === 0">
            <p class="text-slate-500 text-sm">Belum ada user.</p>
        </template>
        <table class="w-full text-sm" x-show="!loading && users.data.length > 0">
            <thead>
                <tr class="text-left border-b border-slate-200">
                    <th class="py-2 px-2">Nama</th>
                    <th class="py-2 px-2">Username</th>
                    <th class="py-2 px-2">Role</th>
                    <th class="py-2 px-2">Wilayah</th>
                    <th class="py-2 px-2">Status</th>
                    <th class="py-2 px-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="u in users.data" :key="u.id">
                    <tr class="border-b border-slate-100">
                        <td class="py-2 px-2" x-text="u.nama"></td>
                        <td class="py-2 px-2" x-text="u.username"></td>
                        <td class="py-2 px-2"><span class="px-2 py-0.5 rounded text-xs bg-slate-100" x-text="u.role?.nama"></span></td>
                        <td class="py-2 px-2" x-text="u.wilayah?.nama ?? '-'"></td>
                        <td class="py-2 px-2">
                            <span class="px-2 py-0.5 rounded text-xs" :class="u.is_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'" x-text="u.is_aktif ? 'Aktif' : 'Nonaktif'"></span>
                        </td>
                        <td class="py-2 px-2 space-x-2 text-xs">
                            <button @click="edit(u)" class="text-emerald-600 hover:underline">Edit</button>
                            <button @click="toggleAktif(u)" class="text-amber-600 hover:underline" x-text="u.is_aktif ? 'Nonaktifkan' : 'Aktifkan'"></button>
                            <button @click="remove(u)" class="text-red-600 hover:underline">Hapus</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Stub form modal — kerjakan di frontend --}}
    <div x-show="showForm" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4" x-text="editingId ? 'Edit User' : 'Tambah User'"></h3>
            <p class="text-sm text-slate-500">Form create/edit + role + wilayah — kerjakan di frontend.</p>
            <div class="flex justify-end mt-4 space-x-2">
                <button @click="showForm = false" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function userManager() {
    return {
        users: { data: [] },
        loading: true,
        showForm: false,
        editingId: null,

        async loadUsers() {
            this.loading = true;
            const res = await fetch('/users?with_trashed=1', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            this.users = await res.json();
            this.loading = false;
        },

        openForm() { this.editingId = null; this.showForm = true; },
        edit(u) { this.editingId = u.id; this.showForm = true; },

        async toggleAktif(u) {
            const res = await fetch(`/users/${u.id}/toggle-aktif`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            if (res.ok) this.loadUsers();
            else alert('Gagal: ' + (await res.json()).message);
        },

        async remove(u) {
            if (!confirm('Hapus user ' + u.nama + '?')) return;
            const res = await fetch(`/users/${u.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            if (res.ok) this.loadUsers();
            else alert('Gagal: ' + (await res.json()).message);
        },
    };
}
</script>
@endsection>