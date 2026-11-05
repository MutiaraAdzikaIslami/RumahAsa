@extends('layouts.app')
@section('breadcrumb', 'Data User')

@section('content')
<div x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editForm: { id: '', nama: '', email: '', role_id: '', wilayah_id: '', is_aktif: 1 },
    openEdit(user) {
        this.editForm.id = user.id;
        this.editForm.nama = user.nama;
        this.editForm.email = user.email;
        this.editForm.role_id = user.role_id;
        this.editForm.wilayah_id = user.wilayah_id;
        this.editForm.is_aktif = user.is_aktif ? 1 : 0;
        this.showEditModal = true;
    }
}" class="p-6 md:p-8 bg-gray-50 min-h-screen font-sans">
    
    <!-- Header Halaman -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <p class="text-xs font-semibold text-orange-600 tracking-wider uppercase mb-1">Administrasi Akses</p>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen User</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun, role, dan wilayah tugas pengguna RumahAsa.</p>
        </div>
        <button @click="showAddModal = true" class="bg-teal-800 hover:bg-teal-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah User
        </button>
    </div>

    <!-- Statistik Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap justify-between items-center">
        <div class="flex gap-8">
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $totalUser }}</span>
                <span class="text-xs text-gray-500 font-medium">Total User</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $aktifUser }}</span>
                <span class="text-xs text-gray-500 font-medium">Akun Aktif</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $nonaktifUser }}</span>
                <span class="text-xs text-gray-500 font-medium">Akun Nonaktif</span>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200 mt-4 md:mt-0">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Hak akses mengikuti role dan wilayah tugas pengguna.
        </div>
    </div>

    <!-- Pesan Sukses / Error -->
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <ul class="text-sm list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filter & Pencarian -->
    <div class="flex gap-4 mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex-1 flex gap-4">
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <select name="role_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 bg-white min-w-[160px] focus:ring-teal-500">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->nama }}</option>
                @endforeach
            </select>
            <select name="wilayah_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 bg-white min-w-[160px] focus:ring-teal-500">
                <option value="">Semua Wilayah</option>
                @foreach($wilayahs as $wilayah)
                    <option value="{{ $wilayah->id }}" {{ request('wilayah_id') == $wilayah->id ? 'selected' : '' }}>{{ $wilayah->nama }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Tabel Data User -->
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-end">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Daftar User</h2>
                <p class="text-xs text-gray-500">{{ $totalUser }} pengguna terdaftar pada sistem</p>
            </div>
            <span class="text-xs text-gray-500">Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-xs font-semibold text-gray-500 tracking-wider">
                        <th class="p-4 border-b border-gray-100">NAMA</th>
                        <th class="p-4 border-b border-gray-100">EMAIL</th>
                        <th class="p-4 border-b border-gray-100">ROLE</th>
                        <th class="p-4 border-b border-gray-100">WILAYAH TUGAS</th>
                        <th class="p-4 border-b border-gray-100">STATUS</th>
                        <th class="p-4 border-b border-gray-100">AKSI</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($user->nama, 0, 2)) }}
                            </div>
                            <span class="font-semibold text-gray-900">{{ $user->nama }}</span>
                        </td>
                        <td class="p-4 text-gray-500">{{ $user->email }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-gray-100 text-gray-600 border border-gray-200">
                                {{ $user->role->nama ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-600 font-medium">{{ $user->wilayah->nama ?? 'N/A' }}</td>
                        <td class="p-4">
                            @if($user->is_aktif)
                                <span class="px-2.5 py-1 text-[11px] font-medium rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 text-[11px] font-medium rounded-full bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <!-- Tombol Edit -->
                                <button @click="openEdit({{ json_encode($user) }})" class="p-1.5 text-gray-400 hover:text-teal-700 hover:bg-teal-50 rounded border border-transparent hover:border-teal-100 transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                
                                <!-- Form Hapus User -->
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded border border-transparent hover:border-red-100 transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">Tidak ada data user yang ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- MODAL TAMBAH USER -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="showAddModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showAddModal = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAddModal"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full relative z-10">
                
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-8">
                        <div class="flex justify-between items-start mb-5">
                            <div>
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Tambah User</h3>
                                <p class="text-xs text-gray-500 mt-1">Isi data baru untuk menambahkan user ke sistem.</p>
                            </div>
                            <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-500 bg-gray-50 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-y-5 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nama lengkap</label>
                                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" placeholder="Masukkan alamat email" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                                <select name="role_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="" disabled selected>Pilih role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Wilayah</label>
                                <select name="wilayah_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="" disabled selected>Pilih wilayah</option>
                                    @foreach($wilayahs as $wilayah)
                                        <option value="{{ $wilayah->id }}">{{ $wilayah->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status akun</label>
                                <select name="is_aktif" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Password default</label>
                                <input type="text" name="password" placeholder="Masukkan password default" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="mt-5 bg-gray-50 border border-gray-100 rounded-lg p-3 flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs text-gray-500">Password default akan dikirim ke email user setelah akun dibuat.</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-gray-100 gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2 bg-teal-800 text-sm font-medium text-white hover:bg-teal-900 sm:w-auto sm:text-sm transition-colors">
                            Simpan
                        </button>
                        <button type="button" @click="showAddModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT USER -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="showEditModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showEditModal = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showEditModal"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full relative z-10">
                
                <form :action="'{{ url('admin/users') }}/' + editForm.id" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-8">
                        <div class="flex justify-between items-start mb-5">
                            <div>
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Edit User</h3>
                                <p class="text-xs text-gray-500 mt-1">Perbarui informasi data akun pengguna.</p>
                            </div>
                            <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500 bg-gray-50 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-y-5 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nama lengkap</label>
                                <input type="text" name="nama" x-model="editForm.nama" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" x-model="editForm.email" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                                <select name="role_id" x-model="editForm.role_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="" disabled>Pilih role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Wilayah</label>
                                <select name="wilayah_id" x-model="editForm.wilayah_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="" disabled>Pilih wilayah</option>
                                    @foreach($wilayahs as $wilayah)
                                        <option value="{{ $wilayah->id }}">{{ $wilayah->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status akun</label>
                                <select name="is_aktif" x-model="editForm.is_aktif" required class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                                <input type="text" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-gray-100 gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2 bg-teal-800 text-sm font-medium text-white hover:bg-teal-900 sm:w-auto sm:text-sm transition-colors">
                            Perbarui
                        </button>
                        <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection