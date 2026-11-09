<?php

namespace App\Modules\Wilayah\Services;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Collection;

/**
 * FR-08: Service untuk manage wilayah berjenjang.
 *
 * Strategi:
 *  - Adjacency list (parent_id) untuk relasi induk-anak
 *  - Materialized path (/1/5/12/45) untuk query descendant cepat via LIKE
 *  - Path di-generate saat create & di-update recursive saat parent berubah
 */
class WilayahService
{
    /**
     * Bikin wilayah baru + auto-generate path.
     */
    public function create(array $data): Wilayah
    {
        $parentId = $data['parent_id'] ?? null;

        // Validasi parent sesuai jenjang
        if (!Wilayah::isValidParent($parentId ? (int) $parentId : null, $data['tingkat'])) {
            throw new \InvalidArgumentException(
                "Parent tidak valid untuk tingkat '{$data['tingkat']}'. " .
                "Parent harus 1 jenjang di atas (Kecamatan -> Kelurahan -> RW -> RT)."
            );
        }

        // Create dulu tanpa path (butuh ID untuk path)
        $wilayah = Wilayah::create([
            'kode'      => $data['kode'],
            'nama'      => $data['nama'],
            'tingkat'   => $data['tingkat'],
            'parent_id' => $parentId,
            'is_aktif'  => $data['is_aktif'] ?? true,
            'path'      => null,
        ]);

        // Generate path berdasarkan parent
        $path = $parentId
            ? Wilayah::find($parentId)->path . '/' . $wilayah->id
            : '/' . $wilayah->id;
        $wilayah->update(['path' => $path]);

        return $wilayah->fresh();
    }

    /**
     * Update wilayah + re-generate path bila parent berubah.
     */
    public function update(Wilayah $wilayah, array $data): Wilayah
    {
        $oldPath = $wilayah->path;
        $newParentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $wilayah->parent_id;

        // Cek parent tidak berubah jenjang
        if ($newParentId !== $wilayah->parent_id) {
            if (!Wilayah::isValidParent($newParentId ? (int) $newParentId : null, $wilayah->tingkat)) {
                throw new \InvalidArgumentException(
                    "Parent baru tidak valid untuk tingkat '{$wilayah->tingkat}'."
                );
            }
        }

        $wilayah->update([
            'kode'      => $data['kode'] ?? $wilayah->kode,
            'nama'      => $data['nama'] ?? $wilayah->nama,
            'parent_id' => $newParentId,
            'is_aktif'  => $data['is_aktif'] ?? $wilayah->is_aktif,
        ]);

        // Bila parent berubah -> update path wilayah ini + semua descendant-nya
        if ($newParentId !== $wilayah->parent_id || $wilayah->wasChanged('parent_id')) {
            $this->regeneratePath($wilayah, $oldPath);
        }

        return $wilayah->fresh();
    }

    /**
     * Soft delete wilayah + cek apakah masih dipakai oleh user/rumah.
     * Kalau dipakai -> throw exception.
     */
    public function delete(Wilayah $wilayah): void
    {
        // Cek user yang terkait
        if ($wilayah->users()->exists()) {
            throw new \RuntimeException(
                "Wilayah tidak bisa dihapus karena masih ada user terkait. " .
                "Nonaktifkan atau pindahkan user terlebih dahulu."
            );
        }

        // Cek rumah yang terkait
        if ($wilayah->rumahWarga()->exists()) {
            throw new \RuntimeException(
                "Wilayah tidak bisa dihapus karena masih ada rumah terdata di wilayah ini."
            );
        }

        // Cek descendant yang masih aktif
        if ($wilayah->children()->exists()) {
            throw new \RuntimeException(
                "Wilayah tidak bisa dihapus karena masih memiliki wilayah anak. " .
                "Hapus wilayah anak terlebih dahulu."
            );
        }

        $wilayah->delete();
    }

    /**
     * Re-generate path untuk wilayah + semua descendant (recursive).
     * Dipakai saat parent_id berubah.
     */
    private function regeneratePath(Wilayah $wilayah, string $oldPath): void
    {
        $newPath = $wilayah->parent_id
            ? $wilayah->parent->path . '/' . $wilayah->id
            : '/' . $wilayah->id;

        $wilayah->update(['path' => $newPath]);

        // Update semua descendant: replace old path prefix dengan new path
        $descendants = Wilayah::where('path', 'like', $oldPath . '/%')->get();
        foreach ($descendants as $descendant) {
            $newDescendantPath = $newPath . substr($descendant->path, strlen($oldPath));
            $descendant->update(['path' => $newDescendantPath]);
        }
    }

    /**
     * Ambil tree wilayah berjenjang (untuk UI tree view).
     */
    public function getTree(): Collection
    {
        return Wilayah::whereNull('parent_id')
            ->with('children.children.children')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();
    }

    /**
     * Ambil semua wilayah dengan tingkat tertentu (untuk dropdown filter).
     */
    public function getByTingkat(string $tingkat): Collection
    {
        return Wilayah::where('tingkat', $tingkat)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'parent_id', 'path']);
    }

    /**
     * Ambil semua descendant dari wilayah tertentu (untuk filter berjenjang).
     */
    public function getDescendants(Wilayah $parent): Collection
    {
        return Wilayah::where('path', 'like', $parent->path . '/%')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();
    }
}