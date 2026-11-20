<?php

namespace App\Modules\Rumah\Http\Controllers;

use App\Models\AuditLog;
use App\Models\RumahWarga;
use App\Models\User;
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

        // Policy: Surveyor hanya lihat wilayahnya
        $user = auth()->user();
        if ($user->isSurveyor() && $user->wilayah) {
            $wilayahPath = $user->wilayah->path;
            $query->whereHas('wilayah', function ($q) use ($wilayahPath) {
                $q->where('path', $wilayahPath)
                  ->orWhere('path', 'like', $wilayahPath . '/%');
            });
        }

        // FIX: Filter berdasarkan nama lokasi (kelurahan/kecamatan/dll)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_registrasi', 'like', "%{$search}%")
                  ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                  ->orWhereHas('wilayah', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
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
        // Cascading dropdown: load kabupaten (514 baris, cepat & ringan)
        // Kecamatan, Kelurahan, RT dimuat via AJAX /wilayah/{id}/children
        $kabupatenList = Wilayah::where('tingkat', 'kabupaten')
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        // Kecamatan tanpa parent (misal dibuat langsung di environment test/seeder khusus)
        $kecamatanDirect = Wilayah::where('tingkat', 'kecamatan')
            ->whereNull('parent_id')
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        // FIX: Ambil daftar surveyor untuk dropdown (Admin yang input, pilih surveyor)
        $surveyorList = User::whereHas('role', function ($q) {
            $q->where('kode', 'surveyor');
        })->where('is_aktif', true)->orderBy('nama')->get();

        // Jika ada old wilayah_id (setelah validasi gagal), preload path untuk re-select
        $selectedWilayah = null;
        if ($oldWilayahId = old('wilayah_id')) {
            $selectedWilayah = Wilayah::with(['parent.parent.parent'])->find($oldWilayahId);
        }

        return view('rumah.create', compact('kabupatenList', 'kecamatanDirect', 'surveyorList', 'selectedWilayah'));
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
            'wilayah_id'            => $isDraft ? 'nullable' : 'required_without:kelurahan_id|nullable|exists:wilayah,id',
            'kelurahan_id'          => 'nullable|exists:wilayah,id',
            'rw_text'               => 'nullable|string|max:10',
            'rt_text'               => 'nullable|string|max:10',
            // FIX: surveyor_id wajib diisi oleh Admin
            'surveyor_id'           => 'required|exists:users,id',
        ];

        $validated = $request->validate($rules, [
            'nik.size' => 'NIK harus 16 digit.',
            'no_kk.size' => 'No. KK harus 16 digit.',
            'wilayah_id.required_without' => 'Wilayah kelurahan/RT wajib dipilih.',
        ]);

        // Resolusi RT/RW hierarki sesuai Kemendagri
        if ($request->filled('kelurahan_id') && $request->filled('rt_text') && $request->filled('rw_text')) {
            $rt = $this->resolveOrCreateRtWilayah(
                (int) $request->input('kelurahan_id'),
                (string) $request->input('rt_text'),
                (string) $request->input('rw_text')
            );
            $validated['wilayah_id'] = $rt->id;
        } elseif ($request->filled('kelurahan_id') && empty($validated['wilayah_id'])) {
            $validated['wilayah_id'] = (int) $request->input('kelurahan_id');
        }
        unset($validated['kelurahan_id'], $validated['rw_text'], $validated['rt_text']);

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
        // FIX: surveyor_id diambil dari input Admin, bukan auth()->id()
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

    public function resolveOrCreateRtWilayah(int $parentKelurahanId, string $rtText, string $rwText): Wilayah
    {
        $parent = Wilayah::findOrFail($parentKelurahanId);

        $rwText = trim($rwText);
        $rtText = trim($rtText);

        $rwNama = 'RW ' . mb_strtoupper($rwText);
        $rw = Wilayah::firstOrCreate(
            ['parent_id' => $parent->id, 'nama' => $rwNama],
            [
                'kode'     => 'RW-' . $parent->id . '-' . mb_strtoupper($rwText),
                'tingkat'  => Wilayah::TINGKAT_RW,
                'path'     => null,
                'is_aktif' => true,
            ],
        );
        if (blank($rw->path)) {
            $rw->forceFill(['path' => ($parent->path ?: '/' . $parent->id) . '/' . $rw->id])->save();
        }

        $rtNama = 'RT ' . mb_strtoupper($rtText);
        $rt = Wilayah::firstOrCreate(
            ['parent_id' => $rw->id, 'nama' => $rtNama],
            [
                'kode'     => 'RT-' . $rw->id . '-' . mb_strtoupper($rtText),
                'tingkat'  => Wilayah::TINGKAT_RT,
                'path'     => null,
                'is_aktif' => true,
            ],
        );
        if (blank($rt->path)) {
            $rt->forceFill(['path' => ($rw->path ?: '/' . $rw->id) . '/' . $rt->id])->save();
        }

        return $rt->fresh();
    }

    public function show(RumahWarga $rumah)
    {
        $rumah->load(['wilayah', 'surveyor', 'survei.komponenSurvei.komponen', 'survei.komponenSurvei.opsiKondisi', 'survei.komponenSurvei.foto', 'riwayatStatus']);
        return view('rumah.show', compact('rumah'));
    }

    public function edit(RumahWarga $rumah)
    {
        $kabupatenList = Wilayah::where('tingkat', 'kabupaten')
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        $kecamatanDirect = Wilayah::where('tingkat', 'kecamatan')
            ->whereNull('parent_id')
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        $surveyorList = User::whereHas('role', function ($q) {
            $q->where('kode', 'surveyor');
        })->where('is_aktif', true)->orderBy('nama')->get();

        $nikEncryptor = app(NikEncryptor::class);
        $nikPlain = $rumah->nik_enc ? $nikEncryptor->decryptNik($rumah->nik_enc) : '';
        $noKkPlain = $rumah->no_kk_enc ? $nikEncryptor->decryptNoKk($rumah->no_kk_enc) : '';

        // Preload parent chain untuk re-select cascading dropdown saat edit
        $selectedWilayah = $rumah->wilayah_id
            ? Wilayah::with(['parent.parent.parent'])->find($rumah->wilayah_id)
            : null;

        return view('rumah.edit', compact('rumah', 'kabupatenList', 'kecamatanDirect', 'surveyorList', 'nikPlain', 'noKkPlain', 'selectedWilayah'));
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
            'wilayah_id'            => $isDraft ? 'nullable' : 'required_without:kelurahan_id|nullable|exists:wilayah,id',
            'kelurahan_id'          => 'nullable|exists:wilayah,id',
            'rw_text'               => 'nullable|string|max:10',
            'rt_text'               => 'nullable|string|max:10',
            'surveyor_id'           => 'required|exists:users,id',
        ];

        $validated = $request->validate($rules);

        // Resolusi RT/RW hierarki sesuai Kemendagri
        if ($request->filled('kelurahan_id') && $request->filled('rt_text') && $request->filled('rw_text')) {
            $rt = $this->resolveOrCreateRtWilayah(
                (int) $request->input('kelurahan_id'),
                (string) $request->input('rt_text'),
                (string) $request->input('rw_text')
            );
            $validated['wilayah_id'] = $rt->id;
        } elseif ($request->filled('kelurahan_id') && empty($validated['wilayah_id'])) {
            $validated['wilayah_id'] = (int) $request->input('kelurahan_id');
        }
        unset($validated['kelurahan_id'], $validated['rw_text'], $validated['rt_text']);

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

        $dataLama = $rumah->only(['nama_kepala_keluarga', 'wilayah_id', 'surveyor_id', 'status']);
        $rumah->update($validated);
        $dataBaru = $rumah->only(['nama_kepala_keluarga', 'wilayah_id', 'surveyor_id', 'status']);

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