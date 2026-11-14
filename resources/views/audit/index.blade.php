@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div x-data="auditLogManager()" x-init="loadLogs()" class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Audit Log</h1>
        <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">Read-only</span>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Aksi</label>
                <select x-model="filter.aksi" @change="loadLogs()" class="w-full text-sm border-slate-300 rounded">
                    <option value="">Semua</option>
                    <template x-for="a in aksiList" :key="a">
                        <option :value="a" x-text="a"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Dari Tanggal</label>
                <input type="date" x-model="filter.date_from" @change="loadLogs()" class="w-full text-sm border-slate-300 rounded">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Sampai Tanggal</label>
                <input type="date" x-model="filter.date_to" @change="loadLogs()" class="w-full text-sm border-slate-300 rounded">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Cari</label>
                <input type="text" x-model="filter.search" @input.debounce.300ms="loadLogs()" placeholder="IP / data..." class="w-full text-sm border-slate-300 rounded">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <template x-if="loading"><p class="p-4 text-slate-500 text-sm">Memuat...</p></template>
        <table class="w-full text-sm" x-show="!loading">
            <thead class="bg-slate-50">
                <tr class="text-left border-b border-slate-200">
                    <th class="py-2 px-3">Waktu</th>
                    <th class="py-2 px-3">User</th>
                    <th class="py-2 px-3">Aksi</th>
                    <th class="py-2 px-3">Entitas</th>
                    <th class="py-2 px-3">IP</th>
                    <th class="py-2 px-3">Detail</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="log in logs.data" :key="log.id">
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-2 px-3 text-xs text-slate-500" x-text="new Date(log.created_at).toLocaleString('id-ID')"></td>
                        <td class="py-2 px-3" x-text="log.user?.nama ?? 'Guest'"></td>
                        <td class="py-2 px-3"><span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700" x-text="log.aksi"></span></td>
                        <td class="py-2 px-3 text-xs" x-text="log.entitas"></td>
                        <td class="py-2 px-3 text-xs text-slate-500" x-text="log.ip_address"></td>
                        <td class="py-2 px-3">
                            <button @click="showDetail(log)" class="text-emerald-600 text-xs hover:underline">Lihat</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center text-sm" x-show="!loading">
        <span class="text-slate-500">Total: <span x-text="logs.total"></span> log</span>
        <div class="space-x-2">
            <button @click="prevPage()" :disabled="!logs.prev_page_url" class="px-3 py-1 bg-slate-100 disabled:opacity-50 rounded">Prev</button>
            <span x-text="logs.current_page + ' / ' + logs.last_page"></span>
            <button @click="nextPage()" :disabled="!logs.next_page_url" class="px-3 py-1 bg-slate-100 disabled:opacity-50 rounded">Next</button>
        </div>
    </div>

    <div x-show="showDetailModal" x-cloak class="fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Detail Audit Log</h3>
            <pre class="bg-slate-50 p-3 rounded text-xs overflow-x-auto" x-text="JSON.stringify(selectedLog, null, 2)"></pre>
            <div class="flex justify-end mt-4">
                <button @click="showDetailModal = false" class="px-4 py-2 text-sm bg-slate-200 hover:bg-slate-300 rounded">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function auditLogManager() {
    return {
        logs: { data: [], current_page: 1, last_page: 1, total: 0, prev_page_url: null, next_page_url: null },
        aksiList: [],
        loading: true,
        showDetailModal: false,
        selectedLog: null,
        filter: { aksi: '', date_from: '', date_to: '', search: '' },

        async init() {
            await this.loadAksiList();
            await this.loadLogs();
        },

        async loadAksiList() {
            const res = await fetch('/audit/aksi-list', { headers: { 'Accept': 'application/json' }});
            this.aksiList = (await res.json()).data;
        },

        async loadLogs(page = 1) {
            this.loading = true;
            const params = new URLSearchParams({ page, per_page: 15, ...this.filter });
            const res = await fetch('/audit?' + params, { headers: { 'Accept': 'application/json' }});
            this.logs = await res.json();
            this.loading = false;
        },

        prevPage() { if (this.logs.prev_page_url) this.loadLogs(this.logs.current_page - 1); },
        nextPage() { if (this.logs.next_page_url) this.loadLogs(this.logs.current_page + 1); },

        showDetail(log) {
            this.selectedLog = log;
            this.showDetailModal = true;
        },
    };
}
</script>
@endsection