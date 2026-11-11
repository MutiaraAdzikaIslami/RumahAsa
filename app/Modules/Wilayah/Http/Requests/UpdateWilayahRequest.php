<?php

namespace App\Modules\Wilayah\Http\Requests;

use App\Models\Wilayah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWilayahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $wilayahId = $this->route('wilayah')->id ?? $this->route('wilayah');

        return [
            'kode'      => ['required', 'string', 'max:30', Rule::unique('wilayah', 'kode')->ignore($wilayahId)],
            'nama'      => ['required', 'string', 'max:100'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:wilayah,id',
                function ($attribute, $value, $fail) {
                    $wilayah = $this->route('wilayah');
                    $wilayah = is_object($wilayah) ? $wilayah : Wilayah::find($wilayah);
                    if ($value && $wilayah && !Wilayah::isValidParent((int) $value, $wilayah->tingkat)) {
                        $fail('Parent harus 1 jenjang di atas tingkat wilayah ini.');
                    }
                },
            ],
            'is_aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required'    => 'Kode wajib diisi.',
            'kode.unique'      => 'Kode sudah dipakai wilayah lain.',
            'nama.required'    => 'Nama wajib diisi.',
            'parent_id.exists' => 'Parent tidak ditemukan.',
        ];
    }
}