@extends('layouts.app')

@section('title', 'Input Data Rumah')

@section('content')
<div x-data="rumahForm()" class="space-y-4 max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Input Data Rumah</h1>

    @if ($errors->any())
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('rumah.store') }}" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf

        {{-- Data Warga --}}
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Data Warga</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kepala Keluarga <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kepala_keluarga" value="{{ old('nama_kepala_keluarga') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama lengkap">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIK <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono" placeholder="16 digit" x-model="nik" @input="nik = nik.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Kartu Keluarga <span class="text-red-500">*</span></label>
                    <input type="text" name="no_kk" value="{{ old('no_kk') }}" maxlength="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono" placeholder="16 digit" x-model="noKk" @input="noKk = noKk.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Anggota</label>
                    <input type="number" name="jumlah_anggota" value="{{ old('jumlah_anggota') }}" min="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pekerjaan</label>
                    <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. Petani, Buruh">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Penghasilan (Rp/bulan)</label>
                    <input type="number" name="penghasilan" value="{{ old('penghasilan') }}" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. 1500000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Kepemilikan</label>
                    <select name="status_kepemilikan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Pilih...</option>
                        <option value="milik_sendiri" {{ old('status_kepemilikan') == 'milik_sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                        <option value="sewa" {{ old('status_kepemilikan') == 'sewa' ? 'selected' : '' }}>Sewa</option>
                        <option value="menumpang" {{ old('status_kepemilikan') == 'menumpang' ? 'selected' : '' }}>Menumpang</option>
                        <option value="lainnya" {{ old('status_kepemilikan') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                    <input type="number" name="luas_rumah" value="{{ old('luas_rumah') }}" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Mis. 36.50">
                </div>

                {{-- Cascading Wilayah: Kabupaten/Kota -> Kecamatan -> Kelurahan -> RW & RT --}}
                <div class="md:col-span-2 space-y-4 rounded-xl border border-slate-200 bg-slate-50/50 p-4" id="wilayahCascadeWrapper">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Lokasi &amp; Wilayah Administratif <span class="text-red-500">*</span></p>
                        <p class="text-xs text-slate-500">Pilih wilayah berjenjang sesuai data Kemendagri (Kabupaten &rarr; Kecamatan &rarr; Kelurahan), lalu isi nomor RW dan RT.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        {{-- Kabupaten / Kota --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kabupaten/Kota <span class="text-red-500">*</span></label>
                            <select id="kabupatenSelect" name="kabupaten_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-forest focus:outline-none">
                                <option value="">Pilih Kabupaten/Kota...</option>
                                @foreach ($kabupatenList as $kab)
                                    <option value="{{ $kab->id }}" {{ old('kabupaten_id') == $kab->id ? 'selected' : '' }}>{{ $kab->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kecamatan --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                            <select id="kecamatanSelect" name="kecamatan_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-forest focus:outline-none disabled:bg-slate-100 disabled:text-slate-400" {{ empty($kecamatanDirect) && !old('kabupaten_id') ? 'disabled' : '' }}>
                                <option value="">{{ old('kabupaten_id') ? 'Pilih Kecamatan...' : 'Pilih kabupaten dulu' }}</option>
                                @if (!empty($kecamatanDirect))
                                    @foreach ($kecamatanDirect as $kec)
                                        <option value="{{ $kec->id }}" {{ old('kecamatan_id') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Kelurahan / Desa --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kelurahan/Desa <span class="text-red-500">*</span></label>
                            <select id="kelurahanSelect" name="kelurahan_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-forest focus:outline-none disabled:bg-slate-100 disabled:text-slate-400" {{ !old('kecamatan_id') ? 'disabled' : '' }}>
                                <option value="">{{ old('kecamatan_id') ? 'Pilih Kelurahan...' : 'Pilih kecamatan dulu' }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- RW & RT Manual Input (Sesuai Kemendagri & Mockup Abel) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-slate-200">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">RW <span class="text-red-500">*</span></label>
                            <input type="text" name="rw_text" id="rwTextInput" value="{{ old('rw_text') }}" maxlength="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white placeholder-slate-400 focus:ring-2 focus:ring-forest focus:outline-none" placeholder="cth: 005 atau 05">
                            <p class="text-[11px] text-slate-400 mt-0.5">Nomor Rukun Warga</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">RT <span class="text-red-500">*</span></label>
                            <input type="text" name="rt_text" id="rtTextInput" value="{{ old('rt_text') }}" maxlength="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white placeholder-slate-400 focus:ring-2 focus:ring-forest focus:outline-none" placeholder="cth: 003 atau 01">
                            <p class="text-[11px] text-slate-400 mt-0.5">Nomor Rukun Tetangga</p>
                        </div>
                    </div>

                    {{-- Hidden input fallback jika wilayah_id diset langsung --}}
                    <input type="hidden" name="wilayah_id" id="wilayahIdFallback" value="{{ old('wilayah_id') }}">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
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
                            <option value="{{ $surveyor->id }}" {{ old('surveyor_id') == $surveyor->id ? 'selected' : '' }}>{{ $surveyor->nama }} ({{ $surveyor->wilayah?->nama ?? 'Tanpa Wilayah' }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Pilih surveyor yang akan menangani rumah ini. Surveyor hanya bisa melihat rumah di wilayah tugasnya.</p>
                </div>
            </div>
        </div>

        {{-- GPS --}}
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3 pb-2 border-b">Koordinat GPS</h2>
            <div class="flex items-center gap-3 mb-3">
                <button type="button" @click="ambilGPS()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    📍 Ambil GPS
                </button>
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

        {{-- Tombol --}}
        <div class="flex justify-between pt-4 border-t">
            <a href="{{ route('rumah.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Batal</a>
            <div class="space-x-2">
                <button type="submit" name="action" value="draft" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">
                    Simpan Draf
                </button>
                <button type="submit" name="action" value="save" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function rumahForm() {
    return {
        nik: '',
        noKk: '',
        latitude: '{{ old('latitude') }}',
        longitude: '{{ old('longitude') }}',
        gpsLoading: false,

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

document.addEventListener('DOMContentLoaded', function () {
    const kabSelect = document.getElementById('kabupatenSelect');
    const kecSelect = document.getElementById('kecamatanSelect');
    const kelSelect = document.getElementById('kelurahanSelect');

    if (!kabSelect) return;

    kabSelect.addEventListener('change', async function () {
        const kabId = this.value;
        kecSelect.innerHTML = '<option value="">Memuat kecamatan...</option>';
        kecSelect.disabled = true;
        kelSelect.innerHTML = '<option value="">Pilih kecamatan dulu</option>';
        kelSelect.disabled = true;

        if (!kabId) {
            kecSelect.innerHTML = '<option value="">Pilih kabupaten dulu</option>';
            return;
        }

        try {
            const res = await fetch(`/wilayah/${kabId}/children`);
            if (res.ok) {
                const data = await res.json();
                kecSelect.innerHTML = '<option value="">Pilih Kecamatan...</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama;
                    kecSelect.appendChild(opt);
                });
                kecSelect.disabled = false;
            }
        } catch (err) {
            console.error('Gagal memuat kecamatan:', err);
            kecSelect.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    });

    kecSelect.addEventListener('change', async function () {
        const kecId = this.value;
        kelSelect.innerHTML = '<option value="">Memuat kelurahan...</option>';
        kelSelect.disabled = true;

        if (!kecId) {
            kelSelect.innerHTML = '<option value="">Pilih kecamatan dulu</option>';
            return;
        }

        try {
            const res = await fetch(`/wilayah/${kecId}/children`);
            if (res.ok) {
                const data = await res.json();
                kelSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa...</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama;
                    kelSelect.appendChild(opt);
                });
                kelSelect.disabled = false;
            }
        } catch (err) {
            console.error('Gagal memuat kelurahan:', err);
            kelSelect.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    });
});
</script>
@endsection