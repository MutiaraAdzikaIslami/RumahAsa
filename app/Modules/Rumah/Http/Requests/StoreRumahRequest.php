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
            'rw_text'              => [$isDraft ? 'nullable' : 'required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\/\-\.]+$/'],
            'rt_text'              => [$isDraft ? 'nullable' : 'required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\/\-\.]+$/'],
            'wilayah_id'           => [
                $isDraft ? 'nullable' : 'required',
                'integer',
                Rule::exists(Wilayah::class, 'id')->where('tingkat', Wilayah::TINGKAT_KELURAHAN),
            ],
            'alamat'               => [$isDraft ? 'nullable' : 'required', 'string', 'max:500'],
            'latitude'             => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'            => ['nullable', 'numeric', 'between:-180,180'],
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
            if ($this->filled('surveyor_id')) {
                $surveyor = User::with('role')->find($this->input('surveyor_id'));
                if ($surveyor && $surveyor->role?->kode !== 'surveyor') {
                    $validator->errors()->add('surveyor_id', 'Surveyor tidak valid.');
                }
            }

            if ($this->filled('nik') && preg_match('/^[0-9]{16}$/', (string) $this->input('nik'))) {
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
                    $validator->errors()->add('nik', 'NIK sudah terdaftar di sistem.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nama_kepala_keluarga.required' => 'Nama kepala keluarga wajib diisi.',
            'nik.required'                  => 'NIK wajib diisi.',
            'nik.size'                      => 'NIK harus 16 digit.',
            'nik.regex'                     => 'NIK hanya boleh angka.',
            'no_kk.required'                => 'Nomor Kartu Keluarga wajib diisi.',
            'no_kk.size'                    => 'Nomor KK harus 16 digit.',
            'no_kk.regex'                   => 'Nomor KK hanya boleh angka.',
            'jumlah_anggota.required'       => 'Jumlah anggota keluarga wajib diisi.',
            'penghasilan.integer'           => 'Penghasilan harus berupa angka.',
            'status_kepemilikan.required'   => 'Status kepemilikan wajib dipilih.',
            'rw_text.required'              => 'RW wajib diisi.',
            'rw_text.regex'                 => 'RW hanya boleh berisi huruf, angka, slash, dash, titik.',
            'rt_text.required'              => 'RT wajib diisi.',
            'rt_text.regex'                 => 'RT hanya boleh berisi huruf, angka, slash, dash, titik.',
            'wilayah_id.required'           => 'Kelurahan/Desa wajib dipilih.',
            'wilayah_id.exists'             => 'Wilayah tidak valid. Pastikan memilih sampai tingkat Kelurahan/Desa.',
            'alamat.required'               => 'Alamat wajib diisi.',
            'surveyor_id.required'          => 'Surveyor wajib ditugaskan.',
            'surveyor_id.exists'            => 'Surveyor tidak valid.',
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
}