<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user dengan fitur pencarian & filter.
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'wilayah']);

        // Fitur Pencarian berdasarkan Nama atau Email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan Role
        if ($request->has('role_id') && $request->role_id != '') {
            $query->where('role_id', $request->role_id);
        }

        // Filter berdasarkan Wilayah
        if ($request->has('wilayah_id') && $request->wilayah_id != '') {
            $query->where('wilayah_id', $request->wilayah_id);
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();
        $wilayahs = Wilayah::all();

        // Hitung statistik untuk ringkasan di atas tabel (disesuaikan dengan View: aktifUser & nonaktifUser)
        $totalUser = User::count();
        $aktifUser = User::where('is_aktif', true)->count();
        $nonaktifUser = User::where('is_aktif', false)->count();

        return view('admin.users.index', compact('users', 'roles', 'wilayahs', 'totalUser', 'aktifUser', 'nonaktifUser'));
    }

    /**
     * Menyimpan user baru ke database (Sesuai modal Tambah User).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'is_aktif' => 'required|boolean',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'wilayah_id' => $request->wilayah_id,
            'is_aktif' => $request->is_aktif,
            'password' => Hash::make($request->password), // Enkripsi password default
            'must_change_password' => true, // Wajib ganti password saat login pertama
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data user yang sudah ada (Sesuai modal Edit User).
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'is_aktif' => 'required|boolean',
            'password' => 'nullable|string|min:6', // Password opsional saat diedit
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'wilayah_id' => $request->wilayah_id,
            'is_aktif' => $request->is_aktif,
        ];

        // Jika admin memasukkan password baru, perbarui dan aktifkan kembali kewajiban ganti password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['must_change_password'] = true;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus user dari sistem.
     */
    public function destroy(User $user)
    {
        // Mencegah admin menghapus akunnya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }
}