<?php

namespace App\Modules\Bantuan\Http\Controllers;

use App\Models\RumahWarga;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Pemantauan progres renovasi dan penetapan status Selesai (Verifikator).
 */
class PemantauanController extends Controller
{
    public function __construct(
        private readonly StatusWorkflow $workflow,
    ) {
    }

    public function show(RumahWarga $rumah)
    {
        abort_unless(in_array($rumah->status, BantuanController::STATUS_BANTUAN, true), 404);

        $rumah->load([
            'wilayah.parent.parent.parent',
            'surveyor',
            'penerimaBantuan.progres.fotos',
            'survei.komponenSurvei.foto',
            'riwayatStatus.user',
        ]);

        return view('bantuan.show', ['rumah' => $rumah, 'bantuan' => $rumah->penerimaBantuan]);
    }

    /**
     * Halaman Detail Penerima Bantuan (diakses dari Daftar Penerima Bantuan — Mockup Halaman 11 & 10).
     */
    public function showPenerima(RumahWarga $rumah)
    {
        abort_unless(in_array($rumah->status, BantuanController::STATUS_BANTUAN, true), 404);

        $rumah->load([
            'wilayah.parent.parent.parent',
            'surveyor',
            'penerimaBantuan.progres.fotos',
            'survei.komponenSurvei.foto',
            'riwayatStatus.user',
        ]);

        return view('bantuan.penerima', ['rumah' => $rumah, 'bantuan' => $rumah->penerimaBantuan]);
    }

    public function selesai(Request $request, RumahWarga $rumah)
    {
        $bantuan = $rumah->penerimaBantuan;

        if ($rumah->status !== RumahWarga::STATUS_DALAM_RENOVASI || !$bantuan) {
            return back()->with('warning', 'Hanya rumah berstatus Dalam Renovasi yang dapat ditetapkan Selesai.');
        }

        if ($bantuan->progresTerakhir() < 100) {
            return back()->with('warning', 'Progres terakhir harus 100% sebelum rumah ditetapkan Selesai.');
        }

        $this->workflow->selesaikan($rumah, $request->input('catatan') ?: null);

        return redirect()->route('bantuan.show', $rumah)->with('success', 'Rumah ditetapkan Selesai.');
    }
}
