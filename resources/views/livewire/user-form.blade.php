<div>
    <form wire:submit="save" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" wire:model="nama" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama lengkap">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" wire:model="username" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Username login">
                @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email (Opsional)</label>
                <input type="email" wire:model="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="email@contoh.com">
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
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Wilayah Tugas {{ $isSurveyor ? '(Wajib untuk Surveyor)' : '(Hanya untuk Surveyor)' }}</h3>
            
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
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan</label>
                    <select wire:model.live="kecamatanId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" {{ !$kabupatenId ? 'disabled' : '' }}>
                        <option value="">Pilih Kecamatan...</option>
                        @foreach ($kecamatanList as $kec)
                            <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelurahan/Desa</label>
                    <select wire:model.live="kelurahanId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" {{ !$kecamatanId ? 'disabled' : '' }}>
                        <option value="">Pilih Kelurahan...</option>
                        @foreach ($kelurahanList as $kel)
                            <option value="{{ $kel->id }}">{{ $kel->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RW</label>
                    <select wire:model.live="rwId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" {{ !$kelurahanId ? 'disabled' : '' }}>
                        <option value="">Pilih RW...</option>
                        @foreach ($rwList as $rw)
                            <option value="{{ $rw->id }}">{{ $rw->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RT</label>
                    <select wire:model="rtId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" {{ !$rwId ? 'disabled' : '' }}>
                        <option value="">Pilih RT...</option>
                        @foreach ($rtList as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->nama }}</option>
                        @endforeach
                    </select>
                    @error('rtId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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

        <div class="flex justify-end pt-4 border-t">
            <button type="button" wire:click="$dispatch('close-modal')" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 mr-2">Batal</button>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                {{ $userId ? 'Simpan Perubahan' : 'Buat User' }}
            </button>
        </div>
    </form>

    @if ($showPasswordModal)
    <div class="fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-[100]">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Password Sementara</h3>
            <p class="text-sm text-slate-600 mb-4">User <strong>{{ $nama }}</strong> berhasil dibuat. Berikut password sementaranya:</p>
            <div class="bg-slate-100 p-4 rounded-lg mb-4 flex items-center justify-between">
                <p class="font-mono text-lg text-center tracking-wider flex-1">{{ $passwordSementara }}</p>
                <button onclick="navigator.clipboard.writeText('{{ $passwordSementara }}')" class="ml-2 text-emerald-600 hover:text-emerald-800">
                    <i data-lucide="copy" class="w-5 h-5"></i>
                </button>
            </div>
            <p class="text-xs text-amber-600 mb-4">âš ï¸ Password ini hanya ditampilkan sekali. Sampaikan ke user segera.</p>
            <button wire:click="closePasswordModal" class="w-full py-2 bg-forest text-white rounded-lg">Tutup</button>
        </div>
    </div>
    @endif
</div>