<?php

namespace App\Modules\Users\Http\Requests;

use App\Modules\Core\Rules\AlphanumericPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');

        return [
            'nama'        => ['required', 'string', 'max:150'],
            'username'    => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email'       => ['nullable', 'email', 'max:100'],
            'role_id'     => ['required', 'exists:roles,id'],
            'wilayah_id'  => ['nullable', 'exists:wilayah,id'],
            'password'    => ['nullable', 'string', new AlphanumericPassword()],
            'is_aktif'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'     => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah dipakai user lain.',
            'role_id.required'  => 'Role wajib dipilih.',
            'role_id.exists'    => 'Role tidak ditemukan.',
            'wilayah_id.exists' => 'Wilayah tidak ditemukan.',
        ];
    }
}