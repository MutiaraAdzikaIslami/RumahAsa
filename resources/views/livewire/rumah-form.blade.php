<div>
    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Data Kepala Keluarga</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kepala Keluarga <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="namaKepalaKeluarga" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama lengkap">
                    @error('namaKepalaKeluarga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIK (16 digit) <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nik" maxlength="16" inputmode="numeric" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="{{ $rumahId ? 'Kosongkan jika tidak diubah' : '16 digit angka' }}">
                    @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @if ($rumahId) <p class="text-slate-400 text-xs mt-1">NIK tersimpan terenkripsi. Isi hanya jika ingin mengubah.</p> @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Kartu Keluarga (16 digit) <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="noKk" maxlength="16" inputmode="numeric" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="{{ $rumahId ? 'Kosongkan jika tidak diubah' : '16 digit angka' }}">
                    @error('noKk') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Anggota Keluarga <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="jumlahAnggota" min="1" max="99" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: 4">
                    @error('jumlahAnggota') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pekerjaan</label>
                    <input type="text" wire:model="pekerjaan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: Buruh harian lepas">
                    @error('pekerjaan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Penghasilan per Bulan</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none">Rp</span>
                        <input
                            type="text"
                            wire:model="penghasilan"
                            inputmode="numeric"
                            class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm"
                            placeholder="cth: 2.500.000"
                            x-data
                            x-on:input="
                                let v = $event.target.value.replace(/[^0-9]/g, '');
                                $event.target.value = v ? new Intl.NumberFormat('id-ID').format(parseInt(v)) : '';
                            "
                        >
                    </div>
                    @error('penghasilan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Luas Rumah (m&sup2;)</label>
                    <input type="number" step="0.01" min="0" wire:model="luasRumah" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: 36">
                    @error('luasRumah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Kepemilikan <span class="text-red-500">*</span></label>
                    <select wire:model="statusKepemilikan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih Status...</option>
                        <option value="milik_sendiri">Milik Sendiri</option>
                        <option value="sewa">Sewa</option>
                        <option value="menumpang">Menumpang</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                    @error('statusKepemilikan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-1">Lokasi Rumah</h3>
            <p class="text-xs text-slate-500 mb-4">Pilih wilayah bertingkat: Kabupaten/Kota -> Kecamatan -> Kelurahan/Desa, lalu isi RW dan RT secara manual.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kabupaten/Kota <span class="text-red-500">*</span></label>
                    <select wire:model.live="kabupatenId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih Kabupaten/Kota...</option>
                        @foreach ($kabupatenList as $kab)
                            <option value="{{ $kab->id }}">{{ $kab->nama }}</option>
                        @endforeach
                    </select>
                    @error('kabupatenId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                    <select wire:model.live="kecamatanId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" {{ !$kabupatenId ? 'disabled' : '' }}>
                        <option value="">{{ $kabupatenId ? 'Pilih Kecamatan...' : 'Pilih kabupaten dulu' }}</option>
                        @foreach ($kecamatanList as $kec)
                            <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                    @error('kecamatanId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelurahan/Desa <span class="text-red-500">*</span></label>
                    <select wire:model.live="kelurahanId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" {{ !$kecamatanId ? 'disabled' : '' }}>
                        <option value="">{{ $kecamatanId ? 'Pilih Kelurahan/Desa...' : 'Pilih kecamatan dulu' }}</option>
                        @foreach ($kelurahanList as $kel)
                            <option value="{{ $kel->id }}">{{ $kel->nama }}</option>
                        @endforeach
                    </select>
                    @error('kelurahanId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RW <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="rwText" maxlength="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: 005" {{ !$kelurahanId ? 'disabled' : '' }}>
                    @error('rwText') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RT <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="rtText" maxlength="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: 003" {{ !$kelurahanId ? 'disabled' : '' }}>
                    @error('rtText') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea wire:model="alamat" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama jalan, patokan, dll (tanpa RT/RW)"></textarea>
                @error('alamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Latitude (opsional)</label>
                    <input type="text" wire:model="latitude" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: -6.21462">
                    @error('latitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Longitude (opsional)</label>
                    <input type="text" wire:model="longitude" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="cth: 106.84513">
                    @error('longitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Penugasan Surveyor</h3>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Surveyor yang Ditugaskan <span class="text-red-500">*</span></label>
                <select wire:model="surveyorId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">Pilih Surveyor...</option>
                    @foreach ($surveyors as $surveyor)
                        <option value="{{ $surveyor->id }}">{{ $surveyor->nama }} ({{ $surveyor->username }})</option>
                    @endforeach
                </select>
                @error('surveyorId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @if ($surveyors->isEmpty())
                    <p class="text-amber-600 text-xs mt-1">Belum ada surveyor aktif. Buat user dengan role Surveyor terlebih dahulu.</p>
                @endif
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <a href="{{ route('rumah.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 mr-2">Batal</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                {{ $rumahId ? 'Simpan Perubahan' : 'Simpan Data Rumah' }}
            </button>
        </div>
    </form>
</div>
