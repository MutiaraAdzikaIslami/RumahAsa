<?php

namespace App\Modules\Survei\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SurveiFoto;
use App\Models\SurveiKomponen;
use App\Modules\Core\Exceptions\FileValidationException;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Core\Services\FotoStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SurveiFotoController extends Controller
{
    public function __construct(
        private readonly FotoStorageService $fotoService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * FR-11b: Upload foto bukti per komponen.
     */
    public function store(Request $request, SurveiKomponen $komponen): JsonResponse
    {
        $request->validate(['file' => 'required|file']);

        try {
            $currentFotoCount = $komponen->foto()->count();
            $dir = "survei-foto/{$komponen->survei_id}/{$komponen->id}";
            
            $data = $this->fotoService->simpan($request->file('file'), $dir, $currentFotoCount);
            
            $foto = $komponen->foto()->create($data);

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['FOTO_UPLOAD'],
                entitas:   'survei_foto',
                entitasId: $foto->id,
                dataBaru:  ['survei_komponen_id' => $komponen->id],
            );

            // FIX: Route name harusnya survei.foto.show (pakai titik)
            return response()->json([
                'success' => true,
                'foto'    => $foto,
                'url'     => route('survei.foto.show', $foto),
            ]);
        } catch (FileValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * FR-11b: Sajikan foto via endpoint terautentikasi.
     */
    public function show(SurveiFoto $foto)
    {
        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['FOTO_VIEW'],
            entitas:   'survei_foto',
            entitasId: $foto->id,
        );

        return $this->fotoService->sajikan($foto->path_file);
    }

    /**
     * FR-11b: Hapus foto.
     */
    public function destroy(SurveiFoto $foto): JsonResponse
    {
        $this->fotoService->hapus($foto->path_file);
        
        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['FOTO_DELETE'],
            entitas:   'survei_foto',
            entitasId: $foto->id,
        );

        $foto->delete();

        return response()->json(['success' => true]);
    }
}