<?php

namespace App\Modules\Bantuan\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PenerimaBantuan;
use App\Models\ProgresRenovasi;
use App\Models\RumahWarga;
use App\Modules\Bantuan\Services\AturanProgres;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CRUD progres renovasi. Perubahan hanya oleh Verifikator; Admin dan Pimpinan hanya melihat.
 */
class ProgresRenovasiController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly AturanProgres $aturan,
    ) {
    }

    public function index(RumahWarga $rumah)
    {
        $bantuan = $this->bantuan($rumah);

        return view('bantuan.progres.index', [
            'rumah'   => $rumah,
            'bantuan' => $bantuan,
            'daftar'  => $bantuan->progres,
        ]);
    }

    public function create(RumahWarga $rumah)
    {
        $bantuan = $this->bantuan($rumah);

        return view('bantuan.progres.form', ['rumah' => $rumah, 'bantuan' => $bantuan, 'progres' => null]);
    }

    public function store(Request $request, RumahWarga $rumah)
    {
        $bantuan = $this->bantuan($rumah);
        $data    = $this->validasi($request);

        if ($error = $this->tolak($bantuan, $data)) {
            return back()->withErrors(['persentase' => $error])->withInput();
        }

        $progres = $bantuan->progres()->create($data);

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['PROGRES_CREATE'],
            entitas:   'progres_renovasi',
            entitasId: $progres->id,
            dataBaru:  $data,
        );

        return redirect()->route('bantuan.progres.index', $rumah)->with('success', 'Progres renovasi ditambahkan.');
    }

    public function edit(RumahWarga $rumah, ProgresRenovasi $progres)
    {
        $bantuan = $this->bantuan($rumah);
        $this->pastikanMilik($bantuan, $progres);

        return view('bantuan.progres.form', compact('rumah', 'bantuan', 'progres'));
    }

    public function update(Request $request, RumahWarga $rumah, ProgresRenovasi $progres)
    {
        $bantuan = $this->bantuan($rumah);
        $this->pastikanMilik($bantuan, $progres);
        $data = $this->validasi($request);

        if ($error = $this->tolak($bantuan, $data, $progres->id)) {
            return back()->withErrors(['persentase' => $error])->withInput();
        }

        $dataLama = $progres->only(['tahap_pekerjaan', 'persentase', 'tanggal_progres', 'catatan']);
        $progres->update($data);

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['PROGRES_UPDATE'],
            entitas:   'progres_renovasi',
            entitasId: $progres->id,
            dataBaru:  $data,
            dataLama:  $dataLama,
        );

        return redirect()->route('bantuan.progres.index', $rumah)->with('success', 'Progres renovasi diperbarui.');
    }

    public function destroy(RumahWarga $rumah, ProgresRenovasi $progres)
    {
        $bantuan = $this->bantuan($rumah);
        $this->pastikanMilik($bantuan, $progres);

        if ($error = $this->aturan->periksaUbah($bantuan)) {
            return back()->withErrors(['progres' => $error]);
        }

        $dataLama = $progres->only(['tahap_pekerjaan', 'persentase', 'tanggal_progres', 'catatan']);
        $progres->delete();

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['PROGRES_DELETE'],
            entitas:   'progres_renovasi',
            entitasId: $progres->id,
            dataLama:  $dataLama,
        );

        return redirect()->route('bantuan.progres.index', $rumah)->with('success', 'Progres renovasi dihapus.');
    }

    private function bantuan(RumahWarga $rumah): PenerimaBantuan
    {
        $bantuan = $rumah->penerimaBantuan;
        abort_unless($bantuan, 404);

        return $bantuan;
    }

    private function pastikanMilik(PenerimaBantuan $bantuan, ProgresRenovasi $progres): void
    {
        abort_unless($progres->penerima_bantuan_id === $bantuan->id, 404);
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'tahap_pekerjaan' => 'required|string|max:150',
            'persentase'      => 'required|integer|between:0,100',
            'tanggal_progres' => 'required|date_format:Y-m-d',
            'catatan'         => 'nullable|string|max:1000',
        ]);
        $data['persentase'] = (int) $data['persentase'];

        return $data;
    }

    private function tolak(PenerimaBantuan $bantuan, array $data, ?int $abaikanId = null): ?string
    {
        return $this->aturan->periksaUbah($bantuan)
            ?? $this->aturan->periksaNilai($bantuan, $data['persentase'], $data['tanggal_progres'], $abaikanId);
    }
}
