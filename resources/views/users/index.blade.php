@extends('layouts.app')

@section('title', 'RumahAsa - Manajemen User')
@section('header', 'Data User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">Administrasi Akses</p>
            <h1 class="mt-1 text-3xl font-bold text-forest-900">Manajemen User</h1>
            <p class="mt-1 text-sm text-forest-600">Kelola akun, role, dan wilayah tugas pengguna RumahAsa.</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest-700 px-5 py-2.5 text-sm font-semibold text-cream-50 shadow-sm transition hover:bg-forest-800">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah User
        </a>
    </div>

    <div class="rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Total User</p>
                <p class="text-2xl font-bold text-forest-900">{{ $users->total() }}</p>
            </div>
            <div class="border-l border-forest-100 pl-4">
                <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Akun Aktif</p>
                <p class="text-2xl font-bold text-forest-600">{{ \App\Models\User::where('is_aktif', true)->count() }}</p>
            </div>
            <div class="border-l border-forest-100 pl-4">
                <p class="text-xs font-medium uppercase tracking-wide text-forest-500">Akun Nonaktif</p>
                <p class="text-2xl font-bold text-forest-400">{{ \App\Models\User::where('is_aktif', false)->count() }}</p>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2 border-t border-forest-100 pt-3 text-xs text-forest-600">
            <i data-lucide="shield-check" class="h-4 w-4 text-forest-500"></i>
            <span>Hak akses mengikuti role dan wilayah tugas pengguna.</span>
        </div>
    </div>

    <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_auto_auto]">
        <div class="relative">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-forest-400"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email user..."
                   class="w-full rounded-xl border border-forest-100 bg-white py-2.5 pl-10 pr-4 text-sm text-forest-900 placeholder-forest-400 focus:border-forest-400 focus:outline-none focus:ring-2 focus:ring-forest-400/30">
        </div>
        <select name="role_id" class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm text-forest-700 focus:border-forest-400 focus:outline-none">
            <option value="">Semua Role</option>
            @foreach ($roles as $r)
                <option value="{{ $r['id'] }}" @selected(request('role_id') == $r['id'])>{{ $r['nama'] }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-forest-700 px-4 py-2.5 text-sm font-medium text-cream-50 hover:bg-forest-800">
                <i data-lucide="filter" class="h-4 w-4"></i> Filter
            </button>
            <a href="{{ route('users.index') }}" class="rounded-xl border border-forest-100 bg-white px-4 py-2.5 text-sm font-medium text-forest-700 hover:bg-cream-50">Reset</a>
        </div>
    </form>

    <div class="rounded-2xl border border-forest-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-forest-100 px-6 py-4">
            <div>
                <h3 class="font-bold text-forest-900">Daftar User</h3>
                <p class="text-xs text-forest-500">{{ $users->total() }} pengguna terdaftar pada sistem</p>
            </div>
            <p class="text-xs text-forest-500">Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-forest-50 text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-forest-500">
                    <tr>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Wilayah Tugas</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-forest-50">
                    @forelse ($users as $u)
                        <tr class="hover:bg-cream-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-9 w-9 place-items-center rounded-lg bg-forest-700 text-xs font-bold text-cream-50">
                                        {{ strtoupper(substr($u->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-forest-900">{{ $u->nama }}</p>
                                        <p class="text-xs text-forest-500">{{ $u->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-forest-600">{{ $u->email ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if ($u->role?->kode === 'admin')
                                    <span class="inline-flex items-center rounded-full bg-clay-100 px-2.5 py-0.5 text-xs font-semibold text-clay-600">Admin</span>
                                @elseif ($u->role?->kode === 'surveyor')
                                    <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-semibold text-forest-700">Surveyor</span>
                                @elseif ($u->role?->kode === 'verifikator')
                                    <span class="inline-flex items-center rounded-full bg-clay-100 px-2.5 py-0.5 text-xs font-semibold text-clay-600">Verifikator</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-semibold text-forest-700">{{ $u->role?->nama ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-forest-600">
                                @if ($u->wilayah)
                                    {{ $u->wilayah->nama }}
                                @else
                                    <span class="text-forest-400">Semua wilayah</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($u->deleted_at)
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-600">Dihapus</span>
                                @elseif (!$u->is_aktif)
                                    <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-semibold text-forest-500">Nonaktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-forest-50 px-2.5 py-0.5 text-xs font-semibold text-forest-700">Aktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="inline-flex gap-1">
                                    <a href="{{ route('users.edit', $u) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-forest-100 px-2.5 py-1.5 text-xs font-medium text-forest-700 hover:bg-forest-50" title="Edit">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </a>
                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.reset-password', $u) }}" onsubmit="return confirm('Reset password untuk user {{ $u->nama }}?')" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50" title="Reset Password">
                                                <i data-lucide="key-round" class="h-3.5 w-3.5"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Hapus user ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50" title="Hapus">
                                                <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="grid h-12 w-12 place-items-center rounded-full bg-forest-50">
                                        <i data-lucide="users" class="h-6 w-6 text-forest-400"></i>
                                    </div>
                                    <p class="text-sm font-medium text-forest-700">Belum ada user terdaftar</p>
                                    <p class="text-xs text-forest-500">Klik tombol "Tambah User" untuk membuat akun baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="flex items-center justify-between border-t border-forest-100 px-6 py-4">
                <p class="text-xs text-forest-500">Menampilkan {{ $users->count() }} dari {{ $users->total() }} user</p>
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Pop-Up Modal Password Sementara (Create & Reset Password) --}}
@if (session('generated_password'))
<div id="modal-generated-password" class="fixed inset-0 z-50 flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-xs">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-2xl">
        <div class="border-b border-forest-100 bg-forest-700 px-6 py-4 text-cream-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="key-round" class="h-5 w-5 text-gold-400"></i>
                    <h3 class="text-base font-bold">Password Sementara User</h3>
                </div>
                <button type="button" onclick="document.getElementById('modal-generated-password').remove()" class="rounded-lg p-1 text-cream-100 hover:bg-forest-800" title="Tutup">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <p class="mt-1 text-xs text-cream-100/90">Simpan atau salin kredensial ini sekarang. Password hanya ditampilkan satu kali.</p>
        </div>

        <div class="space-y-4 px-6 py-5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-forest-500">Username</label>
                <div class="mt-1 rounded-lg border border-forest-100 bg-cream-50 px-3.5 py-2.5">
                    <code id="gen-username" class="text-sm font-bold text-forest-900 select-all">{{ session('generated_username') }}</code>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-forest-500">Password Sementara</label>
                <div class="mt-1 flex items-center justify-between gap-2 rounded-lg border border-amber-300 bg-amber-50/70 px-3.5 py-2.5">
                    <code id="gen-password" class="font-mono text-base font-bold tracking-wider text-forest-900 select-all">{{ session('generated_password') }}</code>
                    <button type="button" id="btn-copy-password"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-forest-700 px-3 py-1.5 text-xs font-semibold text-cream-50 hover:bg-forest-800">
                        <i data-lucide="copy" class="h-3.5 w-3.5"></i>
                        <span id="copy-label">Salin</span>
                    </button>
                </div>
            </div>

            <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                <i data-lucide="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"></i>
                <span>Sampaikan password ini kepada user. User wajib mengganti password saat login pertama kali.</span>
            </div>
        </div>

        <div class="flex items-center justify-end border-t border-forest-100 bg-cream-50/60 px-6 py-3.5">
            <button type="button" onclick="document.getElementById('modal-generated-password').remove()"
                    class="rounded-lg bg-forest-700 px-5 py-2 text-sm font-semibold text-cream-50 hover:bg-forest-800">
                Saya Sudah Menyimpannya
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const copyBtn = document.getElementById('btn-copy-password');
    const passEl = document.getElementById('gen-password');
    const labelEl = document.getElementById('copy-label');
    if (!copyBtn || !passEl) return;

    copyBtn.addEventListener('click', async function() {
        const text = passEl.textContent.trim();
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
            }
            if (labelEl) labelEl.textContent = 'Tersalin!';
            setTimeout(() => { if (labelEl) labelEl.textContent = 'Salin'; }, 2000);
        } catch (err) {
            console.error('Gagal menyalin password:', err);
        }
    });
})();
</script>
@endpush
@endif
@endsection