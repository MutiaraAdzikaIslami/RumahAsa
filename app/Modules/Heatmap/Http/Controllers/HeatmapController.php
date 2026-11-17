<?php

namespace App\Modules\Heatmap\Http\Controllers;

use App\Models\RumahWarga;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;

class HeatmapController extends Controller
{
    /**
     * FR-31 / SCRUM-160 / SCRUM-163:
     * Endpoint data heatmap: koordinat rumah per empat lapisan,
     * mengikuti filter wilayah, triwulan, dan sumber program.
     *
     * Akses:
     * - Surveyor: hanya wilayah tugasnya
     * - Verifikator: semua wilayah
     * - Pimpinan: semua wilayah
     * - Admin / Lainnya: 403 Forbidden
     */
    public function data(Request $request): JsonResponse
    {
        $user = $request->user();

        // SCRUM-163: Batasi akses role
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userRole = $user->role?->kode;
        $allowedRoles = ['surveyor', 'verifikator', 'pimpinan'];

        if (! in_array($userRole, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Akses ditolak. Fitur heatmap hanya untuk Surveyor, Verifikator, dan Pimpinan.',
            ], 403);
        }

        // Base Query: Hanya rumah yang memiliki koordinat latitude & longitude
        $baseQuery = RumahWarga::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter Wilayah
        $filterWilayahId = $request->query('wilayah_id');

        if ($user->isSurveyor()) {
            // Surveyor HANYA melihat wilayah tugasnya
            $surveyorWilayah = $user->wilayah;

            if (! $surveyorWilayah) {
                // Surveyor belum memiliki wilayah tugas -> kembalikan data kosong
                return response()->json($this->emptyResponse('Surveyor belum memiliki wilayah tugas.'));
            }

            $surveyorPath = $surveyorWilayah->path;

            if ($filterWilayahId) {
                $requestedWilayah = Wilayah::find($filterWilayahId);
                // Pastikan wilayah yang dipilih berada di dalam wilayah tugas surveyor
                if ($requestedWilayah && ($requestedWilayah->id === $surveyorWilayah->id || str_starts_with($requestedWilayah->path ?? '', $surveyorPath.'/'))) {
                    $targetPath = $requestedWilayah->path;
                    $baseQuery->whereHas('wilayah', function ($q) use ($targetPath) {
                        $q->where('path', $targetPath)
                            ->orWhere('path', 'like', $targetPath.'/%');
                    });
                } else {
                    // Jika di luar wilayah tugas, tetap batasi ke wilayah tugas surveyor
                    $baseQuery->whereHas('wilayah', function ($q) use ($surveyorPath) {
                        $q->where('path', $surveyorPath)
                            ->orWhere('path', 'like', $surveyorPath.'/%');
                    });
                }
            } else {
                $baseQuery->whereHas('wilayah', function ($q) use ($surveyorPath) {
                    $q->where('path', $surveyorPath)
                        ->orWhere('path', 'like', $surveyorPath.'/%');
                });
            }
        } else {
            // Verifikator & Pimpinan: Semua wilayah secara default, atau sesuai filter wilayah
            if ($filterWilayahId) {
                $wilayah = Wilayah::find($filterWilayahId);
                if ($wilayah) {
                    $targetPath = $wilayah->path;
                    $baseQuery->whereHas('wilayah', function ($q) use ($targetPath) {
                        $q->where('path', $targetPath)
                            ->orWhere('path', 'like', $targetPath.'/%');
                    });
                }
            }
        }

        // Filter Triwulan (berlaku untuk SEMUA lapisan)
        // Dihitung dari tanggal mulai status saat ini di riwayat_status (fallback ke updated_at/created_at)
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        $driver = Schema::getConnection()->getDriverName();
        $dateExpr = 'COALESCE((SELECT MAX(created_at) FROM riwayat_status WHERE riwayat_status.rumah_id = rumah_warga.id AND riwayat_status.status_ke = rumah_warga.status), rumah_warga.updated_at, rumah_warga.created_at)';

        if ($driver === 'sqlite') {
            $monthExpr = "CAST(strftime('%m', {$dateExpr}) AS INTEGER)";
            $yearExpr = "CAST(strftime('%Y', {$dateExpr}) AS INTEGER)";
        } elseif ($driver === 'pgsql') {
            $monthExpr = "EXTRACT(MONTH FROM {$dateExpr})";
            $yearExpr = "EXTRACT(YEAR FROM {$dateExpr})";
        } else {
            $monthExpr = "MONTH({$dateExpr})";
            $yearExpr = "YEAR({$dateExpr})";
        }

        if ($triwulan && in_array((int) $triwulan, [1, 2, 3, 4], true)) {
            $months = match ((int) $triwulan) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
            };

            $baseQuery->where(function ($q) use ($months, $tahun, $monthExpr, $yearExpr) {
                $q->whereRaw("{$monthExpr} IN (".implode(',', $months).')');
                if ($tahun) {
                    $q->whereRaw("{$yearExpr} = ?", [(int) $tahun]);
                }
            });
        } elseif ($tahun) {
            $baseQuery->where(function ($q) use ($tahun, $yearExpr) {
                $q->whereRaw("{$yearExpr} = ?", [(int) $tahun]);
            });
        }

        // Filter Sumber Program (HANYA memengaruhi 2 lapisan renovasi)
        $sumberProgram = $request->query('sumber_program');

        // 1. Lapisan: Menunggu Disurvei
        // Status 'Menunggu Disurvei' atau 'Draf' yang belum memiliki survei
        $menungguQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'Menunggu Disurvei')
                ->orWhere(function ($q2) {
                    $q2->where('status', RumahWarga::STATUS_DRAF)
                        ->whereDoesntHave('survei');
                });
        });

        // 2. Lapisan: Sedang Disurvei (Draf, Diajukan, Perlu Perbaikan)
        // Jika draf memiliki survei, atau status Diajukan / Perlu Perbaikan
        $sedangDisurveiQuery = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('status', [
                RumahWarga::STATUS_DIAJUKAN,
                RumahWarga::STATUS_PERLU_PERBAIKAN,
            ])->orWhere(function ($q2) {
                $q2->where('status', RumahWarga::STATUS_DRAF)
                    ->whereHas('survei');
            });
        });

        // 3. Lapisan: Sedang Renovasi (Dalam Renovasi)
        $sedangRenovasiQuery = (clone $baseQuery)->where('status', RumahWarga::STATUS_DALAM_RENOVASI);
        if ($sumberProgram) {
            $sedangRenovasiQuery->whereHas('penerimaBantuan', function ($q) use ($sumberProgram) {
                $q->where('sumber_program', $sumberProgram);
            });
        }

        // 4. Lapisan: Selesai Renovasi (Selesai)
        $selesaiQuery = (clone $baseQuery)->where('status', RumahWarga::STATUS_SELESAI);
        if ($sumberProgram) {
            $selesaiQuery->whereHas('penerimaBantuan', function ($q) use ($sumberProgram) {
                $q->where('sumber_program', $sumberProgram);
            });
        }

        // Ambil data koordinat per lapisan (HANYA lat, lng, weight — TANPA DATA PRIBADI)
        $menungguPoints = $menungguQuery->get(['latitude', 'longitude'])->map(fn ($r) => [
            'lat' => (float) $r->latitude,
            'lng' => (float) $r->longitude,
            'weight' => 1,
        ])->values();

        $sedangDisurveiPoints = $sedangDisurveiQuery->get(['latitude', 'longitude'])->map(fn ($r) => [
            'lat' => (float) $r->latitude,
            'lng' => (float) $r->longitude,
            'weight' => 1,
        ])->values();

        $sedangRenovasiPoints = $sedangRenovasiQuery->get(['latitude', 'longitude'])->map(fn ($r) => [
            'lat' => (float) $r->latitude,
            'lng' => (float) $r->longitude,
            'weight' => 1,
        ])->values();

        $selesaiPoints = $selesaiQuery->get(['latitude', 'longitude'])->map(fn ($r) => [
            'lat' => (float) $r->latitude,
            'lng' => (float) $r->longitude,
            'weight' => 1,
        ])->values();

        $countMenunggu = $menungguPoints->count();
        $countSedangDisurvei = $sedangDisurveiPoints->count();
        $countSedangRenovasi = $sedangRenovasiPoints->count();
        $countSelesai = $selesaiPoints->count();
        $totalRumah = $countMenunggu + $countSedangDisurvei + $countSedangRenovasi + $countSelesai;

        // Distribusi Area Terbanyak ("Area terbanyak" di panel kanan)
        $areaDistribution = $this->calculateAreaDistribution(
            $user,
            $baseQuery,
            $sumberProgram
        );

        return response()->json([
            'success' => true,
            'layers' => [
                'menunggu_disurvei' => $menungguPoints,
                'sedang_disurvei' => $sedangDisurveiPoints,
                'sedang_renovasi' => $sedangRenovasiPoints,
                'selesai' => $selesaiPoints,
            ],
            'counts' => [
                'menunggu_disurvei' => $countMenunggu,
                'sedang_disurvei' => $countSedangDisurvei,
                'sedang_renovasi' => $countSedangRenovasi,
                'selesai' => $countSelesai,
                'total' => $totalRumah,
            ],
            'area_distribution' => $areaDistribution,
            'wilayah_scope' => $user->isSurveyor() ? $user->wilayah?->nama : 'Semua Wilayah',
        ]);
    }

    /**
     * Hitung distribusi area terbanyak.
     * Untuk surveyor: breakdown berdasarkan RW di kelurahannya.
     * Untuk verifikator / pimpinan: breakdown berdasarkan Kelurahan di wilayahnya.
     */
    private function calculateAreaDistribution($user, $baseQuery, ?string $sumberProgram): array
    {
        // Query semua rumah yang masuk dalam salah satu dari 4 lapisan
        $combinedQuery = (clone $baseQuery)->where(function ($q) use ($sumberProgram) {
            $q->where('status', 'Menunggu Disurvei')
                ->orWhereIn('status', [
                    RumahWarga::STATUS_DRAF,
                    RumahWarga::STATUS_DIAJUKAN,
                    RumahWarga::STATUS_PERLU_PERBAIKAN,
                ])
                ->orWhere(function ($q2) use ($sumberProgram) {
                    $q2->whereIn('status', [
                        RumahWarga::STATUS_DALAM_RENOVASI,
                        RumahWarga::STATUS_SELESAI,
                    ]);
                    if ($sumberProgram) {
                        $q2->whereHas('penerimaBantuan', function ($sub) use ($sumberProgram) {
                            $sub->where('sumber_program', $sumberProgram);
                        });
                    }
                });
        })->with('wilayah');

        $houses = $combinedQuery->get(['id', 'wilayah_id']);
        $total = $houses->count();

        if ($total === 0) {
            return [
                'items' => [],
                'total' => 0,
            ];
        }

        $isSurveyor = $user->isSurveyor();
        $countsByArea = [];

        // Preload Wilayah lookup cache
        $wilayahMap = Wilayah::all()->keyBy('id');

        foreach ($houses as $house) {
            if (! $house->wilayah_id || ! isset($wilayahMap[$house->wilayah_id])) {
                $countsByArea['Lainnya'] = ($countsByArea['Lainnya'] ?? 0) + 1;

                continue;
            }

            $w = $wilayahMap[$house->wilayah_id];

            if ($isSurveyor) {
                // Cari entitas tingkat RW dari hierarki path
                $rwName = $this->findAncestorNameByTingkat($w, Wilayah::TINGKAT_RW, $wilayahMap);
                $label = $rwName ?? ($w->nama ?? 'Lainnya');
                $countsByArea[$label] = ($countsByArea[$label] ?? 0) + 1;
            } else {
                // Cari entitas tingkat Kelurahan dari hierarki path
                $kelName = $this->findAncestorNameByTingkat($w, Wilayah::TINGKAT_KELURAHAN, $wilayahMap);
                $label = $kelName ?? ($w->nama ?? 'Lainnya');
                $countsByArea[$label] = ($countsByArea[$label] ?? 0) + 1;
            }
        }

        // Urutkan terbesar ke terkecil
        arsort($countsByArea);

        $items = [];
        $topLimit = 4;
        $i = 0;
        $lainnyaCount = 0;

        foreach ($countsByArea as $nama => $count) {
            if ($i < $topLimit && $nama !== 'Lainnya') {
                $percentage = $total > 0 ? round(($count / $total) * 100) : 0;
                $items[] = [
                    'nama' => $nama,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
                $i++;
            } else {
                $lainnyaCount += $count;
            }
        }

        if ($lainnyaCount > 0) {
            $percentage = $total > 0 ? round(($lainnyaCount / $total) * 100) : 0;
            $items[] = [
                'nama' => 'Lainnya',
                'count' => $lainnyaCount,
                'percentage' => $percentage,
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    private function findAncestorNameByTingkat(Wilayah $w, string $targetTingkat, $wilayahMap): ?string
    {
        if ($w->tingkat === $targetTingkat) {
            return $w->nama;
        }

        // Periksa path hierarki misal /1/2/3/4
        $ids = array_filter(explode('/', $w->path ?? ''));
        foreach ($ids as $id) {
            if (isset($wilayahMap[$id]) && $wilayahMap[$id]->tingkat === $targetTingkat) {
                return $wilayahMap[$id]->nama;
            }
        }

        return null;
    }

    private function emptyResponse(string $message = ''): array
    {
        return [
            'success' => true,
            'message' => $message,
            'layers' => [
                'menunggu_disurvei' => [],
                'sedang_disurvei' => [],
                'sedang_renovasi' => [],
                'selesai' => [],
            ],
            'counts' => [
                'menunggu_disurvei' => 0,
                'sedang_disurvei' => 0,
                'sedang_renovasi' => 0,
                'selesai' => 0,
                'total' => 0,
            ],
            'area_distribution' => [
                'items' => [],
                'total' => 0,
            ],
            'wilayah_scope' => '-',
        ];
    }
}
