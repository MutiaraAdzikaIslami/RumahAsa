<?php

namespace App\Modules\Bantuan\Http\Controllers;

use App\Models\RumahWarga;
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
        $query = RumahWarga::with(['wilayah', 'penerimaBantuan.progres'])
            ->whereIn('status', self::STATUS_BANTUAN);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%");
            });
        }

        $status = $request->query('status');
        if (in_array($status, self::STATUS_BANTUAN, true)) {
            $query->where('status', $status);
        }

        $rumah = $query->latest()->paginate(15)->withQueryString();

        return view('bantuan.index', compact('rumah'));
    }
}
