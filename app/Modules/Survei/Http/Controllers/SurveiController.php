<?php

namespace App\Modules\Survei\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KomponenPenilaian;
use App\Models\OpsiKondisi;
use App\Models\RumahWarga;
use App\Models\SurveiFoto;
use App\Models\SurveiKelayakan;
use App\Models\SurveiKomponen;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Core\Services\FotoStorageService;
use App\Modules\Survei\Http\Requests\StoreSurveiRequest;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveiController extends Controller
{
    public function __construct(
        private readonly StatusWorkflow $workflow,
        private readonly AuditLogger $auditLogger,
        private readonly FotoStorageService $fotoStorage,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = RumahWarga::with(['wilayah.parent.parent', 'surveyor'])
            ->where('surveyor_id', $user->id);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', [
                RumahWarga::STATUS_MENUNGGU_SURVEI,
                RumahWarga::STATUS_PERLU_PERBAIKAN,
            ]);
        }

        if ($search = $request->input('q')) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(nama_kepala_keluarga) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(kode_registrasi) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(alamat) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        $rumah = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $statusList = [
            RumahWarga::STATUS_MENUNGGU_SURVEI => 'Menunggu Survei',
            RumahWarga::STATUS_PERLU_PERBAIKAN => 'Perlu Perbaikan',
            RumahWarga::STATUS_DIAJUKAN => 'Diajukan',
        ];

        return view('survei.index', compact('rumah', 'statusList'));
    }

    public function create(RumahWarga $rumah): View|RedirectResponse
    {
        $user = Auth::user();

        if ($rumah->surveyor_id !== $user->id) {
            return redirect()->route('survei.index')
                ->with('error', 'Anda tidak ditugaskan untuk rumah ini.');
        }

        if (!$rumah->bisaDieditSurvei()) {
            return redirect()->route('survei.index')
                ->with('error', "Rumah ini tidak bisa disurvei (status: {$rumah->status}).");
        }

        $komponenList = KomponenPenilaian::with('opsiKondisi')
            ->orderBy('urutan')
            ->get();

        $opsiList = OpsiKondisi::orderBy('urutan', 'asc')->get();

        return view('survei.create', compact('rumah', 'komponenList', 'opsiList'));
    }

    public function store(StoreSurveiRequest $request, RumahWarga $rumah): RedirectResponse
    {
        $user = Auth::user();

        if ($rumah->surveyor_id !== $user->id) {
            return redirect()->route('survei.index')
                ->with('error', 'Anda tidak ditugaskan untuk rumah ini.');
        }

        if (!$rumah->bisaDieditSurvei()) {
            return redirect()->route('survei.index')
                ->with('error', "Rumah ini tidak bisa disurvei (status: {$rumah->status}).");
        }

        $validated = $request->validated();

        DB::transaction(function () use ($rumah, $validated, $user) {
            // 1. Upsert header survei
            $survei = SurveiKelayakan::updateOrCreate(
                ['rumah_id' => $rumah->id],
                [
                    'surveyor_id'        => $user->id,
                    'tanggal_survei'     => $validated['tanggal_survei'],
                    'dikirim_at'         => now(),
                    'latitude_survei'    => $validated['latitude_survei'] ?? null,
                    'longitude_survei'   => $validated['longitude_survei'] ?? null,
                    'lokasi_dicatat_at'  => !empty($validated['latitude_survei']) ? now() : null,
                ]
            );

            // 2. Revisi case: hapus komponen + foto lama (file fisik + record DB)
            $existingKomponenIds = SurveiKomponen::where('survei_id', $survei->id)->pluck('id');
            if ($existingKomponenIds->isNotEmpty()) {
                $oldFotos = SurveiFoto::whereIn('survei_komponen_id', $existingKomponenIds)->get();
                foreach ($oldFotos as $oldFoto) {
                    $this->fotoStorage->hapus($oldFoto->path_file);
                    $oldFoto->delete();
                }
                SurveiKomponen::where('survei_id', $survei->id)->delete();
            }

            // 3. Insert komponen baru + upload foto (jika ada)
            foreach ($validated['kondisi'] as $komponenId => $opsiId) {
                $surveiKomponen = SurveiKomponen::create([
                    'survei_id'        => $survei->id,
                    'komponen_id'      => $komponenId,
                    'opsi_kondisi_id'  => $opsiId,
                ]);

                $fotos = $validated['foto'][$komponenId] ?? [];
                $currentCount = 0;
                foreach ($fotos as $file) {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        continue;
                    }

                    $fotoData = $this->fotoStorage->simpan(
                        $file,
                        "survei-foto/{$rumah->id}/{$surveiKomponen->id}",
                        $currentCount
                    );

                    SurveiFoto::create(array_merge(
                        ['survei_komponen_id' => $surveiKomponen->id],
                        $fotoData
                    ));
                    $currentCount++;
                }
            }

            // 4. Update status workflow
            $this->workflow->ajukan($rumah, $user);

            $this->auditLogger->catat(
                aksi: AuditLog::AKSI['SURVEI_SUBMIT'],
                entitas: 'rumah_warga',
                entitasId: $rumah->id,
                dataBaru: [
                    'survei_id' => $survei->id,
                    'status'    => $rumah->fresh()->status,
                    'gps_lat'   => $validated['latitude_survei'] ?? null,
                    'gps_lng'   => $validated['longitude_survei'] ?? null,
                    'foto_count' => SurveiFoto::whereHas('surveiKomponen', function ($q) use ($survei) {
                        $q->where('survei_id', $survei->id);
                    })->count(),
                ],
            );
        });

        return redirect()
            ->route('survei.index')
            ->with('success', "Survei untuk {$rumah->kode_registrasi} berhasil dikirim. Status sekarang: Diajukan (menunggu verifikasi).");
    }
}
