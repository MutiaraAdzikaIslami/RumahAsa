@extends('layouts.app')

@section('title', 'Edit Data Rumah')

@section('content')
<div x-data="rumahForm()" x-init="init()" class="space-y-4 max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Edit Data Rumah</h1>

    @if ($errors->any())
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('rumah.update', $rumah) }}" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Data Warga --}}
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Data Warga</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kepala Keluarga <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kepala_keluarga" value="{{ old('nama_kepala_keluarga', $rumah->nama_kepala_keluarga) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIK <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" maxlength="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono" x-model="nik" @input="nik = nik.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Kartu Keluarga <span class="text-red-500">*</span></label>
                    <input type="text" name="no_kk" maxlength="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono" x-model="noKk" @input="noKk = noKk.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Anggota <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_anggota" value="{{ old('jumlah_anggota', $rumah->jumlah_anggota) }}" min="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pekerjaan</label>
                    <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $rumah->pekerjaan) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Penghasilan (Rp/bulan)</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-slate-500">Rp</span>
                        <input type="text"
                               name="penghasilan"
                               inputmode="numeric"
                               autocomplete="off"
                               x-model="penghasilanDisplay"
                               @input="onPenghasilanInput($event)"
                               class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm font-mono"
                               placeholder="Mis. 1.500.000">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Kepemilikan <span class="text-red-500">*</span></label>
                    <select name="status_kepemilikan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih...</option>
                        <option value="milik_sendiri" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'milik_sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                        <option value="sewa" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'sewa' ? 'selected' : '' }}>Sewa</option>
                        <option value="menumpang" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'menumpang' ? 'selected' : '' }}>Menumpang</option>
                        <option value="lainnya" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Data Rumah --}}
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Data Rumah</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Luas Rumah (m²)</label>
                    <input type="number" name="luas_rumah" value="{{ old('luas_rumah', $rumah->luas_rumah) }}" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. 36.50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kabupaten/Kota <span class="text-red-500">*</span></label>
                    <select name="kabupaten_id"
                            x-model="kabupatenId"
                            @change="onKabupatenChange()"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white">
                        <option value="">Pilih Kabupaten/Kota...</option>
                        @foreach ($kabupatenList as $kab)
                            <option value="{{ $kab->id }}">{{ $kab->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                    <select name="kecamatan_id"
                            x-model="kecamatanId"
                            @change="onKecamatanChange()"
                            :disabled="!kabupatenId"
                            :class="!kabupatenId ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-900'"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih Kecamatan...</option>
                        <template x-for="kec in kecamatanOptions" :key="kec.id">
                            <option :value="String(kec.id)" x-text="kec.nama"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelurahan/Desa <span class="text-red-500">*</span></label>
                    <select name="wilayah_id"
                            x-model="wilayahId"
                            :disabled="!kecamatanId || loadingKelurahan"
                            :class="(!kecamatanId || loadingKelurahan) ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-900'"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="" x-text="loadingKelurahan ? 'Memuat kelurahan...' : 'Pilih Kelurahan/Desa...'"></option>
                        <template x-for="kel in kelurahanOptions" :key="kel.id">
                            <option :value="String(kel.id)" x-text="kel.nama"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RW <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="rw_text"
                           maxlength="10"
                           value="{{ old('rw_text', $rwText) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"
                           placeholder="Mis. 01">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RT <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="rt_text"
                           maxlength="10"
                           value="{{ old('rt_text', $rtText) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"
                           placeholder="Mis. 02">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('alamat', $rumah->alamat) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Penugasan Surveyor --}}
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Penugasan Surveyor</h2>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Surveyor yang Bertugas <span class="text-red-500">*</span></label>
                    <select name="surveyor_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih Surveyor...</option>
                        @foreach ($surveyorList as $surveyor)
                            <option value="{{ $surveyor->id }}" {{ old('surveyor_id', $rumah->surveyor_id) == $surveyor->id ? 'selected' : '' }}>
                                {{ $surveyor->nama }} ({{ $surveyor->wilayah?->nama ?? 'Tanpa Wilayah' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-4 border-t">
            <a href="{{ route('rumah.show', $rumah) }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Batal</a>
            <div class="space-x-2">
                @if ($rumah->status === \App\Models\RumahWarga::STATUS_DRAF)
                    <button type="submit" name="action" value="draft" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Simpan Draf</button>
                @endif
                <button type="submit" name="action" value="save" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>

<script>
function formatRibuan(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    if (!digits) return '';
    return digits.replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function rumahForm() {
    const allKecamatan = @json($kecamatanList ?? []);
    const initialKelurahan = @json($initialKelurahanList ?? []);
    const kelurahanUrlTemplate = @json(route('api.wilayah.kelurahan', ['kecamatanId' => 999999999], false));

    return {
        nik: @json((string) old('nik', $nikPlain)),
        noKk: @json((string) old('no_kk', $noKkPlain)),
        penghasilanDisplay: formatRibuan(@json((string) old('penghasilan', $rumah->penghasilan ?? ''))),
        kabupatenId: @json((string) old('kabupaten_id', $selectedKabupatenId ?? '')),
        kecamatanId: @json((string) old('kecamatan_id', $selectedKecamatanId ?? '')),
        wilayahId: @json((string) old('wilayah_id', $selectedKelurahanId ?? '')),
        kecamatanOptions: [],
        kelurahanOptions: initialKelurahan,
        loadingKelurahan: false,

        async init() {
            if (this.kabupatenId) {
                this.kecamatanOptions = allKecamatan.filter(
                    item => String(item.parent_id) === String(this.kabupatenId)
                );
            }
            if (this.kecamatanId && this.kelurahanOptions.length === 0) {
                const preservedWilayahId = this.wilayahId;
                await this.loadKelurahan(this.kecamatanId);
                this.wilayahId = preservedWilayahId;
            }
        },

        onPenghasilanInput(event) {
            const formatted = formatRibuan(event.target.value);
            this.penghasilanDisplay = formatted;
            event.target.value = formatted;
        },

        onKabupatenChange() {
            this.kecamatanId = '';
            this.wilayahId = '';
            this.kelurahanOptions = [];
            if (!this.kabupatenId) {
                this.kecamatanOptions = [];
                return;
            }
            this.kecamatanOptions = allKecamatan.filter(
                item => String(item.parent_id) === String(this.kabupatenId)
            );
        },

        async onKecamatanChange() {
            this.wilayahId = '';
            this.kelurahanOptions = [];
            if (!this.kecamatanId) return;
            await this.loadKelurahan(this.kecamatanId);
        },

        async loadKelurahan(kecamatanId) {
            this.loadingKelurahan = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const url = kelurahanUrlTemplate.replace('999999999', encodeURIComponent(kecamatanId));
                const res = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const rawText = await res.text();
                const parsed = JSON.parse(rawText.replace(/^\uFEFF/, '').trim());
                this.kelurahanOptions = Array.isArray(parsed) ? parsed : (parsed.data || []);
            } catch (e) {
                console.error('Gagal memuat kelurahan:', e);
                this.kelurahanOptions = [];
            } finally {
                this.loadingKelurahan = false;
            }
        },
    };
}
</script>
@endsection