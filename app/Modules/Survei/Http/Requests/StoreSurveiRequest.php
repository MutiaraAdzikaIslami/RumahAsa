<?php

namespace App\Modules\Survei\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_survei'    => ['required', 'date', 'before_or_equal:today'],
            'kondisi'           => ['required', 'array'],
            'kondisi.*'         => ['required', 'integer', 'exists:opsi_kondisi,id'],
            'latitude_survei'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_survei'  => ['nullable', 'numeric', 'between:-180,180'],
            // Foto bukti per komponen: opsional, max 5 foto/komponen, max 5MB/file
            'foto'              => ['nullable', 'array'],
            'foto.*'            => ['nullable', 'array', 'max:5'],
            'foto.*.*'          => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_survei.required'         => 'Tanggal survei wajib diisi.',
            'tanggal_survei.before_or_equal'  => 'Tanggal survei tidak boleh di masa depan.',
            'kondisi.required'                => 'Semua komponen penilaian wajib dinilai.',
            'kondisi.array'                   => 'Format penilaian tidak valid.',
            'kondisi.*.required'              => 'Komponen wajib dipilih nilainya.',
            'kondisi.*.exists'                => 'Opsi nilai tidak valid.',
            'latitude_survei.between'         => 'Latitude harus antara -90 sampai 90.',
            'longitude_survei.between'        => 'Longitude harus antara -180 sampai 180.',
            'foto.*.max'                       => 'Maksimal 5 foto per komponen.',
            'foto.*.*.image'                   => 'File harus berupa gambar.',
            'foto.*.*.mimes'                   => 'Format foto harus JPG, PNG, atau WebP.',
            'foto.*.*.max'                     => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
