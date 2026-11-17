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
        $query = RumahWarga::with(['wilayah', 'penerimaBantuan.progres', 'riwayatStatus'])
            ->whereIn('status', self::STATUS_BANTUAN);

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

        if ($wilayahId = $request->query('wilayah_id')) {
            $wilayah = Wilayah::find($wilayahId);
            if ($wilayah) {
                $query->whereHas('wilayah', function ($q) use ($wilayah) {
                    $q->where('id', $wilayah->id)
                      ->orWhere('path', 'like', $wilayah->path . '/%');
                });
            }
        }

        $totalPenerima = RumahWarga::whereIn('status', self::STATUS_BANTUAN)->count();

        $rumah = $query->latest('updated_at')->paginate(10)->withQueryString();

        $wilayahList = Wilayah::whereIn('tingkat', [
            Wilayah::TINGKAT_KECAMATAN,
            Wilayah::TINGKAT_KELURAHAN,
        ])->orderBy('tingkat')->orderBy('nama')->get();

        if ($wilayahList->isEmpty()) {
            $wilayahList = Wilayah::orderBy('nama')->get();
        }

        return view('bantuan.index', compact('rumah', 'wilayahList', 'totalPenerima'));
    }
}
