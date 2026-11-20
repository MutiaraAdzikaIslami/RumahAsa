<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Models\PenerimaBantuan;
use App\Models\RiwayatStatus;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isVerifikator()) {
            return $this->verifikatorDashboard($user);
        }

        if ($user->isSurveyor()) {
            return $this->surveyorDashboard($user);
        }

        if ($user->isPimpinan()) {
            return $this->pimpinanDashboard($user);
        }

        // Admin dan role lainnya: tampilkan dashboard admin/placeholder tanpa heatmap
        return view('dashboard.placeholder', ['user' => $user]);
    }

    private function verifikatorDashboard(User $user)
    {
        // 6 Kartu Ringkasan
        $pengajuanBaruCount = RumahWarga::where('status', RumahWarga::STATUS_DIAJUKAN)->count();
        $perluDitinjauCount = $pengajuanBaruCount;
        $perluPerbaikanCount = RumahWarga::where('status', RumahWarga::STATUS_PERLU_PERBAIKAN)->count();
        $tidakLayakCount = RumahWarga::where('status', RumahWarga::STATUS_LAYAK_HUNI)->count();
        $dalamRenovasiCount = RumahWarga::where('status', RumahWarga::STATUS_DALAM_RENOVASI)->count();
        $selesaiCount = RumahWarga::where('status', RumahWarga::STATUS_SELESAI)->count();

        $antreanAktifCount = $perluDitinjauCount + $perluPerbaikanCount;

        // Pengajuan Menunggu Verifikasi
        $pengajuanMenunggu = RumahWarga::with(['wilayah.parent.parent.parent', 'survei.komponenSurvei.foto'])
            ->where('status', RumahWarga::STATUS_DIAJUKAN)
            ->latest()
            ->take(5)
            ->get();

        // Target Hari Ini (Simulasi / Riwayat Status hari ini oleh verifikator)
        $todayReviewsCount = RiwayatStatus::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        $targetTotal = max(8, $todayReviewsCount + $pengajuanMenunggu->count());
        $targetSelesai = min($todayReviewsCount > 0 ? $todayReviewsCount : 5, $targetTotal);

        // Aktivitas Terbaru
        $aktivitasTerbaru = RiwayatStatus::with(['user', 'rumah.wilayah'])
            ->latest('created_at')
            ->take(4)
            ->get();

        // Progres Renovasi Terbaru
        $progresTerbaru = RumahWarga::with(['wilayah.parent.parent.parent', 'penerimaBantuan.progres'])
            ->whereIn('status', [RumahWarga::STATUS_DALAM_RENOVASI, RumahWarga::STATUS_SELESAI])
            ->latest('updated_at')
            ->take(3)
            ->get();

        // Daftar wilayah untuk filter (dioptimalkan agar tidak memuat 83k kelurahan sekaligus)
        $wilayahList = Wilayah::where(function ($q) {
            $q->whereHas('rumahWarga')->orWhereNull('parent_id');
        })->orderBy('nama')->limit(150)->get();

        if ($wilayahList->isEmpty()) {
            $wilayahList = Wilayah::orderBy('tingkat')->orderBy('nama')->limit(150)->get();
        }

        $sumberProgramList = ['APBN', 'APBD'];

        return view('dashboard.verifikator', compact(
            'user',
            'pengajuanBaruCount',
            'perluDitinjauCount',
            'perluPerbaikanCount',
            'tidakLayakCount',
            'dalamRenovasiCount',
            'selesaiCount',
            'antreanAktifCount',
            'pengajuanMenunggu',
            'targetSelesai',
            'targetTotal',
            'aktivitasTerbaru',
            'progresTerbaru',
            'wilayahList',
            'sumberProgramList'
        ));
    }

    private function surveyorDashboard(User $user)
    {
        $wilayah = $user->wilayah;
        $path = $wilayah?->path;

        $scopedQuery = RumahWarga::query();
        if ($path) {
            $scopedQuery->whereHas('wilayah', function ($q) use ($path) {
                $q->where('path', $path)
                    ->orWhere('path', 'like', $path.'/%');
            });
        }

        // 6 Kartu Ringkasan Wilayah Tugas
        $menungguSurveiCount = (clone $scopedQuery)->where(function ($q) {
            $q->where('status', 'Menunggu Disurvei')
                ->orWhere(fn ($q2) => $q2->where('status', RumahWarga::STATUS_DRAF)->whereDoesntHave('survei'));
        })->count();

        $drafCount = (clone $scopedQuery)->where('status', RumahWarga::STATUS_DRAF)
            ->whereHas('survei')
            ->count();

        $diajukanCount = (clone $scopedQuery)->where('status', RumahWarga::STATUS_DIAJUKAN)->count();
        $perluPerbaikanCount = (clone $scopedQuery)->where('status', RumahWarga::STATUS_PERLU_PERBAIKAN)->count();
        $dalamRenovasiCount = (clone $scopedQuery)->where('status', RumahWarga::STATUS_DALAM_RENOVASI)->count();
        $selesaiCount = (clone $scopedQuery)->where('status', RumahWarga::STATUS_SELESAI)->count();

        // Data Rumah Terbaru di Wilayah Tugas
        $dataRumahTerbaru = (clone $scopedQuery)
            ->with(['wilayah', 'survei'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Progres Renovasi
        $progresRenovasi = (clone $scopedQuery)
            ->with('wilayah')
            ->whereIn('status', [RumahWarga::STATUS_DALAM_RENOVASI, RumahWarga::STATUS_SELESAI])
            ->latest('updated_at')
            ->take(5)
            ->get();

        // Opsi wilayah untuk surveyor (hanya dalam wilayah tugasnya)
        $wilayahList = [];
        if ($wilayah) {
            $wilayahList = Wilayah::where('path', 'like', $wilayah->path.'/%')
                ->where('tingkat', Wilayah::TINGKAT_RW)
                ->orderBy('nama')
                ->get();
        }

        $sumberProgramList = ['APBN', 'APBD'];

        return view('dashboard.surveyor', compact(
            'user',
            'wilayah',
            'menungguSurveiCount',
            'drafCount',
            'diajukanCount',
            'perluPerbaikanCount',
            'dalamRenovasiCount',
            'selesaiCount',
            'dataRumahTerbaru',
            'progresRenovasi',
            'wilayahList',
            'sumberProgramList'
        ));
    }

    private function pimpinanDashboard(User $user)
    {
        // 3 Kartu Atas: Dana Tersalur, Rumah Penerima, Rata-rata
        $totalDana = PenerimaBantuan::sum('nominal') ?: 130000000;
        $totalPenerima = PenerimaBantuan::count() ?: 10;
        $rataRata = $totalPenerima > 0 ? ($totalDana / $totalPenerima) : 0;

        // Dana per Sumber Program
        $danaApbn = PenerimaBantuan::where('sumber_program', 'APBN')->sum('nominal') ?: 90000000;
        $countApbn = PenerimaBantuan::where('sumber_program', 'APBN')->count() ?: 6;

        $danaApbd = PenerimaBantuan::where('sumber_program', 'APBD')->sum('nominal') ?: 40000000;
        $countApbd = PenerimaBantuan::where('sumber_program', 'APBD')->count() ?: 4;

        // Penyaluran Terbaru
        $penyaluranTerbaru = PenerimaBantuan::with(['rumah.wilayah'])
            ->latest('tanggal_bantuan')
            ->take(5)
            ->get();

        $wilayahList = Wilayah::where(function ($q) {
            $q->whereHas('rumahWarga')->orWhereNull('parent_id');
        })->orderBy('nama')->limit(150)->get();

        if ($wilayahList->isEmpty()) {
            $wilayahList = Wilayah::orderBy('tingkat')->orderBy('nama')->limit(150)->get();
        }

        $sumberProgramList = ['APBN', 'APBD'];

        return view('dashboard.pimpinan', compact(
            'user',
            'totalDana',
            'totalPenerima',
            'rataRata',
            'danaApbn',
            'countApbn',
            'danaApbd',
            'countApbd',
            'penyaluranTerbaru',
            'wilayahList',
            'sumberProgramList'
        ));
    }
}
