{{-- FR-31 / SCRUM-159: Heatmap Status Rumah Component --}}
@props([
    'role' => 'verifikator',
    'subtitle' => 'Sebaran rumah menurut status: sedang disurvei, sedang renovasi, dan selesai.',
    'wilayahList' => [],
    'sumberProgramList' => ['APBN', 'APBD'],
    'surveyorWilayah' => null,
])

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-8" id="heatmap-component">
    {{-- Header & Filters --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Heatmap Status Rumah</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>
        </div>

        {{-- Filter Controls --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Filter Triwulan --}}
            <div class="relative">
                <select id="heatmap-filter-triwulan" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded-lg pl-3 pr-8 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                    <option value="">Semua Triwulan</option>
                    <option value="1">Triwulan 1</option>
                    <option value="2">Triwulan 2</option>
                    <option value="3">Triwulan 3</option>
                    <option value="4">Triwulan 4</option>
                </select>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            {{-- Filter Wilayah --}}
            <div class="relative">
                @if ($role === 'surveyor')
                    <select id="heatmap-filter-wilayah" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded-lg pl-3 pr-8 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                        <option value="">{{ $surveyorWilayah ? $surveyorWilayah->nama : 'Wilayah Tugas' }}</option>
                        @foreach ($wilayahList as $w)
                            <option value="{{ $w->id }}">{{ $w->nama }}</option>
                        @endforeach
                    </select>
                @else
                    <select id="heatmap-filter-wilayah" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded-lg pl-3 pr-8 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                        <option value="">Semua Wilayah</option>
                        @foreach ($wilayahList as $w)
                            <option value="{{ $w->id }}">{{ $w->nama }}</option>
                        @endforeach
                    </select>
                @endif
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            {{-- Filter Sumber Program --}}
            <div class="relative">
                <select id="heatmap-filter-sumber-program" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded-lg pl-3 pr-8 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                    <option value="">Semua Sumber Program</option>
                    @foreach ($sumberProgramList as $sp)
                        <option value="{{ $sp }}">{{ $sp }}</option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </div>
    </div>

    {{-- Layer Toggle Pills --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        {{-- Layer: Sedang Disurvei --}}
        <button type="button" id="toggle-layer-sedang-disurvei" data-layer="sedang_disurvei" class="heatmap-layer-toggle active flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold border transition duration-150 bg-[#FFF7ED] border-[#FDBA74] text-[#9A3412]">
            <span class="w-2 h-2 rounded-full bg-[#EA580C]"></span>
            <span>Sedang Disurvei</span>
        </button>

        {{-- Layer: Sedang Renovasi --}}
        <button type="button" id="toggle-layer-sedang-renovasi" data-layer="sedang_renovasi" class="heatmap-layer-toggle active flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold border transition duration-150 bg-[#EFF6FF] border-[#BFDBFE] text-[#1E40AF]">
            <span class="w-2 h-2 rounded-full bg-[#2563EB]"></span>
            <span>Sedang Renovasi</span>
        </button>

        {{-- Layer: Selesai --}}
        <button type="button" id="toggle-layer-selesai" data-layer="selesai" class="heatmap-layer-toggle active flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold border transition duration-150 bg-[#ECFDF5] border-[#A7F3D0] text-[#065F46]">
            <span class="w-2 h-2 rounded-full bg-[#059669]"></span>
            <span>Selesai</span>
        </button>

        {{-- Layer: Menunggu Disurvei (Lapisan ke-4 sesuai SCRUM-159) --}}
        <button type="button" id="toggle-layer-menunggu" data-layer="menunggu_disurvei" class="heatmap-layer-toggle active flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold border transition duration-150 bg-[#F8FAFC] border-[#CBD5E1] text-[#334155]">
            <span class="w-2 h-2 rounded-full bg-[#64748B]"></span>
            <span>Menunggu Disurvei</span>
        </button>
    </div>

    {{-- Map & Stats Side Panel --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
        {{-- Map Container (8 / 12 cols on desktop) --}}
        <div class="lg:col-span-8 xl:col-span-9 relative">
            <div id="heatmap-map" class="w-full h-[430px] rounded-xl border border-slate-200 z-0 overflow-hidden bg-slate-50"></div>

            {{-- Surveyor territory badge (top-left inside map) --}}
            @if ($role === 'surveyor' && $surveyorWilayah)
                <div class="absolute top-3 left-3 z-[400] bg-white/95 backdrop-blur-sm border border-slate-200 shadow-sm rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-800 flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>{{ $surveyorWilayah->nama }}</span>
                </div>
            @endif

            {{-- Empty State Overlay --}}
            <div id="heatmap-empty-state" class="hidden absolute inset-0 z-[400] bg-white/70 backdrop-blur-[2px] flex items-center justify-center pointer-events-none rounded-xl">
                <div class="bg-white px-5 py-3 rounded-xl shadow-md border border-slate-200 text-sm font-semibold text-slate-600 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-slate-400"></i>
                    <span>Belum ada rumah pada filter ini.</span>
                </div>
            </div>
        </div>

        {{-- Stats & Area breakdown (4 / 12 cols on desktop) --}}
        <div class="lg:col-span-4 xl:col-span-3 space-y-3 flex flex-col justify-between">
            <div class="space-y-3">
                {{-- Sedang Disurvei Count Card --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#EA580C]"></span>
                            <span class="text-xs font-medium text-slate-500">Sedang Disurvei</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 mt-1" id="stat-sedang-disurvei">0</div>
                    </div>
                </div>

                {{-- Sedang Renovasi Count Card --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#2563EB]"></span>
                            <span class="text-xs font-medium text-slate-500">Sedang Renovasi</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 mt-1" id="stat-sedang-renovasi">0</div>
                    </div>
                </div>

                {{-- Selesai Count Card --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#059669]"></span>
                            <span class="text-xs font-medium text-slate-500">Selesai</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 mt-1" id="stat-selesai">0</div>
                    </div>
                </div>

                {{-- Menunggu Disurvei Count Card (hidden if 0) --}}
                <div id="stat-card-menunggu" class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs hidden">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#64748B]"></span>
                            <span class="text-xs font-medium text-slate-500">Menunggu Disurvei</span>
                        </div>
                        <div class="text-2xl font-bold text-slate-900 mt-1" id="stat-menunggu">0</div>
                    </div>
                </div>
            </div>

            {{-- Area Terbanyak Container --}}
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Area terbanyak</div>
                <div id="heatmap-area-list" class="space-y-2.5 text-xs">
                    {{-- Diisi secara dinamis oleh JavaScript --}}
                    <div class="text-slate-400 italic">Memuat data area...</div>
                </div>
                <div class="border-t border-slate-100 mt-3 pt-2.5 flex items-center justify-between text-xs font-semibold text-slate-700">
                    <span>Total</span>
                    <span id="heatmap-total-rumah">0 rumah</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Legends & Guidance Banner --}}
    <div class="space-y-4">
        {{-- Gradient Legends --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-1 pb-1">
            {{-- Legend Sedang Disurvei --}}
            <div>
                <div class="text-[11px] font-semibold text-slate-600 mb-1.5">Sedang Disurvei</div>
                <div class="h-2 rounded-full bg-gradient-to-r from-[#FDE047] via-[#F97316] to-[#7C2D12]"></div>
                <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-medium">
                    <span>Sedikit</span>
                    <span>Banyak</span>
                </div>
            </div>

            {{-- Legend Sedang Renovasi --}}
            <div>
                <div class="text-[11px] font-semibold text-slate-600 mb-1.5">Sedang Renovasi</div>
                <div class="h-2 rounded-full bg-gradient-to-r from-[#BFDBFE] via-[#3B82F6] to-[#172554]"></div>
                <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-medium">
                    <span>Sedikit</span>
                    <span>Banyak</span>
                </div>
            </div>

            {{-- Legend Selesai --}}
            <div>
                <div class="text-[11px] font-semibold text-slate-600 mb-1.5">Selesai</div>
                <div class="h-2 rounded-full bg-gradient-to-r from-[#A7F3D0] via-[#10B981] to-[#064E3B]"></div>
                <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-medium">
                    <span>Sedikit</span>
                    <span>Banyak</span>
                </div>
            </div>
        </div>

        <div class="text-center text-[11px] text-slate-400 italic">
            Makin pekat = makin banyak rumah pada status tersebut.
        </div>

        {{-- Notice Banner --}}
        <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-xl p-3.5 flex items-start gap-3 text-emerald-800 text-xs">
            <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
            <div>
                @if ($role === 'surveyor')
                    <div class="font-semibold text-emerald-900">Heatmap hanya menampilkan rumah di wilayah tugas Anda. Data pribadi warga tidak ditampilkan.</div>
                    <div class="text-emerald-700 text-[11px] mt-0.5">Rumah yang belum memiliki koordinat tidak muncul di peta.</div>
                @else
                    <div class="font-semibold text-emerald-900">Heatmap hanya menampilkan sebaran area. Data pribadi warga tidak ditampilkan.</div>
                    <div class="text-emerald-700 text-[11px] mt-0.5">Rumah yang belum memiliki koordinat lokasi tidak muncul di peta.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Leaflet Assets & Heatmap Logic --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapElement = document.getElementById('heatmap-map');
    if (!mapElement) return;

    // Default center (Sukamaju / Jawa Barat / Surabaya)
    const defaultCenter = [-6.9175, 107.6191];
    const defaultZoom = 13;

    // Inisialisasi Peta Leaflet dengan OpenStreetMap
    const map = L.map('heatmap-map', {
        center: defaultCenter,
        zoom: defaultZoom,
        attributionControl: true,
        zoomControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
    }).addTo(map);

    // Konfigurasi Gradien Warna per Lapisan
    const layerGradients = {
        sedang_disurvei: {
            0.2: '#FED7AA',
            0.5: '#F97316',
            0.8: '#C2410C',
            1.0: '#7C2D12'
        },
        sedang_renovasi: {
            0.2: '#BFDBFE',
            0.5: '#3B82F6',
            0.8: '#1D4ED8',
            1.0: '#172554'
        },
        selesai: {
            0.2: '#A7F3D0',
            0.5: '#10B981',
            0.8: '#047857',
            1.0: '#064E3B'
        },
        menunggu_disurvei: {
            0.2: '#E2E8F0',
            0.5: '#94A3B8',
            0.8: '#64748B',
            1.0: '#334155'
        }
    };

    // State Lapisan
    const activeLayers = {
        sedang_disurvei: true,
        sedang_renovasi: true,
        selesai: true,
        menunggu_disurvei: true
    };

    const heatLayerInstances = {
        sedang_disurvei: null,
        sedang_renovasi: null,
        selesai: null,
        menunggu_disurvei: null
    };

    let latestHeatmapData = null;

    // Ambil Elemen Filter
    const filterTriwulan = document.getElementById('heatmap-filter-triwulan');
    const filterWilayah = document.getElementById('heatmap-filter-wilayah');
    const filterSumberProgram = document.getElementById('heatmap-filter-sumber-program');
    const emptyStateEl = document.getElementById('heatmap-empty-state');

    // Stat Counter Elements
    const statSedangDisurvei = document.getElementById('stat-sedang-disurvei');
    const statSedangRenovasi = document.getElementById('stat-sedang-renovasi');
    const statSelesai = document.getElementById('stat-selesai');
    const statMenunggu = document.getElementById('stat-menunggu');
    const statCardMenunggu = document.getElementById('stat-card-menunggu');
    const areaListContainer = document.getElementById('heatmap-area-list');
    const totalRumahLabel = document.getElementById('heatmap-total-rumah');

    // Fetch Heatmap Data dari API
    function fetchHeatmapData() {
        const params = new URLSearchParams();
        if (filterTriwulan && filterTriwulan.value) {
            params.append('triwulan', filterTriwulan.value);
        }
        if (filterWilayah && filterWilayah.value) {
            params.append('wilayah_id', filterWilayah.value);
        }
        if (filterSumberProgram && filterSumberProgram.value) {
            params.append('sumber_program', filterSumberProgram.value);
        }

        fetch(`{{ route('heatmap.data') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat data heatmap');
            return res.json();
        })
        .then(data => {
            latestHeatmapData = data;
            renderHeatmapLayers(data);
            updateStatsAndBreakdown(data);
        })
        .catch(err => {
            console.error('Heatmap load error:', err);
        });
    }

    // Render Lapisan Heatmap ke Peta
    function renderHeatmapLayers(data) {
        if (!data || !data.layers) return;

        let allPointsForBounds = [];

        // Update / Buat layer untuk tiap status
        ['menunggu_disurvei', 'sedang_disurvei', 'sedang_renovasi', 'selesai'].forEach(layerKey => {
            // Hapus layer lama jika ada
            if (heatLayerInstances[layerKey]) {
                map.removeLayer(heatLayerInstances[layerKey]);
                heatLayerInstances[layerKey] = null;
            }

            const rawPoints = data.layers[layerKey] || [];
            const heatPoints = rawPoints.map(p => [p.lat, p.lng, p.weight || 0.8]);

            if (heatPoints.length > 0) {
                const heatLayer = L.heatLayer(heatPoints, {
                    radius: 28,
                    blur: 20,
                    maxZoom: 17,
                    max: 1.0,
                    gradient: layerGradients[layerKey]
                });

                heatLayerInstances[layerKey] = heatLayer;

                if (activeLayers[layerKey]) {
                    heatLayer.addTo(map);
                    allPointsForBounds = allPointsForBounds.concat(rawPoints.map(p => [p.lat, p.lng]));
                }
            }
        });

        // Penyesuaian viewport peta
        if (allPointsForBounds.length > 0) {
            emptyStateEl.classList.add('hidden');
            const bounds = L.latLngBounds(allPointsForBounds);
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        } else {
            emptyStateEl.classList.remove('hidden');
        }
    }

    // Update Counter dan Area Terbanyak
    function updateStatsAndBreakdown(data) {
        if (!data) return;

        const counts = data.counts || {};
        if (statSedangDisurvei) statSedangDisurvei.textContent = counts.sedang_disurvei || 0;
        if (statSedangRenovasi) statSedangRenovasi.textContent = counts.sedang_renovasi || 0;
        if (statSelesai) statSelesai.textContent = counts.selesai || 0;

        if (statMenunggu) {
            statMenunggu.textContent = counts.menunggu_disurvei || 0;
            if (statCardMenunggu) {
                if (counts.menunggu_disurvei > 0) {
                    statCardMenunggu.classList.remove('hidden');
                } else {
                    statCardMenunggu.classList.add('hidden');
                }
            }
        }

        const total = counts.total || 0;
        if (totalRumahLabel) totalRumahLabel.textContent = `${total} rumah`;

        // Render Area Terbanyak
        if (areaListContainer) {
            const distribution = data.area_distribution?.items || [];
            if (distribution.length === 0) {
                areaListContainer.innerHTML = `
                    <div class="text-slate-400 italic py-2">Tidak ada data untuk filter ini.</div>
                `;
            } else {
                let html = '';
                distribution.forEach(item => {
                    html += `
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-medium text-slate-700">${item.nama}</span>
                                <span class="font-bold text-slate-900">${item.count}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#1D4034] h-1.5 rounded-full transition-all duration-300" style="width: ${item.percentage}%"></div>
                            </div>
                        </div>
                    `;
                });
                areaListContainer.innerHTML = html;
            }
        }
    }

    // Event Listener untuk Toggle Lapisan
    document.querySelectorAll('.heatmap-layer-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const layerKey = this.getAttribute('data-layer');
            activeLayers[layerKey] = !activeLayers[layerKey];

            if (activeLayers[layerKey]) {
                this.classList.remove('opacity-40', 'bg-slate-100', 'border-slate-300', 'text-slate-400');
                if (heatLayerInstances[layerKey]) {
                    heatLayerInstances[layerKey].addTo(map);
                }
            } else {
                this.classList.add('opacity-40', 'bg-slate-100', 'border-slate-300', 'text-slate-400');
                if (heatLayerInstances[layerKey]) {
                    map.removeLayer(heatLayerInstances[layerKey]);
                }
            }

            // Hitung ulang bounds titik aktif
            let activePoints = [];
            if (latestHeatmapData && latestHeatmapData.layers) {
                Object.keys(activeLayers).forEach(k => {
                    if (activeLayers[k] && latestHeatmapData.layers[k]) {
                        activePoints = activePoints.concat(latestHeatmapData.layers[k].map(p => [p.lat, p.lng]));
                    }
                });
            }

            if (activePoints.length > 0) {
                emptyStateEl.classList.add('hidden');
            } else {
                emptyStateEl.classList.remove('hidden');
            }
        });
    });

    // Event Listener Filter Changes
    if (filterTriwulan) filterTriwulan.addEventListener('change', fetchHeatmapData);
    if (filterWilayah) filterWilayah.addEventListener('change', fetchHeatmapData);
    if (filterSumberProgram) filterSumberProgram.addEventListener('change', fetchHeatmapData);

    // Initial load
    fetchHeatmapData();

    // Re-render icons if lucide available
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
