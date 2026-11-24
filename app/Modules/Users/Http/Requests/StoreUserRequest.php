<?php

namespace App\Modules\Users\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // di-handle via middleware role:manage-users
    }

    public function rules(): array
    {
        return [
            'nama'       => ['required', 'string', 'max:150'],
            'username'   => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'      => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'role_id'    => ['required', 'exists:roles,id'],
            'wilayah_id' => ['nullable', 'exists:wilayah,id'],
            'is_aktif'   => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $role = Role::find($this->input('role_id'));
            if (!$role) {
                return;
            }

            $wilayahId = $this->input('wilayah_id');

            if ($role->kode === 'surveyor') {
                if (empty($wilayahId)) {
                    $validator->errors()->add('wilayah_id', 'Surveyor wajib memiliki wilayah tugas.');
                }
            } elseif (!empty($wilayahId)) {
                $validator->errors()->add(
                    'wilayah_id',
                    "Role {$role->nama} tidak boleh memiliki wilayah tugas (lihat semua wilayah)."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'nama.required'     => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah dipakai user lain.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah dipakai user lain.',
            'role_id.required'  => 'Role wajib dipilih.',
            'role_id.exists'    => 'Role tidak ditemukan.',
            'wilayah_id.exists' => 'Wilayah tidak ditemukan.',
        ];
    }
}