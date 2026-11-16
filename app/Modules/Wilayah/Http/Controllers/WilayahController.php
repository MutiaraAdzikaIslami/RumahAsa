<?php

namespace App\Modules\Wilayah\Http\Controllers;

use App\Models\Wilayah;
use App\Modules\Wilayah\Http\Requests\StoreWilayahRequest;
use App\Modules\Wilayah\Http\Requests\UpdateWilayahRequest;
use App\Modules\Wilayah\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class WilayahController extends Controller
{
    public function __construct(
        private readonly WilayahService $wilayahService,
    ) {}

    public function index(Request $request): View
    {
        $query = Wilayah::query()->with('parent');

        if ($tingkat = $request->input('tingkat')) {
            if (array_key_exists($tingkat, Wilayah::URUTAN_TINGKAT)) {
                $query->where('tingkat', $tingkat);
            }
        } else {
            $query->dropdownTingkat();
        }

        if ($request->input('status') === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($request->input('status') === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        if ($search = $request->input('q')) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        $wilayah = $query->orderBy('path')->paginate(25)->withQueryString();

        $tingkatOptions = [];
        foreach (Wilayah::URUTAN_TINGKAT as $t => $urutan) {
            $tingkatOptions[$t] = Wilayah::LABEL_TINGKAT[$t] ?? ucfirst($t);
        }

        return view('wilayah.index', [
            'wilayah'         => $wilayah,
            'tingkatOptions'  => $tingkatOptions,
            'selectedTingkat' => $request->input('tingkat', ''),
        ]);
    }

    public function store(StoreWilayahRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        if (! $request->wantsJson()) {
            $data['is_aktif'] = $request->boolean('is_aktif', true);
        }

        $wilayah = $this->wilayahService->create($data);

        if ($request->wantsJson()) {
            return response()->json($wilayah, 201);
        }

        return redirect()->route('wilayah.index')
            ->with('success', "Wilayah {$wilayah->nama} berhasil ditambahkan.");
    }

    public function show(Wilayah $wilayah): JsonResponse
    {
        return response()->json($wilayah->load('parent'));
    }

    public function update(UpdateWilayahRequest $request, Wilayah $wilayah): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        if (! $request->wantsJson()) {
            $data['is_aktif'] = $request->boolean('is_aktif');
        }

        $updated = $this->wilayahService->update($wilayah, $data);

        if ($request->wantsJson()) {
            return response()->json($updated, 200);
        }

        return redirect()->route('wilayah.index')
            ->with('success', "Wilayah {$updated->nama} berhasil diperbarui.");
    }

    public function destroy(Request $request, Wilayah $wilayah): JsonResponse|RedirectResponse|\Illuminate\Http\Response
    {
        try {
            $nama = $wilayah->nama;
            $this->wilayahService->delete($wilayah);

            if ($request->wantsJson()) {
                return response()->noContent();
            }

            return redirect()->route('wilayah.index')
                ->with('success', "Wilayah {$nama} berhasil dihapus.");
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->route('wilayah.index')
                ->with('error', $e->getMessage());
        }
    }

    public function tree(): JsonResponse
    {
        $provinsi = Wilayah::where('tingkat', Wilayah::TINGKAT_PROVINSI)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'tingkat', 'path', 'is_aktif']);

        return response()->json($provinsi);
    }

    public function descendants(Wilayah $wilayah): JsonResponse
    {
        return response()->json($this->wilayahService->getDescendants($wilayah));
    }

    /**
     * API internal untuk dropdown Parent Wilayah (1 jenjang di atas tingkat terpilih).
     */
    public function parents(Request $request): JsonResponse
    {
        $tingkat = (string) $request->query('tingkat', '');
        $urutan = Wilayah::URUTAN_TINGKAT[$tingkat] ?? null;

        if ($urutan === null || $urutan <= 1) {
            return response()->json(['data' => []]);
        }

        $parentTingkat = array_search($urutan - 1, Wilayah::URUTAN_TINGKAT, true);
        if (! $parentTingkat) {
            return response()->json(['data' => []]);
        }

        $parents = Wilayah::where('tingkat', $parentTingkat)
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->limit(500)
            ->get(['id', 'kode', 'nama']);

        return response()->json(['data' => $parents]);
    }

    /**
     * API: Ambil list Kabupaten/Kota.
     */
    public function getKabupaten(): JsonResponse
    {
        $data = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * API: Ambil list Kecamatan by kabupaten_id.
     */
    public function getKecamatan(Request $request, int $kabupatenId): JsonResponse
    {
        $data = Wilayah::where('tingkat', Wilayah::TINGKAT_KECAMATAN)
            ->where('parent_id', $kabupatenId)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * API: Ambil list Kelurahan/Desa by kecamatan_id.
     */
    public function getKelurahan(Request $request, int $kecamatanId): JsonResponse
    {
        $data = Wilayah::where('tingkat', Wilayah::TINGKAT_KELURAHAN)
            ->where('parent_id', $kecamatanId)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}