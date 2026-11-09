<?php

namespace App\Modules\Wilayah\Http\Requests;

use App\Models\Wilayah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWilayahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization di-handle via middleware 'role:manage-wilayah'
    }

    public function rules(): array
    {
        return [
            'kode'      => ['required', 'string', 'max:30', 'unique:wilayah,kode'],
            'nama'      => ['required', 'string', 'max:100'],
            'tingkat'   => ['required', Rule::in(array_keys(Wilayah::URUTAN_TINGKAT))],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:wilayah,id',
                function ($attribute, $value, $fail) {
                    if ($value && !Wilayah::isValidParent((int) $value, $this->input('tingkat'))) {
                        $fail('Parent harus 1 jenjang di atas tingkat yang dipilih.');
                    }
                },
            ],
            'is_aktif'  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required'    => 'Kode wajib diisi.',
            'kode.unique'      => 'Kode sudah dipakai wilayah lain.',
            'nama.required'    => 'Nama wajib diisi.',
            'tingkat.required' => 'Tingkat wajib dipilih.',
            'tingkat.in'       => 'Tingkat harus salah satu: kecamatan, kelurahan, rw, rt.',
            'parent_id.exists' => 'Parent tidak ditemukan.',
        ];
    }
}