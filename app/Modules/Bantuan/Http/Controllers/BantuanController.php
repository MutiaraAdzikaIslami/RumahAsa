<?php

namespace App\Modules\Bantuan\Http\Controllers;

use App\Models\RumahWarga;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BantuanController extends Controller
{
    /** Status rumah yang termasuk dalam daftar penerima bantuan. */
    public const STATUS_BANTUAN = [
        RumahWarga::STATUS_PENERIMA_BANTUAN,
        RumahWarga::STATUS_DALAM_RENOVASI,
        RumahWarga::STATUS_SELESAI,
    ];

    public function index(Request $request)
    {
        $query = RumahWarga::with([
            'wilayah.parent.parent.parent',
            'surveyor',
            'penerimaBantuan.progres.fotos',
            'riwayatStatus',
        ])->whereIn('status', self::STATUS_BANTUAN);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhereHas('wilayah', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->query('status')) {
            if (in_array($status, self::STATUS_BANTUAN, true)) {
                $query->where('status', $status);
            }
        }

        if ($tahap = $request->query('tahap')) {
            $query->whereHas('penerimaBantuan.progres', function ($q) use ($tahap) {
                $q->where('tahap_pekerjaan', 'like', "%{$tahap}%");
            });
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

        $totalPenerima      = RumahWarga::whereIn('status', self::STATUS_BANTUAN)->count();
        $belumDimulaiCount  = RumahWarga::where('status', RumahWarga::STATUS_PENERIMA_BANTUAN)->count();
        $dalamRenovasiCount = RumahWarga::where('status', RumahWarga::STATUS_DALAM_RENOVASI)->count();
        $selesaiCount       = RumahWarga::where('status', RumahWarga::STATUS_SELESAI)->count();

        $rumah = $query->latest('updated_at')->paginate(10)->withQueryString();

        $wilayahList = Wilayah::where(function ($q) {
            $q->whereHas('rumahWarga')->orWhereIn('tingkat', [
                Wilayah::TINGKAT_KECAMATAN,
                Wilayah::TINGKAT_KELURAHAN,
            ]);
        })->orderBy('tingkat')->orderBy('nama')->limit(150)->get();

        return view('bantuan.index', compact(
            'rumah',
            'wilayahList',
            'totalPenerima',
            'belumDimulaiCount',
            'dalamRenovasiCount',
            'selesaiCount'
        ));
    }

    /**
     * FR-28: Rekap Dana per Triwulan (Verifikator, Pimpinan, Admin).
     */
    public function rekap(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);
        $triwulan = $request->query('triwulan');
        $sumberProgram = $request->query('sumber_program');
        $wilayahId = $request->query('wilayah_id');
        $search = $request->query('search');

        $query = \App\Models\PenerimaBantuan::with(['rumah.wilayah.parent.parent.parent'])
            ->whereYear('tanggal_bantuan', $tahun);

        if ($triwulan && in_array((int) $triwulan, [1, 2, 3, 4], true)) {
            $bulanMulai = ((int) $triwulan - 1) * 3 + 1;
            $bulanAkhir = $bulanMulai + 2;
            $query->whereMonth('tanggal_bantuan', '>=', $bulanMulai)
                  ->whereMonth('tanggal_bantuan', '<=', $bulanAkhir);
        }

        if ($sumberProgram) {
            $query->where('sumber_program', $sumberProgram);
        }

        if ($wilayahId) {
            $wilayah = Wilayah::find($wilayahId);
            if ($wilayah) {
                $query->whereHas('rumah.wilayah', function ($q) use ($wilayah) {
                    $q->where('id', $wilayah->id)
                      ->orWhere('path', 'like', $wilayah->path . '/%');
                });
            }
        }

        if ($search) {
            $query->whereHas('rumah', function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $daftarBantuan = $query->orderByDesc('tanggal_bantuan')->get();
        $totalNominal = $daftarBantuan->sum('nominal');
        $totalPenerima = $daftarBantuan->count();

        $rekapSumber = $daftarBantuan->groupBy(fn ($b) => $b->sumber_program ?: 'APBD')
            ->map(fn ($items) => [
                'jumlah'    => $items->count(),
                'nominal'   => $items->sum('nominal'),
                'rata_rata' => $items->count() > 0 ? $items->sum('nominal') / $items->count() : 0,
            ]);

        $rekapJenis = $daftarBantuan->groupBy(fn ($b) => $b->jenis_bantuan ?: 'Lainnya')
            ->map(fn ($items) => [
                'jumlah'  => $items->count(),
                'nominal' => $items->sum('nominal'),
            ]);

        $wilayahList = Wilayah::where(function ($q) {
            $q->whereHas('rumahWarga')->orWhereIn('tingkat', [
                Wilayah::TINGKAT_KECAMATAN,
                Wilayah::TINGKAT_KELURAHAN,
            ]);
        })->orderBy('tingkat')->orderBy('nama')->limit(150)->get();

        return view('bantuan.rekap', compact(
            'daftarBantuan',
            'totalNominal',
            'totalPenerima',
            'rekapSumber',
            'rekapJenis',
            'wilayahList',
            'tahun',
            'triwulan',
            'sumberProgram',
            'wilayahId',
            'search'
        ));
    }
}
