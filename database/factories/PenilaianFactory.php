<?php

namespace Database\Factories;

use App\Models\Penilaian;
use App\Models\RumahWarga;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Penilaian>
 */
class PenilaianFactory extends Factory
{
    protected $model = Penilaian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rumah_id' => RumahWarga::factory(),
            'user_id' => User::factory(),
            'kategori' => Penilaian::KATEGORI_TIDAK_LAYAK_HUNI,
            'catatan' => fake()->sentence(8),
            'alasan_revisi' => null,
            'is_aktif' => true,
        ];
    }
}
