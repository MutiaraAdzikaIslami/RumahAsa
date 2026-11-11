<?php

namespace App\Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // di-handle via middleware role:manage-users
    }

    public function rules(): array
    {
        return [
            'nama'        => ['required', 'string', 'max:150'],
            'username'    => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'       => ['nullable', 'email', 'max:100'],
            'role_id'     => ['required', 'exists:roles,id'],
            'wilayah_id'  => ['nullable', 'exists:wilayah,id'],
            'is_aktif'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'     => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah dipakai user lain.',
            'email.email'       => 'Format email tidak valid.',
            'role_id.required'  => 'Role wajib dipilih.',
            'role_id.exists'    => 'Role tidak ditemukan.',
            'wilayah_id.exists' => 'Wilayah tidak ditemukan.',
        ];
    }
}