<?php

namespace App\Modules\Bantuan\Http\Controllers;

use App\Models\RumahWarga;
use App\Models\Wilayah;
use App\Modules\Bantuan\Services\LaporanService;
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

    public function __construct(
        private readonly LaporanService $laporanService,
    ) {
    }

    public function index(Request $request)
    {
        $query = $this->buildDaftarQuery($request);

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
     * FR-27: Ekspor Daftar Penerima Bantuan / Progres Renovasi ke Excel (.xlsx) atau PDF (.pdf).
     */
    public function eksporDaftar(Request $request)
    {
        $format = strtolower((string) $request->query('format', 'excel'));
        $isProgres = $request->query('mode') === 'progres';
        $daftarRumah = $this->buildDaftarQuery($request)->latest('updated_at')->get();

        $metaFilter = $request->only(['mode', 'status', 'wilayah_id', 'tahap', 'search']);
        $jenisEntitas = $isProgres ? 'progres_renovasi' : 'penerima_bantuan';
        $prefixFile = $isProgres ? 'progres-renovasi' : 'daftar-penerima-bantuan';
        $timestamp = now()->format('Ymd-His');

        if ($format === 'pdf') {
            return $this->laporanService->eksporPdf(
                jenis:      $jenisEntitas,
                view:       'bantuan.pdf.daftar-penerima',
                viewData:   [
                    'daftarRumah'  => $daftarRumah,
                    'judulLaporan' => $isProgres ? 'Laporan Monitoring Progres Renovasi' : 'Laporan Daftar Penerima Bantuan Renovasi',
                ],
                filename:   "{$prefixFile}-{$timestamp}.pdf",
                metaFilter: $metaFilter,
            );
        }

        $headings = [
            'No',
            'Kode Registrasi',
            'Nama Pemilik',
            'NIK (Tersamar)',
            'Alamat Lengkap',
            'Wilayah',
            'Tanggal Penetapan',
            'Status Bantuan',
            'Tahap Renovasi',
            'Progres Renovasi (%)',
            'Surveyor',
            'Terakhir Diperbarui',
        ];

        $rows = [];
        foreach ($daftarRumah as $idx => $r) {
            $bantuan = $r->penerimaBantuan;
            $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
            $progresTerbaru = $bantuan?->progres?->sortByDesc('tanggal_progres')->first();
            $tglPenetapan = $r->riwayatStatus->where('status_ke', RumahWarga::STATUS_PENERIMA_BANTUAN)->sortByDesc('id')->first()?->created_at
                ?? ($bantuan?->tanggal_bantuan ? \Carbon\Carbon::parse($bantuan->tanggal_bantuan) : $r->created_at);

            $rows[] = [
                $idx + 1,
                $r->kode_registrasi,
                $r->nama_kepala_keluarga,
                $r->nik_enc ? $r->nikTersamar() : '-',
                $r->alamatLengkap(),
                $r->wilayahSingkat(),
                $tglPenetapan ? $tglPenetapan->format('d M Y') : '-',
                $r->status,
                $progresTerbaru?->tahap_pekerjaan ?? ($r->status === 'Selesai' ? 'Finishing' : 'Persiapan'),
                $persen . '%',
                $r->surveyor?->nama ?? '-',
                $r->terakhirDiperbaruiLabel(),
            ];
        }

        return $this->laporanService->eksporExcel(
            jenis:      $jenisEntitas,
            headings:   $headings,
            rows:       $rows,
            filename:   "{$prefixFile}-{$timestamp}.xlsx",
            metaFilter: $metaFilter,
        );
    }

    /**
     * FR-28 & FR-29: Rekap Dana per Triwulan (Verifikator, Pimpinan, Admin).
     */
    public function rekap(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);
        $triwulan = $request->query('triwulan');
        $sumberProgram = $request->query('sumber_program');
        $wilayahId = $request->query('wilayah_id');
        $search = $request->query('search');

        $rekapData = $this->laporanService->rekapDanaTriwulan([
            'tahun'          => $tahun,
            'triwulan'       => $triwulan,
            'sumber_program' => $sumberProgram,
            'wilayah_id'     => $wilayahId,
            'search'         => $search,
        ]);

        $daftarBantuan = $rekapData['daftarBantuan'];
        $totalNominal  = $rekapData['totalNominal'];
        $totalPenerima = $rekapData['totalPenerima'];
        $rekapSumber   = $rekapData['rekapSumber'];
        $rekapJenis    = $rekapData['rekapJenis'];

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

    /**
     * FR-30: Ekspor Rekap Dana per Triwulan ke Excel (.xlsx) atau PDF (.pdf) beserta AuditLog.
     */
    public function eksporRekap(Request $request)
    {
        $format = strtolower((string) $request->query('format', 'excel'));
        $tahun = (int) $request->query('tahun', now()->year);
        $triwulan = $request->query('triwulan');
        $sumberProgram = $request->query('sumber_program');
        $wilayahId = $request->query('wilayah_id');
        $search = $request->query('search');

        $filter = [
            'tahun'          => $tahun,
            'triwulan'       => $triwulan,
            'sumber_program' => $sumberProgram,
            'wilayah_id'     => $wilayahId,
            'search'         => $search,
        ];

        $rekapData = $this->laporanService->rekapDanaTriwulan($filter);
        $daftarBantuan = $rekapData['daftarBantuan'];
        $namaWilayah = $wilayahId ? (Wilayah::find($wilayahId)?->nama ?? 'Terpilih') : 'Semua Wilayah';
        $timestamp = now()->format('Ymd-His');

        if ($format === 'pdf') {
            return $this->laporanService->eksporPdf(
                jenis:      'rekap_dana',
                view:       'bantuan.pdf.rekap-dana',
                viewData:   $rekapData + [
                    'tahun'         => $tahun,
                    'triwulan'      => $triwulan,
                    'sumberProgram' => $sumberProgram,
                    'namaWilayah'   => $namaWilayah,
                ],
                filename:   "rekap-dana-bantuan-{$tahun}-{$timestamp}.pdf",
                metaFilter: $filter,
            );
        }

        $headings = [
            'No',
            'Kode Registrasi',
            'Nama Kepala Keluarga',
            'NIK (Tersamar)',
            'Wilayah',
            'Jenis Bantuan',
            'Sumber Program',
            'Status Renovasi',
            'Nominal Bantuan (Rp)',
            'Nomor SK',
            'Tanggal Penyaluran',
        ];

        $rows = [];
        foreach ($daftarBantuan as $idx => $b) {
            $rm = $b->rumah;
            $rows[] = [
                $idx + 1,
                $rm?->kode_registrasi ?? '-',
                $rm?->nama_kepala_keluarga ?? '-',
                $rm && $rm->nik_enc ? $rm->nikTersamar() : '-',
                $rm ? $rm->wilayahSingkat() : '-',
                $b->jenis_bantuan,
                $b->sumber_program ?: 'APBD',
                $rm?->status ?? '-',
                (float) $b->nominal,
                $b->nomor_sk,
                $b->tanggal_bantuan ? \Carbon\Carbon::parse($b->tanggal_bantuan)->format('Y-m-d') : '-',
            ];
        }

        return $this->laporanService->eksporExcel(
            jenis:      'rekap_dana',
            headings:   $headings,
            rows:       $rows,
            filename:   "rekap-dana-bantuan-{$tahun}-{$timestamp}.xlsx",
            metaFilter: $filter,
        );
    }

    private function buildDaftarQuery(Request $request)
    {
        $query = RumahWarga::with([
            'wilayah.parent.parent.parent',
            'surveyor',
            'penerimaBantuan.progres.fotos',
            'riwayatStatus.user',
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

        return $query;
    }
}
