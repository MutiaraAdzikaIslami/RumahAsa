@extends('layouts.app')

@section('title', 'Edit Data Rumah')

@section('content')
<div x-data="rumahForm()" class="space-y-4 max-w-3xl">
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

        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Data Warga</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kepala Keluarga</label>
                    <input type="text" name="nama_kepala_keluarga" value="{{ old('nama_kepala_keluarga', $rumah->nama_kepala_keluarga) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $nikPlain) }}" maxlength="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono" x-model="nik" @input="nik = nik.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Kartu Keluarga</label>
                    <input type="text" name="no_kk" value="{{ old('no_kk', $noKkPlain) }}" maxlength="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono" x-model="noKk" @input="noKk = noKk.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Anggota</label>
                    <input type="number" name="jumlah_anggota" value="{{ old('jumlah_anggota', $rumah->jumlah_anggota) }}" min="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pekerjaan</label>
                    <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $rumah->pekerjaan) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Penghasilan</label>
                    <input type="number" name="penghasilan" value="{{ old('penghasilan', $rumah->penghasilan) }}" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Kepemilikan</label>
                    <select name="status_kepemilikan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih...</option>
                        <option value="milik_sendiri" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'milik_sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                        <option value="sewa" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'sewa' ? 'selected' : '' }}>Sewa</option>
                        <option value="menumpang" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'menumpang' ? 'selected' : '' }}>Menumpang</option>
                        <option value="lainnya" {{ old('status_kepemilikan', $rumah->status_kepemilikan) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Luas Rumah (m²)</label>
                    <input type="number" name="luas_rumah" value="{{ old('luas_rumah', $rumah->luas_rumah) }}" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Data Rumah</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Wilayah (RT / RW)</label>
                    <select name="wilayah_id" x-model="wilayahId" @change="onWilayahChange()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih Wilayah (RT / RW)...</option>
                        @foreach ($rtList as $rt)
                            <option value="{{ $rt->id }}" {{ old('wilayah_id', $rumah->wilayah_id) == $rt->id ? 'selected' : '' }}>
                                [{{ strtoupper($rt->tingkat) }}] {{ $rt->nama_lengkap }} ({{ $rt->kode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('alamat', $rumah->alamat) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Penugasan Surveyor --}}
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Penugasan Surveyor</h2>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Surveyor yang Bertugas</label>
                    <select name="surveyor_id" x-model="surveyorId" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih Surveyor...</option>
                        @foreach ($surveyorList as $surveyor)
                            <option value="{{ $surveyor->id }}" {{ old('surveyor_id', $rumah->surveyor_id) == $surveyor->id ? 'selected' : '' }}>{{ $surveyor->nama }} (Wilayah Tugas: {{ $surveyor->wilayah?->nama ?? 'Tanpa Wilayah' }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Pilih surveyor yang bertugas. Surveyor hanya dapat melihat dan menyurvei rumah di wilayah tugasnya.</p>
                    <div x-show="surveyorWarning" class="mt-2 p-2.5 rounded bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-1.5">
                        <span class="font-bold">⚠️</span>
                        <span x-text="surveyorWarning"></span>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Koordinat GPS</h2>
            <div class="flex items-center gap-3 mb-3">
                <button type="button" @click="ambilGPS()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">📍 Ambil GPS</button>
                <span x-show="gpsLoading" class="text-sm text-slate-500">Mengambil lokasi...</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                    <input type="text" name="latitude" x-model="latitude" readonly class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                    <input type="text" name="longitude" x-model="longitude" readonly class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono bg-slate-50">
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-4 border-t">
            <a href="{{ route('rumah.show', $rumah) }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Batal</a>
            <button type="submit" name="action" value="draft" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
function rumahForm() {
    return {
        nik: '{{ old('nik', $nikPlain) }}',
        noKk: '{{ old('no_kk', $noKkPlain) }}',
        latitude: '{{ old('latitude', $rumah->latitude) }}',
        longitude: '{{ old('longitude', $rumah->longitude) }}',
        gpsLoading: false,
        wilayahId: '{{ old('wilayah_id', $rumah->wilayah_id) }}',
        surveyorId: '{{ old('surveyor_id', $rumah->surveyor_id) }}',
        wilayahList: @json($wilayahData),
        surveyorList: @json($surveyorData),

        onWilayahChange() {
            if (!this.wilayahId) return;
            const selectedW = this.wilayahList.find(w => w.id == this.wilayahId);
            if (!selectedW) return;

            // Cari surveyor yang wilayah tugasnya menaungi wilayah terpilih
            const matchingSurveyor = this.surveyorList.find(s => {
                if (!s.wilayah_path) return false;
                const sPath = s.wilayah_path.replace(/\/+$/, '') + '/';
                const wPath = selectedW.path.replace(/\/+$/, '') + '/';
                return selectedW.id == s.wilayah_id || wPath.startsWith(sPath);
            });

            if (matchingSurveyor) {
                this.surveyorId = matchingSurveyor.id;
            }
        },

        get surveyorWarning() {
            if (!this.wilayahId || !this.surveyorId) return null;
            const selectedW = this.wilayahList.find(w => w.id == this.wilayahId);
            const selectedS = this.surveyorList.find(s => s.id == this.surveyorId);
            if (!selectedW || !selectedS) return null;

            if (!selectedS.wilayah_path) {
                return `Surveyor ${selectedS.nama} belum memiliki wilayah tugas.`;
            }
            const sPath = selectedS.wilayah_path.replace(/\/+$/, '') + '/';
            const wPath = selectedW.path.replace(/\/+$/, '') + '/';
            if (selectedW.id != selectedS.wilayah_id && !wPath.startsWith(sPath)) {
                return `Surveyor ${selectedS.nama} bertugas di ${selectedS.wilayah_nama}, tidak mencakup wilayah ${selectedW.nama}. Rumah ini tidak akan muncul di daftar tugas surveyor tersebut!`;
            }
            return null;
        },

        ambilGPS() {
            if (!navigator.geolocation) {
                alert('Browser tidak mendukung GPS.');
                return;
            }
            this.gpsLoading = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.latitude = pos.coords.latitude.toFixed(7);
                    this.longitude = pos.coords.longitude.toFixed(7);
                    this.gpsLoading = false;
                },
                (err) => {
                    alert('Gagal mengambil GPS: ' + err.message);
                    this.gpsLoading = false;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },
    };
}
</script>
@endsection