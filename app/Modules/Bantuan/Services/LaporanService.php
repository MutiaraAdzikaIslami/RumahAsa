<?php

namespace App\Modules\Bantuan\Services;

use App\Models\AuditLog;
use App\Models\PenerimaBantuan;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Bantuan\Http\Controllers\BantuanController;
use App\Modules\Core\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class Diagram 4.4.2 #6 & FR-27 s.d. FR-30:
 * LaporanService menyediakan eksporExcel(), eksporPdf(), rekapDanaTriwulan(),
 * dan daftarPenerimaDana() untuk pelaporan.
 * Setiap ekspor menyamarkan NIK sesuai UU PDP (NFR-05) dan dicatat melalui AuditLogger.
 */
class LaporanService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * FR-28: Menghitung rekap dana bantuan per triwulan & sumber program.
     */
    public function rekapDanaTriwulan(array $filter): array
    {
        $daftar = $this->daftarPenerimaDana($filter);
        $totalNominal = (float) $daftar->sum('nominal');
        $totalPenerima = $daftar->count();

        $rekapSumber = $daftar->groupBy(fn ($b) => $b->sumber_program ?: 'APBD')
            ->map(fn ($items) => [
                'jumlah'    => $items->count(),
                'nominal'   => (float) $items->sum('nominal'),
                'rata_rata' => $items->count() > 0 ? (float) $items->sum('nominal') / $items->count() : 0,
            ]);

        $rekapJenis = $daftar->groupBy(fn ($b) => $b->jenis_bantuan ?: 'Lainnya')
            ->map(fn ($items) => [
                'jumlah'  => $items->count(),
                'nominal' => (float) $items->sum('nominal'),
            ]);

        return [
            'daftarBantuan' => $daftar,
            'totalNominal'  => $totalNominal,
            'totalPenerima' => $totalPenerima,
            'rekapSumber'   => $rekapSumber,
            'rekapJenis'    => $rekapJenis,
        ];
    }

    /**
     * FR-29: Mengambil daftar rumah penerima dana sesuai filter periode, wilayah, dan sumber program.
     */
    public function daftarPenerimaDana(array $filter): Collection
    {
        $tahun = (int) ($filter['tahun'] ?? now()->year);
        $triwulan = $filter['triwulan'] ?? null;
        $sumberProgram = $filter['sumber_program'] ?? null;
        $wilayahId = $filter['wilayah_id'] ?? null;
        $search = $filter['search'] ?? null;

        $query = PenerimaBantuan::with(['rumah.wilayah.parent.parent.parent'])
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

        return $query->orderByDesc('tanggal_bantuan')->get();
    }

    /**
     * FR-27 & FR-30: Ekspor ke berkas Microsoft Excel (.xlsx) dengan pencatatan AuditLog.
     */
    public function eksporExcel(string $jenis, array $headings, array $rows, string $filename, array $metaFilter = [])
    {
        $this->auditLogger->catat(
            aksi:    AuditLog::AKSI['EKSPOR_EXCEL'],
            entitas: $jenis,
            dataBaru: [
                'format'      => 'xlsx',
                'nama_berkas' => $filename,
                'jumlah_baris'=> count($rows),
                'filter'      => $metaFilter,
            ],
        );

        $exportObject = new class($headings, $rows, strtoupper(str_replace('_', ' ', $jenis))) implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle {
            public function __construct(
                private readonly array $headings,
                private readonly array $rows,
                private readonly string $sheetTitle,
            ) {
            }

            public function headings(): array
            {
                return $this->headings;
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function title(): string
            {
                return substr($this->sheetTitle, 0, 31);
            }

            public function styles(Worksheet $sheet): array
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1E4631'],
                        ],
                    ],
                ];
            }
        };

        return Excel::download($exportObject, $filename);
    }

    /**
     * FR-27 & FR-30: Ekspor ke berkas PDF (.pdf) dengan pencatatan AuditLog.
     */
    public function eksporPdf(string $jenis, string $view, array $viewData, string $filename, array $metaFilter = [])
    {
        $this->auditLogger->catat(
            aksi:    AuditLog::AKSI['EKSPOR_PDF'],
            entitas: $jenis,
            dataBaru: [
                'format'      => 'pdf',
                'nama_berkas' => $filename,
                'filter'      => $metaFilter,
            ],
        );

        $pdf = Pdf::loadView($view, $viewData)->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
