<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Core\Rules\AlphanumericPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah lewat middleware auth
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'confirmed',
                new AlphanumericPassword(),
                Rule::notIn([$this->input('current_password')]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required'          => 'Password baru wajib diisi.',
            'password.confirmed'         => 'Konfirmasi password tidak cocok.',
            'password.not_in'            => 'Password baru tidak boleh sama dengan password lama.',
        ];
    }
}