<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel secara eksplisit
     * karena nama tabel di database adalah 'wilayah' (bukan 'wilayahs')
     */
    protected $table = 'wilayah';

    /**
     * Kolom yang diizinkan untuk diisi secara massal.
     */
    protected $fillable = [
        'parent_id',
        'tingkat',
        'nama',
        'path',
    ];

    /**
     * Relasi One-to-Many ke tabel Users.
     * Satu wilayah bisa memiliki banyak pengguna/petugas (Admin, Surveyor, dll).
     */
    public function users()
    {
        return $this->hasMany(User::class, 'wilayah_id');
    }

    /**
     * Relasi Self-Referencing (Inverse): Mendapatkan wilayah induk.
     * Contoh: Jika ini adalah RT, maka parent-nya adalah RW.
     */
    public function parent()
    {
        return $this->belongsTo(Wilayah::class, 'parent_id');
    }

    /**
     * Relasi Self-Referencing: Mendapatkan daftar wilayah anak.
     * Contoh: Jika ini adalah Kelurahan, maka children-nya adalah daftar RW.
     */
    public function children()
    {
        return $this->hasMany(Wilayah::class, 'parent_id');
    }
}