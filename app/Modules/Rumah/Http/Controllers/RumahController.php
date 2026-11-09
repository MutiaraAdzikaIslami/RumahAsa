<?php

namespace App\Modules\Rumah\Http\Controllers;

use App\Models\AuditLog;
use App\Models\RumahWarga;
use App\Models\Wilayah;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Core\Services\NikEncryptor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RumahController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request)
    {
        $query = RumahWarga::with(['wilayah', 'surveyor', 'survei']);

        $user = auth()->user();
        if ($user->isSurveyor() && $user->wilayah) {
            $wilayahPath = $user->wilayah->path;
            $query->whereHas('wilayah', function ($q) use ($wilayahPath) {
                $q->where('path', $wilayahPath)
                  ->orWhere('path', 'like', $wilayahPath . '/%');
            });
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
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

        $rumah = $query->latest()->paginate(15);
        $wilayahList = Wilayah::orderBy('tingkat')->orderBy('nama')->get();

        return view('rumah.index', compact('rumah', 'wilayahList'));
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->isSurveyor() && $user->wilayah) {
            $wilayahPath = $user->wilayah->path;
            $rtList = Wilayah::where('tingkat', 'rt')
                ->where(function ($q) use ($wilayahPath) {
                    $q->where('path', $wilayahPath)
                      ->orWhere('path', 'like', $wilayahPath . '/%');
                })
                ->orderBy('nama')
                ->get();
        } else {
            $rtList = Wilayah::where('tingkat', 'rt')->orderBy('nama')->get();
        }

        return view('rumah.create', compact('rtList'));
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('action') === 'draft';

        $rules = [
            'nama_kepala_keluarga' => $isDraft ? 'nullable|string|max:150' : 'required|string|max:150',
            'nik'                   => $isDraft ? 'nullable|string|size:16' : 'required|string|size:16',
            'no_kk'                 => $isDraft ? 'nullable|string|size:16' : 'required|string|size:16',
            'jumlah_anggota'        => 'nullable|integer|min:1',
            'luas_rumah'            => 'nullable|numeric|min:0',
            'pekerjaan'             => 'nullable|string|max:100',
            'penghasilan'           => 'nullable|integer|min:0',
            'status_kepemilikan'    => 'nullable|in:milik_sendiri,sewa,menumpang,lainnya',
            'alamat'                => $isDraft ? 'nullable|string' : 'required|string',
            'latitude'              => 'nullable|numeric',
            'longitude'             => 'nullable|numeric',
            'wilayah_id'            => $isDraft ? 'nullable|exists:wilayah,id' : 'required|exists:wilayah,id',
        ];

        $validated = $request->validate($rules, [
            'nik.size' => 'NIK harus 16 digit.',
            'no_kk.size' => 'No. KK harus 16 digit.',
        ]);

        $nikEncryptor = app(NikEncryptor::class);
        if (!empty($validated['nik'])) {
            $nikHash = $nikEncryptor->hashNik($validated['nik']);
            $exists = RumahWarga::where('nik_hash', $nikHash)->exists();
            if ($exists) {
                return back()->withErrors(['nik' => 'NIK sudah terdaftar di sistem.'])->withInput();
            }
            $validated['nik_enc'] = $nikEncryptor->encryptNik($validated['nik']);
            $validated['nik_hash'] = $nikHash;
        }
        if (!empty($validated['no_kk'])) {
            $validated['no_kk_enc'] = $nikEncryptor->encryptNoKk($validated['no_kk']);
            $validated['no_kk_hash'] = $nikEncryptor->hashNoKk($validated['no_kk']);
        }
        unset($validated['nik'], $validated['no_kk']);

        $count = RumahWarga::withTrashed()->count() + 1;
        $validated['kode_registrasi'] = 'RUMAH-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        $validated['surveyor_id'] = auth()->id();
        $validated['status'] = RumahWarga::STATUS_DRAF;

        $rumah = RumahWarga::create($validated);

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['RUMAH_CREATE'],
            entitas:   'rumah_warga',
            entitasId: $rumah->id,
            dataBaru:  ['kode_registrasi' => $rumah->kode_registrasi],
        );

        return redirect()->route('rumah.show', $rumah)
            ->with('success', $isDraft ? 'Draf berhasil disimpan.' : 'Data rumah berhasil disimpan.');
    }

    public function show(RumahWarga $rumah)
    {
        $rumah->load(['wilayah', 'surveyor', 'survei.komponenSurvei.komponen', 'survei.komponenSurvei.opsiKondisi', 'survei.komponenSurvei.foto', 'riwayatStatus']);
        return view('rumah.show', compact('rumah'));
    }

    public function edit(RumahWarga $rumah)
    {
        $user = auth()->user();
        if ($user->isSurveyor() && $user->wilayah) {
            $wilayahPath = $user->wilayah->path;
            $rtList = Wilayah::where('tingkat', 'rt')
                ->where(function ($q) use ($wilayahPath) {
                    $q->where('path', $wilayahPath)
                      ->orWhere('path', 'like', $wilayahPath . '/%');
                })
                ->orderBy('nama')
                ->get();
        } else {
            $rtList = Wilayah::where('tingkat', 'rt')->orderBy('nama')->get();
        }

        $nikEncryptor = app(NikEncryptor::class);
        $nikPlain = $rumah->nik_enc ? $nikEncryptor->decryptNik($rumah->nik_enc) : '';
        $noKkPlain = $rumah->no_kk_enc ? $nikEncryptor->decryptNoKk($rumah->no_kk_enc) : '';

        return view('rumah.edit', compact('rumah', 'rtList', 'nikPlain', 'noKkPlain'));
    }

    public function update(Request $request, RumahWarga $rumah)
    {
        $isDraft = $request->input('action') === 'draft';

        $rules = [
            'nama_kepala_keluarga' => $isDraft ? 'nullable|string|max:150' : 'required|string|max:150',
            'nik'                   => $isDraft ? 'nullable|string|size:16' : 'required|string|size:16',
            'no_kk'                 => $isDraft ? 'nullable|string|size:16' : 'required|string|size:16',
            'jumlah_anggota'        => 'nullable|integer|min:1',
            'luas_rumah'            => 'nullable|numeric|min:0',
            'pekerjaan'             => 'nullable|string|max:100',
            'penghasilan'           => 'nullable|integer|min:0',
            'status_kepemilikan'    => 'nullable|in:milik_sendiri,sewa,menumpang,lainnya',
            'alamat'                => $isDraft ? 'nullable|string' : 'required|string',
            'latitude'              => 'nullable|numeric',
            'longitude'             => 'nullable|numeric',
            'wilayah_id'            => $isDraft ? 'nullable|exists:wilayah,id' : 'required|exists:wilayah,id',
        ];

        $validated = $request->validate($rules);

        $nikEncryptor = app(NikEncryptor::class);
        if (!empty($validated['nik'])) {
            $nikHash = $nikEncryptor->hashNik($validated['nik']);
            $exists = RumahWarga::where('nik_hash', $nikHash)
                ->where('id', '!=', $rumah->id)
                ->exists();
            if ($exists) {
                return back()->withErrors(['nik' => 'NIK sudah terdaftar di rumah lain.'])->withInput();
            }
            $validated['nik_enc'] = $nikEncryptor->encryptNik($validated['nik']);
            $validated['nik_hash'] = $nikHash;
        }
        if (!empty($validated['no_kk'])) {
            $validated['no_kk_enc'] = $nikEncryptor->encryptNoKk($validated['no_kk']);
            $validated['no_kk_hash'] = $nikEncryptor->hashNoKk($validated['no_kk']);
        }
        unset($validated['nik'], $validated['no_kk']);

        $dataLama = $rumah->only(['nama_kepala_keluarga', 'wilayah_id', 'status']);
        $rumah->update($validated);
        $dataBaru = $rumah->only(['nama_kepala_keluarga', 'wilayah_id', 'status']);

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['RUMAH_UPDATE'],
            entitas:   'rumah_warga',
            entitasId: $rumah->id,
            dataBaru:  $dataBaru,
            dataLama:  $dataLama,
        );

        return redirect()->route('rumah.show', $rumah)
            ->with('success', 'Data rumah berhasil diperbarui.');
    }
}