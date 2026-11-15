<?php

namespace App\Modules\Wilayah\Http\Controllers;

use App\Models\Wilayah;
use App\Modules\Wilayah\Http\Requests\StoreWilayahRequest;
use App\Modules\Wilayah\Http\Requests\UpdateWilayahRequest;
use App\Modules\Wilayah\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WilayahController extends Controller
{
    public function __construct(private readonly WilayahService $service)
    {
    }

    /**
     * List semua wilayah (flat, paginated).
     * GET /wilayah?tingkat=rt&parent_id=5&search=...
     */
    public function index(Request $request): JsonResponse
    {
        $query = Wilayah::query()->with('parent:id,kode,nama,tingkat');

        if ($tingkat = $request->query('tingkat')) {
            $query->where('tingkat', $tingkat);
        }
        if ($parentId = $request->query('parent_id')) {
            $query->where('parent_id', $parentId);
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('tingkat')->orderBy('nama')->paginate(20)
        );
    }

    /**
     * GET /wilayah/tree — kembalikan tree hierarki (ROOT = kecamatan).
     */
    public function tree(): JsonResponse
    {
        return response()->json($this->service->getTree());
    }

    /**
     * Ambil 1 wilayah + parent + children langsung.
     */
    public function show(Wilayah $wilayah): JsonResponse
    {
        return response()->json(
            $wilayah->load(['parent:id,kode,nama,tingkat', 'children:id,kode,nama,tingkat,parent_id'])
        );
    }

    /**
     * Simpan wilayah baru.
     */
    public function store(StoreWilayahRequest $request): JsonResponse
    {
        try {
            $wilayah = $this->service->create($request->validated());
            return response()->json($wilayah, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update wilayah.
     */
    public function update(UpdateWilayahRequest $request, Wilayah $wilayah): JsonResponse
    {
        try {
            $updated = $this->service->update($wilayah, $request->validated());
            return response()->json($updated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Soft delete wilayah.
     */
    public function destroy(Wilayah $wilayah): JsonResponse
    {
        try {
            $this->service->delete($wilayah);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Ambil semua descendant dari wilayah tertentu (untuk filter berjenjang).
     */
    public function descendants(Wilayah $wilayah): JsonResponse
    {
        return response()->json($this->service->getDescendants($wilayah));
    }
}