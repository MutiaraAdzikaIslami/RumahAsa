<?php

namespace App\Modules\Users\Http\Requests;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') instanceof User
            ? $this->route('user')->id
            : (int) $this->route('user');

        return [
            'role_id'    => ['required', 'integer', Rule::exists(Role::class, 'id')],
            'wilayah_id' => ['nullable', 'integer', Rule::exists(Wilayah::class, 'id')],
            'nama'       => ['required', 'string', 'max:150'],
            'username'   => [
                'required', 'string', 'min:3', 'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique(User::class, 'username')->ignore($userId),
            ],
            'email'    => ['nullable', 'string', 'email:rfc', 'max:100', Rule::unique(User::class, 'email')->ignore($userId)],
            'is_aktif' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $role = Role::find($this->input('role_id'));
            if (! $role) {
                return;
            }

            $wilayahId = $this->input('wilayah_id');
            if ($role->kode === 'surveyor' && empty($wilayahId)) {
                $validator->errors()->add('wilayah_id', 'Surveyor wajib memiliki wilayah tugas.');
            }

            if ($role->kode !== 'surveyor' && ! empty($wilayahId)) {
                $validator->errors()->add('wilayah_id', 'Role selain surveyor tidak boleh memiliki wilayah tugas.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'role_id.required'  => 'Role wajib dipilih.',
            'nama.required'     => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.min'      => 'Username minimal 3 karakter.',
            'username.regex'    => 'Username hanya boleh berisi huruf, angka, titik (.), dash (-), underscore (_). Tidak boleh spasi atau simbol lain.',
            'username.unique'   => 'Username sudah dipakai user lain.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah dipakai user lain.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge(['username' => strtolower(trim((string) $this->input('username')))]);
        }
        if ($this->has('email') && $this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
        if ($this->has('nama')) {
            $this->merge(['nama' => trim((string) $this->input('nama'))]);
        }
    }
}