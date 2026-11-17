<?php

namespace App\Modules\Bantuan\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PenerimaBantuan;
use App\Models\RumahWarga;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * UC-13: Verifikator mencatat penyaluran bantuan
 * (rumah Penerima Bantuan -> Dalam Renovasi).
 */
class PenyaluranController extends Controller
{
    public function __construct(
        private readonly StatusWorkflow $workflow,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function create(RumahWarga $rumah)
    {
        if ($tolak = $this->tolakJikaTidakBisa($rumah)) {
            return $tolak;
        }

        return view('bantuan.salurkan', compact('rumah'));
    }

    public function store(Request $request, RumahWarga $rumah)
    {
        if ($tolak = $this->tolakJikaTidakBisa($rumah)) {
            return $tolak;
        }

        $data = $request->validate([
            'tanggal_bantuan' => 'required|date_format:Y-m-d',
            'jenis_bantuan'   => 'required|string|max:100',
            'sumber_program'  => 'required|string|max:100',
            'nominal'         => 'required|numeric|min:0',
            'nomor_sk'        => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($rumah, $data) {
            $bantuan = PenerimaBantuan::create($data + [
                'rumah_id'       => $rumah->id,
                'status_bantuan' => 'Disalurkan',
            ]);

            $this->workflow->mulaiRenovasi(
                $rumah,
                "Bantuan disalurkan: {$data['jenis_bantuan']} (SK {$data['nomor_sk']})"
            );

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['BANTUAN_CREATE'],
                entitas:   'penerima_bantuan',
                entitasId: $bantuan->id,
                dataBaru:  ['rumah_id' => $rumah->id] + $data,
            );
        });

        return redirect()->route('bantuan.index')
            ->with('success', 'Penyaluran bantuan dicatat. Rumah masuk tahap renovasi.');
    }

    private function tolakJikaTidakBisa(RumahWarga $rumah)
    {
        if ($rumah->status !== RumahWarga::STATUS_PENERIMA_BANTUAN || $rumah->penerimaBantuan()->exists()) {
            return redirect()->route('bantuan.index')->with(
                'warning',
                'Penyaluran hanya dapat dicatat untuk rumah berstatus Penerima Bantuan yang belum disalurkan.'
            );
        }

        return null;
    }
}
