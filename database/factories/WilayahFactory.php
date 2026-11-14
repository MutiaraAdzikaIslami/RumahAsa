<?php

namespace Database\Factories;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

class WilayahFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode'      => 'KEC-' . $this->faker->unique()->numerify('###'),
            'nama'      => $this->faker->city,
            'tingkat'   => 'kecamatan',
            'parent_id' => null,
            'is_aktif'  => true,
        ];
    }
}