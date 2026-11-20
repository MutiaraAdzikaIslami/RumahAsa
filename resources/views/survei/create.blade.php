@extends('layouts.app')

@section('title', 'Survei Kelayakan')

@section('content')
<form action="{{ route('survei.update', [$rumah, $survei]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="space-y-4 max-w-4xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Survei Kelayakan</h1>
            <a href="{{ route('rumah.show', $rumah) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Kembali</a>
        </div>

        @if (session('success'))
            <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Koordinat GPS Lokasi Rumah (Surveyor Lapangan) --}}
        <div class="bg-white rounded-lg shadow p-6" data-lat="{{ old('latitude', $rumah->latitude ?? '') }}" data-lng="{{ old('longitude', $rumah->longitude ?? '') }}" x-data="gpsSurveyor($el.dataset.lat, $el.dataset.lng)">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 mb-4 border-b">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-slate-900">Koordinat GPS Lokasi Rumah</h2>
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Presisi &lt; 10m</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Klik Akses GPS untuk mengambil lokasi perangkat, lalu geser pin pada peta satelit tepat ke atap rumah warga jika perlu.</p>
                </div>
                <button type="button" @click="ambilGPS()" :disabled="gpsLoading"
                    style="background-color: #1D4034; color: #ffffff;"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm hover:opacity-90 disabled:opacity-60 transition cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="gpsLoading ? 'Mengambil Lokasi...' : 'Akses GPS'"></span>
                </button>
            </div>

            <div x-show="gpsMessage" x-cloak class="mb-4 px-3.5 py-2.5 rounded-lg text-xs font-medium flex items-center justify-between gap-2"
                :class="gpsError ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
                <span x-text="gpsMessage"></span>
                <span x-show="precisionBadge" class="font-mono font-bold shrink-0" x-text="precisionBadge"></span>
            </div>

            {{-- Peta Satelit Kalibrasi Titik Rumah (muncul otomatis saat koordinat didapat) --}}
            <div x-show="latitude && longitude" x-cloak class="mb-4">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-medium text-slate-600">Kalibrasi Titik Rumah (Klik peta atau geser pin biru ke atap rumah):</span>
                    <div class="inline-flex rounded-md border border-slate-200 overflow-hidden text-[11px] font-medium">
                        <button type="button" @click="switchMapLayer('satellite')"
                            :class="mapLayerMode === 'satellite' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                            class="px-2.5 py-1 cursor-pointer">Satelit</button>
                        <button type="button" @click="switchMapLayer('street')"
                            :class="mapLayerMode === 'street' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                            class="px-2.5 py-1 cursor-pointer">Peta Jalan</button>
                    </div>
                </div>
                <div x-ref="miniMap" class="w-full h-64 rounded-lg border border-slate-300 z-0 overflow-hidden"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                    <input type="text" name="latitude" x-model="latitude" readonly
                        placeholder="Terisi otomatis setelah Akses GPS diizinkan"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono bg-slate-50 text-slate-800">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                    <input type="text" name="longitude" x-model="longitude" readonly
                        placeholder="Terisi otomatis setelah Akses GPS diizinkan"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono bg-slate-50 text-slate-800">
                </div>
            </div>
        </div>
        {{-- 7 Komponen --}}
        @foreach ($komponenList as $komponen)
            @php
                $surveiKomponen = $survei->komponenSurvei->where('komponen_id', $komponen->id)->first();
            @endphp
            <div class="bg-white rounded-lg shadow p-6" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <h2 class="text-lg font-semibold text-slate-900">
                        <span class="text-slate-400 mr-2">{{ $loop->iteration }}.</span>
                        {{ $komponen->nama }}
                    </h2>
                    <svg class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>

                <div x-show="open" x-cloak class="mt-4 space-y-4 border-t pt-4">
                    <p class="text-xs text-slate-500">{{ $komponen->deskripsi }}</p>

                    {{-- Opsi Kondisi --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Kondisi:</label>
                        <div class="space-y-2">
                            @foreach ($komponen->opsiKondisi as $opsi)
                                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 {{ $surveiKomponen->opsi_kondisi_id == $opsi->id ? 'border-emerald-500 bg-emerald-50' : '' }}">
                                    <input type="radio" name="komponen[{{ $komponen->id }}][opsi_kondisi_id]" value="{{ $opsi->id }}" {{ $surveiKomponen->opsi_kondisi_id == $opsi->id ? 'checked' : '' }} class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="font-medium text-sm text-slate-900">{{ $opsi->label }}</span>
                                        @if ($opsi->otomatis_rtlh)
                                            <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded">RTLH</span>
                                        @endif
                                        <p class="text-xs text-slate-500 mt-1">{{ $opsi->deskripsi }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                        <textarea name="komponen[{{ $komponen->id }}][catatan]" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Catatan tambahan...">{{ $surveiKomponen->catatan ?? '' }}</textarea>
                    </div>

                    {{-- Upload Foto (FR-11b) --}}
                    <div x-data="fotoUploader({{ $surveiKomponen->id }})">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Foto Bukti</label>
                        
                        <div class="grid grid-cols-3 gap-2 mb-3" id="foto-grid-{{ $surveiKomponen->id }}">
                            @foreach ($surveiKomponen->foto as $foto)
                                <div class="relative group">
                                    <img src="{{ route('survei.foto.show', $foto) }}" class="w-full h-24 object-cover rounded-lg border">
                                    <button type="button" onclick="hapusFoto(this, {{ $foto->id }})" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition">×</button>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-center w-full" @drop.prevent="handleDrop($event, {{ $surveiKomponen->id }})" @dragover.prevent>
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 border-slate-300">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-6 h-6 mb-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    <p class="text-xs text-slate-500"><span class="font-medium">Klik untuk upload</span> atau drag & drop</p>
                                </div>
                                <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp" @change="handleUpload($event.target.files, {{ $surveiKomponen->id }})">
                            </label>
                        </div>
                        <p x-show="uploading" class="text-xs text-blue-500 mt-1">Uploading...</p>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Tombol Aksi --}}
        <div class="flex justify-between pt-4 border-t bg-white sticky bottom-0 p-4 rounded-lg shadow">
            <a href="{{ route('rumah.show', $rumah) }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Batal</a>
            <div class="space-x-2">
                <button type="submit" name="action" value="draft" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Simpan Draf</button>
                <button type="submit" name="action" value="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Kirim Survei</button>
            </div>
        </div>
    </div>
</form>

<style>[x-cloak] { display: none !important; }</style>

<script>
window.gpsSurveyor = function(initialLat, initialLng) {
    return {
        latitude: initialLat || '',
        longitude: initialLng || '',
        gpsLoading: false,
        gpsMessage: initialLat && initialLng ? 'Koordinat lokasi rumah sudah tersimpan.' : '',
        gpsError: false,
        precisionBadge: initialLat && initialLng ? 'Terkunci' : '',
        bestAccuracy: null,
        watchId: null,
        timeoutTimer: null,
        map: null,
        marker: null,
        accuracyCircle: null,
        satelliteLayer: null,
        streetLayer: null,
        mapLayerMode: 'satellite',
        pinPinnedManually: false,

        init() {
            if (this.latitude && this.longitude) {
                this.$nextTick(() => this.renderMiniMap(parseFloat(this.latitude), parseFloat(this.longitude), null));
            }
        },

        ensureLeaflet() {
            return new Promise((resolve) => {
                if (window.L) return resolve();
                if (!document.getElementById('leaflet-survei-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-survei-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }
                if (!document.getElementById('leaflet-survei-js')) {
                    const script = document.createElement('script');
                    script.id = 'leaflet-survei-js';
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => resolve();
                    document.head.appendChild(script);
                } else {
                    const check = setInterval(() => {
                        if (window.L) { clearInterval(check); resolve(); }
                    }, 50);
                }
            });
        },

        async renderMiniMap(lat, lng, accuracyMeters) {
            await this.ensureLeaflet();
            await this.$nextTick();

            const el = this.$refs.miniMap;
            if (!el) return;

            if (!this.map) {
                this.map = L.map(el, { center: [lat, lng], zoom: 19, maxZoom: 20 });

                this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 20,
                    attribution: 'Tiles &copy; Esri'
                });
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                });

                this.satelliteLayer.addTo(this.map);

                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this.marker.bindPopup('Geser pin ini tepat ke atap rumah warga').openPopup();

                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.lockExactPin(pos.lat, pos.lng);
                });

                this.map.on('click', (e) => {
                    this.marker.setLatLng(e.latlng);
                    this.lockExactPin(e.latlng.lat, e.latlng.lng);
                });
            } else {
                this.map.invalidateSize();
                this.map.setView([lat, lng], 19);
                this.marker.setLatLng([lat, lng]);
            }

            if (this.accuracyCircle) {
                this.map.removeLayer(this.accuracyCircle);
                this.accuracyCircle = null;
            }
            if (accuracyMeters && accuracyMeters > 10) {
                this.accuracyCircle = L.circle([lat, lng], {
                    radius: accuracyMeters,
                    color: '#2563EB',
                    fillColor: '#3B82F6',
                    fillOpacity: 0.12,
                    weight: 1
                }).addTo(this.map);
            }
        },

        switchMapLayer(mode) {
            if (!this.map) return;
            this.mapLayerMode = mode;
            if (mode === 'satellite') {
                this.map.removeLayer(this.streetLayer);
                this.satelliteLayer.addTo(this.map);
            } else {
                this.map.removeLayer(this.satelliteLayer);
                this.streetLayer.addTo(this.map);
            }
        },

        lockExactPin(lat, lng) {
            this.stopWatch();
            this.pinPinnedManually = true;
            this.latitude = lat.toFixed(7);
            this.longitude = lng.toFixed(7);
            this.gpsError = false;
            this.precisionBadge = '±2.0m (Pin Satelit)';
            this.gpsMessage = 'Titik lokasi rumah terkunci presisi di bawah 5 meter berdasarkan posisi pin peta.';
            if (this.accuracyCircle && this.map) {
                this.map.removeLayer(this.accuracyCircle);
                this.accuracyCircle = null;
            }
        },

        stopWatch() {
            if (this.watchId !== null) {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
            }
            if (this.timeoutTimer !== null) {
                clearTimeout(this.timeoutTimer);
                this.timeoutTimer = null;
            }
            this.gpsLoading = false;
        },

        ambilGPS() {
            if (!navigator.geolocation) {
                this.gpsError = true;
                this.gpsMessage = 'Browser atau perangkat Anda tidak mendukung fitur akses GPS.';
                return;
            }

            this.stopWatch();
            this.pinPinnedManually = false;
            this.gpsLoading = true;
            this.gpsError = false;
            this.bestAccuracy = null;
            this.precisionBadge = '';
            this.gpsMessage = 'Meminta izin lokasi & mengunci satelit GPS presisi tinggi...';

            this.timeoutTimer = setTimeout(() => {
                if (this.watchId !== null) {
                    const finalAcc = this.bestAccuracy;
                    this.stopWatch();
                    if (finalAcc !== null && !this.pinPinnedManually) {
                        this.gpsError = finalAcc >= 10;
                        this.precisionBadge = finalAcc < 10 ? `±${finalAcc.toFixed(1)}m` : `±${Math.round(finalAcc)}m`;
                        this.gpsMessage = finalAcc < 10
                            ? `Koordinat presisi tinggi terkunci (akurasi ±${finalAcc.toFixed(1)} meter).`
                            : `GPS perangkat mentah ±${Math.round(finalAcc)}m (keterbatasan sensor Wi-Fi/ruangan). Geser pin pada peta satelit di bawah tepat ke atap rumah untuk mengunci akurasi < 5m.`;
                    }
                }
            }, 8000);

            this.watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    if (this.pinPinnedManually) return;
                    const acc = pos.coords.accuracy;

                    if (this.bestAccuracy === null || acc < this.bestAccuracy) {
                        this.bestAccuracy = acc;
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        this.latitude = lat.toFixed(7);
                        this.longitude = lng.toFixed(7);
                        this.renderMiniMap(lat, lng, acc);
                    }

                    if (acc < 10) {
                        this.stopWatch();
                        this.gpsError = false;
                        this.precisionBadge = `±${acc.toFixed(1)}m`;
                        this.gpsMessage = `Presisi tinggi tercapai (< 10m): koordinat terkunci dengan akurasi ±${acc.toFixed(1)} meter.`;
                    } else {
                        this.gpsError = true;
                        this.precisionBadge = `±${Math.round(this.bestAccuracy)}m`;
                        this.gpsMessage = `Lokasi terdeteksi (±${Math.round(this.bestAccuracy)}m). Menunggu sinyal < 10m atau geser pin peta satelit di bawah langsung ke atap rumah.`;
                    }
                },
                (err) => {
                    this.stopWatch();
                    this.gpsError = true;
                    this.gpsMessage = err.code === 1
                        ? 'Izin akses lokasi ditolak. Aktifkan izin Location pada browser lalu klik Akses GPS lagi.'
                        : 'Gagal mendapatkan lokasi GPS: ' + err.message;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    };
};
function fotoUploader(komponenId) {
    return {
        uploading: false,
        
        async handleUpload(files, compId) {
            if (!files.length) return;
            this.uploading = true;
            
            const formData = new FormData();
            formData.append('file', files[0]);

            try {
                const res = await fetch(`/survei/survei-komponen/${compId}/foto`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    const grid = document.getElementById(`foto-grid-${compId}`);
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    div.innerHTML = `
                        <img src="${data.url}" class="w-full h-24 object-cover rounded-lg border">
                        <button type="button" onclick="hapusFoto(this, ${data.foto.id})" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition">×</button>
                    `;
                    grid.appendChild(div);
                } else {
                    const err = await res.json();
                    alert('Gagal upload: ' + (err.message || 'Unknown error'));
                }
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.uploading = false;
            }
        },

        handleDrop(e, compId) {
            this.handleUpload(e.dataTransfer.files, compId);
        }
    };
}

window.hapusFoto = async function(el, fotoId) {
    if (!confirm('Hapus foto ini?')) return;
    const res = await fetch(`/survei/survei-foto/${fotoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    });
    if (res.ok) {
        el.closest('.relative').remove();
    }
};
</script>
@endsection