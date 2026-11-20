<?php

namespace App\Modules\Rumah\Http\Controllers;

use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\NikEncryptor;
use App\Modules\Rumah\Http\Requests\StoreRumahRequest;
use App\Modules\Rumah\Services\RumahService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class RumahController extends Controller
{
    public function __construct(
        private readonly RumahService $rumahService,
    ) {
    }

    public function index(Request $request)
    {
        $query = RumahWarga::with(['wilayah.parent.parent', 'surveyor', 'survei']);

        $user = auth()->user();
        if ($user->isSurveyor() && $user->wilayah) {
            $wilayahPath = $user->wilayah->path;
            $query->whereHas('wilayah', function ($q) use ($wilayahPath) {
                $q->where('path', $wilayahPath)
                  ->orWhere('path', 'like', $wilayahPath . '/%');
            });
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                  ->orWhereHas('wilayah', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($wilayahId = $request->query('wilayah_id')) {
            $wilayah = Wilayah::find($wilayahId);
            if ($wilayah) {
                $query->whereHas('wilayah', function ($q) use ($wilayah) {
                    $q->where('id', $wilayah->id)
                      ->orWhere('path', 'like', $wilayah->path . '/%');
                });
            }
        }

        $rumah = $query->latest()->paginate(15);
        $wilayahList = Wilayah::orderBy('tingkat')->orderBy('nama')->get();

        return view('rumah.index', compact('rumah', 'wilayahList'));
    }

    public function create()
    {
        Gate::authorize('create', RumahWarga::class);

        $kabupatenList = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $kecamatanList = Wilayah::where('tingkat', Wilayah::TINGKAT_KECAMATAN)
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'parent_id']);

        $surveyorList = User::with('wilayah.parent.parent')
            ->whereHas('role', function ($q) {
                $q->where('kode', 'surveyor');
            })
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get();

        return view('rumah.create', compact('kabupatenList', 'kecamatanList', 'surveyorList'));
    }

    public function store(StoreRumahRequest $request)
    {
        Gate::authorize('create', RumahWarga::class);

        try {
            $isDraft = $request->isDraftSubmission();
            $rumah   = $this->rumahService->createRumah($request->validated(), $isDraft);

            return redirect()->route('rumah.show', $rumah)
                ->with('success', $isDraft ? 'Draf data rumah berhasil disimpan.' : 'Data rumah berhasil disimpan.');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['surveyor_id' => $e->getMessage()])
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan data rumah: ' . $e->getMessage());
        }
    }

    public function show(RumahWarga $rumah)
    {
        $rumah->load([
            'wilayah.parent.parent.parent.parent',
            'surveyor',
            'survei.komponenSurvei.komponen',
            'survei.komponenSurvei.opsiKondisi',
            'survei.komponenSurvei.foto',
            'riwayatStatus',
        ]);
        return view('rumah.show', compact('rumah'));
    }

    public function edit(RumahWarga $rumah)
    {
        Gate::authorize('update', $rumah);

        $kabupatenList = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $kecamatanList = Wilayah::where('tingkat', Wilayah::TINGKAT_KECAMATAN)
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'parent_id']);

        $surveyorList = User::with('wilayah.parent.parent')
            ->whereHas('role', function ($q) {
                $q->where('kode', 'surveyor');
            })
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get();

        $rumah->load('wilayah.parent.parent.parent.parent');

        $selectedKabupatenId = null;
        $selectedKecamatanId = null;
        $selectedKelurahanId = null;
        $rtText = '';
        $rwText = '';

        $w = $rumah->wilayah;
        if ($w) {
            if ($w->tingkat === Wilayah::TINGKAT_RT) {
                $rtText    = trim((string) preg_replace('/^RT\s*/i', '', $w->nama));
                $rw        = $w->parent;
                $rwText    = $rw ? trim((string) preg_replace('/^RW\s*/i', '', $rw->nama)) : '';
                $kelurahan = $rw?->parent;
                $kecamatan = $kelurahan?->parent;
                $kabupaten = $kecamatan?->parent;
            } elseif ($w->tingkat === Wilayah::TINGKAT_RW) {
                $rwText    = trim((string) preg_replace('/^RW\s*/i', '', $w->nama));
                $kelurahan = $w->parent;
                $kecamatan = $kelurahan?->parent;
                $kabupaten = $kecamatan?->parent;
            } elseif ($w->tingkat === Wilayah::TINGKAT_KELURAHAN) {
                $kelurahan = $w;
                $kecamatan = $kelurahan->parent;
                $kabupaten = $kecamatan?->parent;
            } elseif ($w->tingkat === Wilayah::TINGKAT_KECAMATAN) {
                $kelurahan = null;
                $kecamatan = $w;
                $kabupaten = $kecamatan->parent;
            } else {
                $kelurahan = null;
                $kecamatan = null;
                $kabupaten = $w;
            }

            $selectedKelurahanId = $kelurahan?->id;
            $selectedKecamatanId = $kecamatan?->id;
            $selectedKabupatenId = $kabupaten?->id;
        }

        $activeKecamatanId = old('kecamatan_id', $selectedKecamatanId);
        $initialKelurahanList = $activeKecamatanId
            ? Wilayah::where('parent_id', $activeKecamatanId)
                ->where('tingkat', Wilayah::TINGKAT_KELURAHAN)
                ->where('is_aktif', true)
                ->orderBy('nama')
                ->get(['id', 'nama'])
            : collect();

        $nikEncryptor = app(NikEncryptor::class);
        $nikPlain  = $rumah->nik_enc ? $nikEncryptor->decryptNik($rumah->nik_enc) : '';
        $noKkPlain = $rumah->no_kk_enc ? $nikEncryptor->decryptNoKk($rumah->no_kk_enc) : '';

        return view('rumah.edit', compact(
            'rumah',
            'kabupatenList',
            'kecamatanList',
            'initialKelurahanList',
            'selectedKabupatenId',
            'selectedKecamatanId',
            'selectedKelurahanId',
            'rtText',
            'rwText',
            'surveyorList',
            'nikPlain',
            'noKkPlain',
        ));
    }

    public function update(StoreRumahRequest $request, RumahWarga $rumah)
    {
        Gate::authorize('update', $rumah);

        try {
            $isDraft = $request->isDraftSubmission();
            $rumah   = $this->rumahService->updateRumah($rumah, $request->validated(), $isDraft);

            return redirect()->route('rumah.show', $rumah)
                ->with('success', $isDraft ? 'Draf perubahan berhasil disimpan.' : 'Data rumah berhasil diperbarui.');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['surveyor_id' => $e->getMessage()])
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem saat memperbarui data rumah: ' . $e->getMessage());
        }
    }
}