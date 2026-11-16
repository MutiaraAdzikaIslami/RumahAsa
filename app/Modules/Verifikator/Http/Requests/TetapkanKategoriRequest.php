<?php

namespace App\Modules\Verifikator\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TetapkanKategoriRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Hanya role Verifikator yang diizinkan menetapkan kategori.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role?->kode === 'verifikator';
    }

    /**
     * Get the validation rules that apply to the request.
     * SCRUM-113: Validasi catatan wajib
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori' => ['required', 'string', Rule::in(['layak_huni', 'tidak_layak_huni'])],
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kategori.required' => 'Pilihan kategori wajib ditentukan (Layak Huni atau Tidak Layak Huni).',
            'kategori.in' => 'Pilihan kategori harus berupa Layak Huni atau Tidak Layak Huni.',
            'catatan.required' => 'Catatan alasan penetapan wajib diisi.',
            'catatan.min' => 'Catatan alasan minimal 5 karakter.',
            'catatan.max' => 'Catatan alasan maksimal 1000 karakter.',
        ];
    }

    /**
     * Prepare the data for validation.
     * Memastikan string catatan di-trim sebelum divalidasi agar spasi kosong tidak lolos validasi required.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('catatan') && is_string($this->catatan)) {
            $this->merge([
                'catatan' => trim($this->catatan),
            ]);
        }
    }
}
