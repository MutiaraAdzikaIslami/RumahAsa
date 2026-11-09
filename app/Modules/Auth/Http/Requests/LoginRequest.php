<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    public function throttleKey(): string
    {
        return 'login.attempts:' . strtolower($this->input('username'));
    }

    public function getUsername(): string
    {
        return strtolower(trim($this->input('username')));
    }

    public function getPassword(): string
    {
        return $this->input('password');
    }

    public function remember(): bool
    {
        return $this->boolean('remember');
    }
}
