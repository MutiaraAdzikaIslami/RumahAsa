<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama'                  => fake()->name(),
            'username'              => fake()->unique()->userName(),
            'email'                 => fake()->unique()->safeEmail(),
            'password'              => Hash::make('password'),
            'must_change_password'  => false,
            'is_aktif'              => true,
        ];
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'must_change_password' => true,
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_aktif' => false,
        ]);
    }

    public function withPassword(string $plainPassword): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($plainPassword),
        ]);
    }
}
