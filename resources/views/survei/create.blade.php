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