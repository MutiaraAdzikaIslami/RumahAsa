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
     * FR-08: List wilayah dengan pagination & filter (optimized for 90k+ rows).
     */
    public function index(Request $request)
    {
        $query = Wilayah::query()->with('parent:id,kode,nama,tingkat');

        // Filter by tingkat (provinsi, kabupaten, kecamatan, dll)
        if ($tingkat = $request->query('tingkat')) {
            $query->where('tingkat', $tingkat);
        }

        // Search by nama or kode
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $wilayah = $query->orderBy('tingkat')->orderBy('nama')->paginate(25)->withQueryString();
        $tingkatList = ['provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'rw', 'rt'];

        return view('wilayah.index', compact('wilayah', 'tingkatList'));
    }

    /**
     * Get tree (hanya untuk root/provinsi supaya ringan)
     */
    public function tree(): JsonResponse
    {
        // Hanya load provinsi untuk performance
        $tree = Wilayah::where('tingkat', 'provinsi')->orderBy('nama')->get();
        return response()->json($tree);
    }

    public function show(Wilayah $wilayah): JsonResponse
    {
        return response()->json(
            $wilayah->load(['parent:id,kode,nama,tingkat', 'children:id,kode,nama,tingkat,parent_id'])
        );
    }

    public function store(StoreWilayahRequest $request): JsonResponse
    {
        try {
            $wilayah = $this->service->create($request->validated());
            return response()->json($wilayah, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(UpdateWilayahRequest $request, Wilayah $wilayah): JsonResponse
    {
        try {
            $updated = $this->service->update($wilayah, $request->validated());
            return response()->json($updated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Wilayah $wilayah): JsonResponse
    {
        try {
            $this->service->delete($wilayah);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function descendants(Wilayah $wilayah): JsonResponse
    {
        return response()->json($this->service->getDescendants($wilayah));
    }
}