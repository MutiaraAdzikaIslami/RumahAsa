<?php

namespace App\Modules\Rumah\Http\Requests;

use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\NikEncryptor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRumahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function isDraftSubmission(): bool
    {
        return $this->input('action') === 'draft';
    }

    public function rules(): array
    {
        $isDraft   = $this->isDraftSubmission();
        $isUpdate  = in_array($this->method(), ['PUT', 'PATCH'], true);
        $nikDigits = ['string', 'size:16', 'regex:/^[0-9]{16}$/'];

        return [
            'nama_kepala_keluarga' => [$isDraft ? 'nullable' : 'required', 'string', 'max:150'],
            'nik'                  => ($isDraft || $isUpdate)
                ? array_merge(['nullable'], $nikDigits)
                : array_merge(['required'], $nikDigits),
            'no_kk'                => ($isDraft || $isUpdate)
                ? array_merge(['nullable'], $nikDigits)
                : array_merge(['required'], $nikDigits),
            'jumlah_anggota'       => [$isDraft ? 'nullable' : 'required', 'integer', 'min:1', 'max:99'],
            'luas_rumah'           => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'pekerjaan'            => ['nullable', 'string', 'max:100'],
            'penghasilan'          => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'status_kepemilikan'   => [$isDraft ? 'nullable' : 'required', Rule::in(['milik_sendiri', 'sewa', 'menumpang', 'lainnya'])],
            'kabupaten_id'         => [
                $isDraft ? 'nullable' : 'required',
                'integer',
                Rule::exists(Wilayah::class, 'id')->where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA),
            ],
            'kecamatan_id'         => [
                $isDraft ? 'nullable' : 'required',
                'integer',
                Rule::exists(Wilayah::class, 'id')->where('tingkat', Wilayah::TINGKAT_KECAMATAN),
            ],
            'wilayah_id'           => [
                $isDraft ? 'nullable' : 'required',
                'integer',
                Rule::exists(Wilayah::class, 'id')->where('tingkat', Wilayah::TINGKAT_KELURAHAN),
            ],
            'rw_text'              => [$isDraft ? 'nullable' : 'required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\/\-\.]+$/'],
            'rt_text'              => [$isDraft ? 'nullable' : 'required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\/\-\.]+$/'],
            'alamat'               => [$isDraft ? 'nullable' : 'required', 'string', 'max:500'],
            'surveyor_id'          => [
                'required',
                'integer',
                Rule::exists(User::class, 'id'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validasi Hierarki Wilayah (Kabupaten -> Kecamatan -> Kelurahan)
            if ($this->filled('kabupaten_id') && $this->filled('kecamatan_id')) {
                $kecamatan = Wilayah::find($this->input('kecamatan_id'));
                if ($kecamatan && (int) $kecamatan->parent_id !== (int) $this->input('kabupaten_id')) {
                    $validator->errors()->add('kecamatan_id', 'Kecamatan yang dipilih tidak berada dalam Kabupaten/Kota yang dipilih.');
                }
            }

            if ($this->filled('kecamatan_id') && $this->filled('wilayah_id')) {
                $kelurahan = Wilayah::find($this->input('wilayah_id'));
                if ($kelurahan && (int) $kelurahan->parent_id !== (int) $this->input('kecamatan_id')) {
                    $validator->errors()->add('wilayah_id', 'Kelurahan/Desa yang dipilih tidak berada dalam Kecamatan yang dipilih.');
                }
            }

            // Validasi Surveyor dan Wilayah Tugasnya
            if ($this->filled('surveyor_id')) {
                $surveyor = User::with(['role', 'wilayah'])->find($this->input('surveyor_id'));
                if (!$surveyor || $surveyor->role?->kode !== 'surveyor') {
                    $validator->errors()->add('surveyor_id', 'User yang dipilih bukan seorang surveyor.');
                } elseif (!$surveyor->is_aktif) {
                    $validator->errors()->add('surveyor_id', "Surveyor {$surveyor->nama} sedang berstatus non-aktif.");
                } elseif (!$surveyor->wilayah) {
                    $validator->errors()->add(
                        'surveyor_id',
                        "Surveyor {$surveyor->nama} belum memiliki wilayah tugas. Silakan tetapkan wilayah tugas terlebih dahulu di Manajemen User."
                    );
                } elseif ($this->filled('wilayah_id')) {
                    $kelurahan = Wilayah::find($this->input('wilayah_id'));
                    if ($kelurahan) {
                        $wilayahSurveyor = $surveyor->wilayah;
                        $isMatch = false;

                        if ($kelurahan->id === $wilayahSurveyor->id) {
                            $isMatch = true;
                        } elseif (!empty($kelurahan->path) && !empty($wilayahSurveyor->path) && str_starts_with($kelurahan->path, rtrim($wilayahSurveyor->path, '/') . '/')) {
                            $isMatch = true;
                        } elseif ($kelurahan->parent_id === $wilayahSurveyor->id) {
                            $isMatch = true;
                        } else {
                            // Cek kesamaan lingkup Kecamatan
                            $kecamatanSurveyor = $this->resolveKecamatanModel($wilayahSurveyor);
                            $kecamatanRumah    = $this->resolveKecamatanModel($kelurahan);

                            if ($kecamatanSurveyor && $kecamatanRumah && $kecamatanSurveyor->id === $kecamatanRumah->id) {
                                $isMatch = true;
                            }
                        }

                        if (!$isMatch) {
                            $kecamatanSurveyor = $this->resolveKecamatanModel($wilayahSurveyor);
                            $kecamatanRumah    = $this->resolveKecamatanModel($kelurahan);
                            $namaKecSurveyor   = $kecamatanSurveyor ? "Kecamatan {$kecamatanSurveyor->nama}" : "{$wilayahSurveyor->nama} (" . ucfirst($wilayahSurveyor->tingkat) . ')';
                            $namaKecRumah      = $kecamatanRumah ? "Kecamatan {$kecamatanRumah->nama}" : "{$kelurahan->nama} (" . ucfirst($kelurahan->tingkat) . ')';

                            $validator->errors()->add(
                                'surveyor_id',
                                "Surveyor {$surveyor->nama} bertugas di {$namaKecSurveyor}, berbeda dengan wilayah rumah di {$namaKecRumah} ({$kelurahan->nama}). Harap pilih surveyor yang bertugas di {$namaKecRumah}."
                            );
                        }
                    }
                }
            }

            // Validasi Keunikan NIK (Blind Index HMAC)
            if ($this->filled('nik')) {
                if (!preg_match('/^[0-9]{16}$/', (string) $this->input('nik'))) {
                    $validator->errors()->add('nik', 'NIK harus berupa 16 digit angka tanpa spasi atau karakter lain.');
                } else {
                    $nikHash    = app(NikEncryptor::class)->hashNik((string) $this->input('nik'));
                    $routeRumah = $this->route('rumah');
                    $ignoreId   = $routeRumah instanceof RumahWarga
                        ? $routeRumah->id
                        : (is_numeric($routeRumah) ? (int) $routeRumah : null);

                    $exists = RumahWarga::withTrashed()
                        ->where('nik_hash', $nikHash)
                        ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add('nik', 'NIK sudah terdaftar pada data rumah lain di sistem.');
                    }
                }
            }

            // Validasi Format No KK jika diisi
            if ($this->filled('no_kk') && !preg_match('/^[0-9]{16}$/', (string) $this->input('no_kk'))) {
                $validator->errors()->add('no_kk', 'Nomor Kartu Keluarga harus berupa 16 digit angka tanpa spasi atau karakter lain.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nama_kepala_keluarga.required' => 'Nama kepala keluarga wajib diisi.',
            'nama_kepala_keluarga.max'      => 'Nama kepala keluarga maksimal 150 karakter.',
            'nik.required'                  => 'NIK wajib diisi 16 digit angka.',
            'nik.size'                      => 'NIK harus tepat 16 digit angka.',
            'nik.regex'                     => 'NIK hanya boleh berisi angka (16 digit).',
            'no_kk.required'                => 'Nomor Kartu Keluarga wajib diisi 16 digit angka.',
            'no_kk.size'                    => 'Nomor Kartu Keluarga harus tepat 16 digit angka.',
            'no_kk.regex'                   => 'Nomor Kartu Keluarga hanya boleh berisi angka (16 digit).',
            'jumlah_anggota.required'       => 'Jumlah anggota keluarga wajib diisi.',
            'jumlah_anggota.integer'        => 'Jumlah anggota keluarga harus berupa angka bulat.',
            'jumlah_anggota.min'            => 'Jumlah anggota keluarga minimal 1 orang.',
            'jumlah_anggota.max'            => 'Jumlah anggota keluarga maksimal 99 orang.',
            'luas_rumah.numeric'            => 'Luas rumah harus berupa angka desimal atau bulat (misal: 36.50).',
            'luas_rumah.min'                => 'Luas rumah tidak boleh bernilai negatif.',
            'luas_rumah.max'                => 'Luas rumah melebihi batas wajar maksimal.',
            'pekerjaan.max'                 => 'Pekerjaan maksimal 100 karakter.',
            'penghasilan.integer'           => 'Penghasilan harus berupa angka nominal bulat.',
            'penghasilan.min'               => 'Penghasilan tidak boleh bernilai negatif.',
            'penghasilan.max'               => 'Penghasilan melebihi batas nominal yang diizinkan.',
            'status_kepemilikan.required'   => 'Status kepemilikan rumah wajib dipilih.',
            'status_kepemilikan.in'         => 'Status kepemilikan tidak valid (pilih: Milik Sendiri, Sewa, Menumpang, atau Lainnya).',
            'kabupaten_id.required'         => 'Kabupaten/Kota wajib dipilih.',
            'kabupaten_id.exists'           => 'Kabupaten/Kota yang dipilih tidak terdaftar di sistem.',
            'kecamatan_id.required'         => 'Kecamatan wajib dipilih.',
            'kecamatan_id.exists'           => 'Kecamatan yang dipilih tidak terdaftar di sistem.',
            'wilayah_id.required'           => 'Kelurahan/Desa wajib dipilih.',
            'wilayah_id.exists'             => 'Kelurahan/Desa yang dipilih tidak valid. Pastikan memilih sampai tingkat Kelurahan/Desa.',
            'rw_text.required'              => 'Nomor RW wajib diisi.',
            'rw_text.max'                   => 'Nomor RW maksimal 10 karakter.',
            'rw_text.regex'                 => 'Nomor RW hanya boleh berisi angka atau huruf (misal: 01, 04, atau IV).',
            'rt_text.required'              => 'Nomor RT wajib diisi.',
            'rt_text.max'                   => 'Nomor RT maksimal 10 karakter.',
            'rt_text.regex'                 => 'Nomor RT hanya boleh berisi angka atau huruf (misal: 01, 02, atau 003).',
            'alamat.required'               => 'Alamat lengkap wajib diisi (nama jalan, nomor rumah, atau patokan).',
            'alamat.max'                    => 'Alamat maksimal 500 karakter.',
            'surveyor_id.required'          => 'Surveyor penanggung jawab wajib dipilih.',
            'surveyor_id.exists'            => 'Surveyor yang dipilih tidak terdaftar di sistem.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('penghasilan')) {
            $raw   = (string) $this->input('penghasilan', '');
            $clean = preg_replace('/[^0-9]/', '', $raw);
            $this->merge(['penghasilan' => $clean !== '' ? (int) $clean : null]);
        }

        if ($this->has('rt_text')) {
            $rt = trim((string) $this->input('rt_text'));
            $this->merge(['rt_text' => $rt !== '' ? $rt : null]);
        }
        if ($this->has('rw_text')) {
            $rw = trim((string) $this->input('rw_text'));
            $this->merge(['rw_text' => $rw !== '' ? $rw : null]);
        }
        if ($this->has('wilayah_id') && $this->input('wilayah_id') === '') {
            $this->merge(['wilayah_id' => null]);
        }
    }

    /**
     * Mengambil model Wilayah tingkat Kecamatan dari hierarki wilayah.
     */
    private function resolveKecamatanModel(Wilayah $wilayah): ?Wilayah
    {
        if ($wilayah->tingkat === Wilayah::TINGKAT_KECAMATAN) {
            return $wilayah;
        }

        if (!empty($wilayah->path)) {
            $segments = array_values(array_filter(explode('/', $wilayah->path)));
            // Format path: /provinsi/kabupaten/kecamatan/... (indeks 2 adalah id kecamatan)
            if (count($segments) >= 3) {
                return Wilayah::find((int) $segments[2]);
            }
        }

        $current = $wilayah;
        while ($current) {
            if ($current->tingkat === Wilayah::TINGKAT_KECAMATAN) {
                return $current;
            }
            $current = $current->parent;
        }

        return null;
    }
}