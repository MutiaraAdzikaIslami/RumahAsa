<?php

namespace App\Modules\Survei\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KomponenPenilaian;
use App\Models\RumahWarga;
use App\Models\SurveiKelayakan;
use App\Models\SurveiKomponen;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SurveiController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly StatusWorkflow $workflow,
    ) {
    }

    public function create(RumahWarga $rumah)
    {
        $komponenList = KomponenPenilaian::with('opsiKondisi')->orderBy('urutan')->get();

        $survei = $rumah->survei()->first();
        if (!$survei) {
            $survei = SurveiKelayakan::create([
                'rumah_id'      => $rumah->id,
                'surveyor_id'    => auth()->id(),
                'tanggal_survei' => now(),
            ]);
            foreach ($komponenList as $k) {
                SurveiKomponen::create([
                    'survei_id'   => $survei->id,
                    'komponen_id' => $k->id,
                ]);
            }
        }

        $survei->load(['komponenSurvei.komponen.opsiKondisi', 'komponenSurvei.opsiKondisi', 'komponenSurvei.foto']);

        return view('survei.create', compact('rumah', 'survei', 'komponenList'));
    }

    public function update(Request $request, RumahWarga $rumah, SurveiKelayakan $survei)
    {
        $isDraft = $request->input('action') === 'draft';

        $rules = [];
        foreach ($request->input('komponen', []) as $komponenId => $data) {
            $rules["komponen.{$komponenId}.opsi_kondisi_id"] = 'nullable|exists:opsi_kondisi,id';
            $rules["komponen.{$komponenId}.catatan"] = 'nullable|string';
        }
        $validated = $request->validate($rules);

        // FIX: Pakai $request->input('komponen', []) bukan $validated['komponen']
        $komponenData = $request->input('komponen', []);
        foreach ($komponenData as $komponenId => $data) {
            SurveiKomponen::where('survei_id', $survei->id)
                ->where('komponen_id', $komponenId)
                ->update([
                    'opsi_kondisi_id' => $data['opsi_kondisi_id'] ?? null,
                    'catatan'          => $data['catatan'] ?? null,
                ]);
        }

        if (!$isDraft) {
            $survei->refresh();
            if (!$survei->apakahLengkap()) {
                return back()->withErrors(['msg' => 'Survei belum lengkap. Pastikan 7 komponen sudah dinilai dan ada minimal 1 foto per komponen.'])->withInput();
            }

            $survei->update(['dikirim_at' => now()]);
            $this->workflow->ajukan($rumah);

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['SURVEI_SUBMIT'],
                entitas:   'survei_kelayakan',
                entitasId: $survei->id,
            );

            return redirect()->route('rumah.show', $rumah)->with('success', 'Survei berhasil dikirim ke Verifikator.');
        }

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['SURVEI_DRAFT_SAVE'],
            entitas:   'survei_kelayakan',
            entitasId: $survei->id,
        );

        return redirect()->route('rumah.show', $rumah)->with('success', 'Draf survei berhasil disimpan.');
    }
}