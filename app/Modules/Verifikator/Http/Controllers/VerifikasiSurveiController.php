<?php

namespace App\Modules\Verifikator\Http\Controllers;

use App\Models\RumahWarga;
use App\Models\Wilayah;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Core\Services\NikEncryptor;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VerifikasiSurveiController extends Controller
{
    public function __construct(
        private readonly StatusWorkflow $workflow,
        private readonly AuditLogger $auditLogger,
        private readonly NikEncryptor $nikEncryptor,
    ) {}

    /**
     * SCRUM-110: Menampilkan antrean survei berstatus Diajukan dari semua wilayah.
     */
    public function index(Request $request)
    {
        $query = RumahWarga::with([
            'wilayah',
            'surveyor',
            'survei.komponenSurvei.foto',
        ])->where('status', RumahWarga::STATUS_DIAJUKAN);

        // Filter pencarian berdasarkan kode registrasi atau nama kepala keluarga
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                    ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%");
            });
        }

        // Filter berjenjang wilayah
        if ($wilayahId = $request->query('wilayah_id')) {
            $wilayah = Wilayah::find($wilayahId);
            if ($wilayah) {
                $query->whereHas('wilayah', function ($q) use ($wilayah) {
                    $q->where('id', $wilayah->id)
                        ->orWhere('path', 'like', $wilayah->path.'/%');
                });
            }
        }

        // Urutkan berdasarkan data pengajuan terbaru (updated_at)
        $antrean = $query->latest('updated_at')->paginate(15)->withQueryString();

        $totalAntrean = RumahWarga::where('status', RumahWarga::STATUS_DIAJUKAN)->count();
        $wilayahList = Wilayah::orderBy('tingkat')->orderBy('nama')->get();

        return view('verifikator.survei.index', compact('antrean', 'totalAntrean', 'wilayahList'));
    }

    /**
     * SCRUM-111: Halaman review survei dengan data lengkap dan galeri foto per komponen.
     */
    public function show(RumahWarga $rumah)
    {
        $rumah->load([
            'wilayah',
            'surveyor',
            'survei.komponenSurvei.komponen.opsiKondisi',
            'survei.komponenSurvei.opsiKondisi',
            'survei.komponenSurvei.foto',
            'riwayatStatus.user.role',
        ]);

        $survei = $rumah->survei()->first();

        // Hitung total foto bukti dan komponen indikasi RTLH
        $totalFoto = 0;
        $rtlhCount = 0;
        $apakahLengkap = false;

        if ($survei) {
            $totalFoto = $survei->komponenSurvei->sum(fn ($k) => $k->foto->count());
            $rtlhCount = $survei->komponenSurvei->filter(fn ($k) => $k->opsiKondisi?->otomatis_rtlh)->count();
            $apakahLengkap = $survei->apakahLengkap();
        }

        return view('verifikator.survei.show', compact('rumah', 'survei', 'totalFoto', 'rtlhCount', 'apakahLengkap'));
    }

    /**
     * Verifikator meminta perbaikan survei (Diajukan -> Perlu Perbaikan).
     */
    public function mintaPerbaikan(Request $request, RumahWarga $rumah)
    {
        if ($rumah->status !== RumahWarga::STATUS_DIAJUKAN) {
            return back()->with('warning', 'Hanya survei berstatus Diajukan yang dapat diminta perbaikan.');
        }

        $validated = $request->validate([
            'catatan' => 'required|string|min:5|max:1000',
        ], [
            'catatan.required' => 'Catatan perbaikan wajib diisi.',
            'catatan.min' => 'Catatan perbaikan minimal 5 karakter.',
            'catatan.max' => 'Catatan perbaikan maksimal 1000 karakter.',
        ]);

        $this->workflow->mintaPerbaikan($rumah, $validated['catatan'], auth()->user());

        return redirect()->route('verifikator.survei.index')
            ->with('success', "Survei rumah {$rumah->kode_registrasi} berhasil dikembalikan ke Surveyor untuk perbaikan.");
    }

    /**
     * Verifikator menetapkan kategori kelayakan manual (Diajukan -> Layak Huni / Penerima Bantuan).
     */
    public function tetapkanKategori(Request $request, RumahWarga $rumah)
    {
        if ($rumah->status !== RumahWarga::STATUS_DIAJUKAN) {
            return back()->with('warning', 'Hanya survei berstatus Diajukan yang dapat ditetapkan kategorinya.');
        }

        $validated = $request->validate([
            'kategori' => 'required|in:layak_huni,tidak_layak_huni',
            'catatan' => 'required|string|min:5|max:1000',
        ], [
            'kategori.required' => 'Pilihan kategori wajib ditentukan (Layak Huni atau Tidak Layak Huni).',
            'catatan.required' => 'Catatan alasan penetapan wajib diisi.',
            'catatan.min' => 'Catatan alasan minimal 5 karakter.',
            'catatan.max' => 'Catatan alasan maksimal 1000 karakter.',
        ]);

        $this->workflow->tetapkanKategori($rumah, $validated['kategori'], $validated['catatan'], auth()->user());

        $statusLabel = $validated['kategori'] === 'tidak_layak_huni' ? 'Penerima Bantuan' : 'Layak Huni';

        return redirect()->route('verifikator.survei.index')
            ->with('success', "Keputusan berhasil disimpan. Status rumah {$rumah->kode_registrasi} ditetapkan sebagai {$statusLabel}.");
    }
}
