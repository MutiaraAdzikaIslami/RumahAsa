<?php

namespace App\Modules\Rumah\Http\Controllers;

use App\Models\RumahWarga;
use App\Modules\Rumah\Http\Requests\StoreRumahRequest;
use App\Modules\Rumah\Services\RumahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class RumahController extends Controller
{
    public function __construct(
        private readonly RumahService $rumahService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = RumahWarga::with(['wilayah', 'surveyor', 'survei']);

        if ($search = $request->input('q')) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(nama_kepala_keluarga) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(kode_registrasi) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(alamat) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $rumah = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $statusList = RumahWarga::STATUS_LIST;

        return view('rumah.index', compact('rumah', 'statusList'));
    }

    public function create(): View
    {
        return view('rumah.create');
    }

    public function store(StoreRumahRequest $request): RedirectResponse
    {
        $rumah = $this->rumahService->createRumah($request->validated());

        return redirect()
            ->route('rumah.show', $rumah)
            ->with('success', "Data rumah {$rumah->kode_registrasi} berhasil disimpan. Status: Menunggu Survei.");
    }

    public function show(RumahWarga $rumah): View
    {
        $rumah->load(['wilayah.parent.parent.parent.parent', 'surveyor', 'survei', 'riwayatStatus.user']);
        return view('rumah.show', compact('rumah'));
    }

    public function edit(RumahWarga $rumah): RedirectResponse|View
    {
        if (!$rumah->bisaDieditOlehAdmin()) {
            return redirect()
                ->route('rumah.show', $rumah)
                ->with('error', "Data rumah tidak bisa diedit karena sudah dalam proses survei (status: {$rumah->status}).");
        }

        return view('rumah.create', ['rumah' => $rumah]);
    }

    public function update(StoreRumahRequest $request, RumahWarga $rumah): RedirectResponse
    {
        $this->rumahService->updateRumah($rumah, $request->validated());

        return redirect()
            ->route('rumah.show', $rumah)
            ->with('success', "Data rumah {$rumah->kode_registrasi} berhasil diperbarui.");
    }

    public function destroy(RumahWarga $rumah): RedirectResponse
    {
        try {
            $this->rumahService->deleteRumah($rumah);
            return redirect()->route('rumah.index')
                ->with('success', "Rumah {$rumah->kode_registrasi} telah dihapus.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
