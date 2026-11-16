<div>
    {{-- Daftar User --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold tracking-widest text-forest uppercase">Administrasi Akses</p>
                <h1 class="text-4xl font-semibold text-slate-900 mt-1">Manajemen User</h1>
            </div>
            <button wire:click="openCreateModal" class="flex items-center gap-2 px-6 py-3 bg-forest hover:bg-forest-dark text-white font-semibold rounded-xl shadow-sm transition">
                <i data-lucide="plus" class="w-5 h-5"></i> Tambah User
            </button>
        </div>

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-lg">Daftar User</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left">
                            <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Nama</th>
                            <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Username</th>
                            <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Role</th>
                            <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Wilayah Tugas</th>
                            <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs">Status</th>
                            <th class="py-4 px-6 font-medium text-slate-500 uppercase tracking-wider text-xs text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-medium text-slate-900">
                                {{ $u->nama }}
                                @if ($u->deleted_at)
                                    <span class="ml-2 text-xs text-red-500">(Dihapus)</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-600">{{ $u->username }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-md bg-green-50 text-forest text-xs font-semibold">{{ $u->role->nama }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 text-xs">
                                @if($u->wilayah)
                                    {{ $u->wilayah->nama }}
                                @else
                                    <span class="text-slate-400">Semua Wilayah</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($u->is_aktif)
                                    <span class="px-3 py-1 rounded-full bg-green-50 text-forest text-xs font-semibold">Aktif</span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($u->deleted_at)
                                        <button wire:click="restore({{ $u->id }})" wire:confirm="Yakin pulihkan user ini?" class="p-2 rounded-lg border border-slate-200 text-blue-500 hover:bg-blue-50 transition">
                                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                        </button>
                                    @else
                                        <button wire:click="openEditModal({{ $u->id }})" class="p-2 rounded-lg border border-slate-200 text-forest hover:bg-slate-50 transition">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button wire:click="delete({{ $u->id }})" wire:confirm="Yakin hapus user ini? User tidak akan bisa login tapi datanya tetap tersimpan." class="p-2 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    @if ($showModal)
    <div class="fixed inset-0 bg-slate-900 bg-opacity-50" style="z-index: 40;"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 50;">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto" x-data="{ scrollOnError() { this.$nextTick(() => { let error = this.$el.querySelector('.text-red-500'); if(error) error.scrollIntoView({behavior:'smooth', block:'center'}); }) } }" x-init="scrollOnError()">
            <div class="flex justify-between items-center mb-4 sticky top-0 bg-white pb-2 border-b">
                <h3 class="text-lg font-semibold">{{ $userId ? 'Edit User' : 'Tambah User' }}</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form wire:submit="save" class="space-y-5">
                @if ($errors->any())
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="nama" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" wire:model="username" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email (Opsional)</label>
                        <input type="email" wire:model="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select wire:model.live="roleId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <option value="">Pilih Role...</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->nama }}</option>
                            @endforeach
                        </select>
                        @error('roleId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Wilayah Tugas {{ $isSurveyor ? '(Wajib untuk Surveyor)' : '(Hanya untuk Surveyor)' }}</h3>
                    <p class="text-xs text-slate-500 mb-3">*Pilih Kabupaten hingga Kelurahan. Untuk RW dan RT, ketik manual.</p>
                    
                    @if ($isSurveyor)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kabupaten/Kota</label>
                            <select wire:model.live="kabupatenId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                <option value="">Pilih Kabupaten...</option>
                                @foreach ($kabupatenList as $kab)
                                    <option value="{{ $kab->id }}">{{ $kab->nama }}</option>
                                @endforeach
                            </select>
                            @error('kabupatenId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan</label>
                            <select wire:model.live="kecamatanId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" @if(!$kabupatenId) disabled @endif>
                                <option value="">Pilih Kecamatan...</option>
                                @foreach ($kecamatanList as $kec)
                                    <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kelurahan/Desa</label>
                            <select wire:model.live="kelurahanId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" @if(!$kecamatanId) disabled @endif>
                                <option value="">Pilih Kelurahan...</option>
                                @foreach ($kelurahanList as $kel)
                                    <option value="{{ $kel->id }}">{{ $kel->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RW (Ketik Manual)</label>
                            <input type="text" wire:model="rwNama" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. RW 01" @if(!$kelurahanId) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RT (Ketik Manual)</label>
                            <input type="text" wire:model="rtNama" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. RT 02" @if(!$kelurahanId) disabled @endif>
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-slate-500 bg-slate-50 p-3 rounded-lg">Role ini tidak memerlukan penempatan wilayah tugas.</p>
                    @endif
                </div>

                <div class="flex items-center">
                    <input type="checkbox" wire:model="isAktif" id="isAktif" class="rounded border-slate-300 text-forest focus:ring-forest/20">
                    <label for="isAktif" class="ml-2 text-sm text-slate-600">Akun Aktif</label>
                </div>

                <div class="flex justify-end pt-4 border-t sticky bottom-0 bg-white pb-2">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                        {{ $userId ? 'Simpan Perubahan' : 'Buat User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Password Sementara --}}
    @if ($showPasswordModal)
    <div class="fixed inset-0 bg-slate-900 bg-opacity-50" style="z-index: 90;"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 100;">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4">Password Sementara</h3>
            <p class="text-sm text-slate-600 mb-4">User <strong>{{ $nama }}</strong> berhasil dibuat. Berikut password sementaranya:</p>
            <div class="bg-slate-100 p-4 rounded-lg mb-4 flex items-center justify-between">
                <p class="font-mono text-lg text-center tracking-wider flex-1">{{ $passwordSementara }}</p>
                <button onclick="navigator.clipboard.writeText('{{ $passwordSementara }}')" class="ml-2 text-emerald-600 hover:text-emerald-800">
                    <i data-lucide="copy" class="w-5 h-5"></i>
                </button>
            </div>
            <p class="text-xs text-amber-600 mb-4">⚠️ Password ini hanya ditampilkan sekali. Sampaikan ke user segera.</p>
            <button wire:click="closePasswordModal" class="w-full py-2 bg-forest text-white rounded-lg">Tutup</button>
        </div>
    </div>
    @endif
</div>