<?php

namespace Database\Factories;

use App\Models\PenerimaBantuan;
use App\Models\RumahWarga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenerimaBantuan>
 */
class PenerimaBantuanFactory extends Factory
{
    protected $model = PenerimaBantuan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rumah_id' => RumahWarga::factory(),
            'status_bantuan' => PenerimaBantuan::STATUS_MENUNGGU_PENYALURAN,
            'tanggal_bantuan' => null,
            'jenis_bantuan' => null,
            'nominal' => null,
            'nomor_sk' => null,
        ];
    }
}
