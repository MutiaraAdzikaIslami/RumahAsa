<?php

namespace App\Livewire;

use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Rumah\Services\RumahService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RumahForm extends Component
{
    #[Locked]
    public ?int $rumahId = null;

    public string $namaKepalaKeluarga = '';
    public string $nik = '';
    public string $noKk = '';
    public ?int $jumlahAnggota = null;
    public ?string $luasRumah = null;
    public string $pekerjaan = '';
    public ?string $penghasilan = null;
    public ?string $statusKepemilikan = null;
    public string $alamat = '';
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?int $surveyorId = null;

    public ?int $kabupatenId = null;
    public ?int $kecamatanId = null;
    public ?int $kelurahanId = null;

    public string $rwText = '';
    public string $rtText = '';

    public function mount(?int $rumahId = null): void
    {
        if ($rumahId === null) {
            return;
        }

        $rumah = RumahWarga::findOrFail($rumahId);

        $this->rumahId            = $rumah->id;
        $this->namaKepalaKeluarga = (string) $rumah->nama_kepala_keluarga;
        $this->jumlahAnggota      = $rumah->jumlah_anggota;
        $this->luasRumah          = $rumah->luas_rumah !== null ? (string) $rumah->luas_rumah : null;
        $this->pekerjaan          = (string) ($rumah->pekerjaan ?? '');
        // Format penghasilan untuk tampilan edit: 2500000 -> "2.500.000"
        $this->penghasilan        = $rumah->penghasilan !== null
            ? number_format((int) $rumah->penghasilan, 0, ',', '.')
            : null;
        $this->statusKepemilikan  = $rumah->status_kepemilikan;
        $this->alamat             = (string) $rumah->alamat;
        $this->latitude           = $rumah->latitude !== null ? (string) $rumah->latitude : null;
        $this->longitude          = $rumah->longitude !== null ? (string) $rumah->longitude : null;
        $this->surveyorId         = $rumah->surveyor_id;

        $rt = $rumah->wilayah;
        if ($rt && $rt->tingkat === Wilayah::TINGKAT_RT) {
            $this->rtText = trim((string) preg_replace('/^RT\s*/i', '', $rt->nama));

            $rw = $rt->parent;
            if ($rw) {
                $this->rwText = trim((string) preg_replace('/^RW\s*/i', '', $rw->nama));

                $kel = $rw->parent;
                if ($kel) {
                    $this->kelurahanId = $kel->id;

                    $kec = $kel->parent;
                    if ($kec) {
                        $this->kecamatanId = $kec->id;

                        $kab = $kec->parent;
                        if ($kab) {
                            $this->kabupatenId = $kab->id;
                        }
                    }
                }
            }
        }
    }

    protected function rules(): array
    {
        $isEdit = $this->rumahId !== null;
        $nikDigits = ['string', 'size:16', 'regex:/^[0-9]{16}$/'];

        return [
            'namaKepalaKeluarga' => ['required', 'string', 'max:150'],
            'nik'                => $isEdit
                ? array_merge(['nullable'], $nikDigits)
                : array_merge(['required'], $nikDigits),
            'noKk'               => $isEdit
                ? array_merge(['nullable'], $nikDigits)
                : array_merge(['required'], $nikDigits),
            'jumlahAnggota'      => ['required', 'integer', 'min:1', 'max:99'],
            'luasRumah'          => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'pekerjaan'          => ['nullable', 'string', 'max:100'],
            'penghasilan'        => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'statusKepemilikan'  => ['required', Rule::in(['milik_sendiri', 'sewa', 'menumpang', 'lainnya'])],
            'kabupatenId'  => ['required', 'integer', Rule::exists('wilayah', 'id')->where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)],
            'kecamatanId'  => ['required', 'integer', Rule::exists('wilayah', 'id')->where('tingkat', Wilayah::TINGKAT_KECAMATAN)],
            'kelurahanId'  => ['required', 'integer', Rule::exists('wilayah', 'id')->where('tingkat', Wilayah::TINGKAT_KELURAHAN)],
            'rwText'       => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\/\-\.]+$/'],
            'rtText'       => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\/\-\.]+$/'],
            'alamat'     => ['required', 'string', 'max:500'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'surveyorId' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'namaKepalaKeluarga.required' => 'Nama kepala keluarga wajib diisi.',
            'nik.required'   => 'NIK wajib diisi.',
            'nik.size'       => 'NIK harus 16 digit.',
            'nik.regex'      => 'NIK hanya boleh angka.',
            'noKk.required'  => 'Nomor Kartu Keluarga wajib diisi.',
            'noKk.size'      => 'Nomor KK harus 16 digit.',
            'noKk.regex'     => 'Nomor KK hanya boleh angka.',
            'jumlahAnggota.required' => 'Jumlah anggota keluarga wajib diisi.',
            'penghasilan.integer'    => 'Penghasilan harus berupa angka.',
            'statusKepemilikan.required' => 'Status kepemilikan wajib dipilih.',
            'kabupatenId.required' => 'Kabupaten/Kota wajib dipilih.',
            'kecamatanId.required' => 'Kecamatan wajib dipilih.',
            'kelurahanId.required' => 'Kelurahan/Desa wajib dipilih.',
            'rwText.required' => 'RW wajib diisi.',
            'rwText.regex'    => 'RW hanya boleh huruf, angka, slash, dash, titik.',
            'rtText.required' => 'RT wajib diisi.',
            'rtText.regex'    => 'RT hanya boleh huruf, angka, slash, dash, titik.',
            'alamat.required' => 'Alamat wajib diisi.',
            'surveyorId.required' => 'Surveyor wajib ditugaskan.',
        ];
    }

    public function updated(string $propertyName): void
    {
        if ($propertyName === 'kabupatenId') {
            $this->kecamatanId = null;
            $this->kelurahanId = null;
        } elseif ($propertyName === 'kecamatanId') {
            $this->kelurahanId = null;
        }

        $validatable = [
            'namaKepalaKeluarga', 'nik', 'noKk', 'jumlahAnggota', 'luasRumah',
            'pekerjaan', 'penghasilan', 'statusKepemilikan', 'kabupatenId',
            'kecamatanId', 'kelurahanId', 'rwText', 'rtText', 'alamat',
            'latitude', 'longitude',
        ];

        if (in_array($propertyName, $validatable, true)) {
            $this->validateOnly($propertyName);
        }
    }

    /**
     * Strip non-digit (titik pemisah ribuan) sebelum validasi.
     * Dipanggil otomatis Livewire saat property di-update.
     */
    public function updatedPenghasilan(?string $value): void
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        $this->penghasilan = ($clean !== '') ? $clean : null;
    }

    public function save()
    {
        // Safety net: strip pemisah ribuan ("2.500.000" -> "2500000") sebelum validate
        if ($this->penghasilan !== null && $this->penghasilan !== '') {
            $clean = preg_replace('/[^0-9]/', '', (string) $this->penghasilan);
            $this->penghasilan = ($clean !== '') ? $clean : null;
        }

        $surveyorIds = User::whereHas('role', fn ($q) => $q->where('kode', 'surveyor'))
            ->aktif()
            ->pluck('id')
            ->all();

        if ($this->surveyorId === null || !in_array((int) $this->surveyorId, $surveyorIds, true)) {
            $this->addError('surveyorId', 'Surveyor tidak valid.');
            return;
        }

        $validated = $this->validate();

        $data = [
            'nama_kepala_keluarga' => $validated['namaKepalaKeluarga'],
            'nik'                  => $validated['nik'] ?? null,
            'no_kk'                => $validated['noKk'] ?? null,
            'jumlah_anggota'       => (int) $validated['jumlahAnggota'],
            'luas_rumah'           => $validated['luasRumah'] !== null ? (float) $validated['luasRumah'] : null,
            'pekerjaan'            => $validated['pekerjaan'] !== '' ? $validated['pekerjaan'] : null,
            'penghasilan'          => $validated['penghasilan'] !== null ? (int) $validated['penghasilan'] : null,
            'status_kepemilikan'   => $validated['statusKepemilikan'],
            'wilayah_id'           => (int) $validated['kelurahanId'],
            'rw_text'              => $validated['rwText'],
            'rt_text'              => $validated['rtText'],
            'alamat'               => $validated['alamat'],
            'latitude'             => $validated['latitude'] !== null ? (float) $validated['latitude'] : null,
            'longitude'            => $validated['longitude'] !== null ? (float) $validated['longitude'] : null,
            'surveyor_id'          => (int) $validated['surveyorId'],
        ];

        $rumahService = app(RumahService::class);

        if ($this->rumahId) {
            $rumah = RumahWarga::findOrFail($this->rumahId);
            $rumahService->updateRumah($rumah, $data);
            session()->flash('success', "Data rumah {$rumah->kode_registrasi} berhasil diperbarui.");
            return $this->redirect(route('rumah.show', $rumah), navigate: true);
        }

        $rumah = $rumahService->createRumah($data);
        session()->flash('success', "Data rumah {$rumah->kode_registrasi} berhasil disimpan. Status: Menunggu Survei. Surveyor yang ditugaskan akan melihat rumah ini di daftar tugasnya.");
        return $this->redirect(route('rumah.show', $rumah), navigate: true);
    }

    public function render()
    {
        $surveyors = User::whereHas('role', fn ($q) => $q->where('kode', 'surveyor'))
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'username']);

        $kabupatenList = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $kecamatanList = $this->kabupatenId
            ? Wilayah::where('tingkat', Wilayah::TINGKAT_KECAMATAN)
                ->where('parent_id', $this->kabupatenId)
                ->orderBy('nama')
                ->get(['id', 'nama'])
            : collect();

        $kelurahanList = $this->kecamatanId
            ? Wilayah::where('tingkat', Wilayah::TINGKAT_KELURAHAN)
                ->where('parent_id', $this->kecamatanId)
                ->orderBy('nama')
                ->get(['id', 'nama'])
            : collect();

        return view('livewire.rumah-form', compact(
            'surveyors',
            'kabupatenList',
            'kecamatanList',
            'kelurahanList'
        ));
    }
}
