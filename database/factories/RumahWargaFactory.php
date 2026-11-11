<?php

namespace Database\Factories;

use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class RumahWargaFactory extends Factory
{
    public function definition(): array
    {
        $nik = $this->faker->unique()->numerify('################');
        $noKk = $this->faker->numerify('################');
        
        // Pastikan ada user surveyor untuk dijadikan surveyor_id
        $surveyor = User::factory()->create([
            'role_id' => \App\Models\Role::where('kode', 'surveyor')->first()->id ?? 1
        ]);
        
        return [
            'kode_registrasi'      => 'RUMAH-' . date('Y') . '-' . $this->faker->unique()->numerify('####'),
            'nama_kepala_keluarga' => $this->faker->name,
            'nik_enc'              => Crypt::encryptString($nik),
            'nik_hash'             => hash_hmac('sha256', $nik, config('rumahasa.nik_hmac_key', 'test')),
            'no_kk_enc'            => Crypt::encryptString($noKk),
            'no_kk_hash'           => hash_hmac('sha256', $noKk, config('rumahasa.nik_hmac_key', 'test')),
            'jumlah_anggota'       => $this->faker->numberBetween(2, 6),
            'luas_rumah'           => $this->faker->randomFloat(2, 20, 100),
            'pekerjaan'            => $this->faker->jobTitle,
            'penghasilan'          => $this->faker->numberBetween(1000000, 5000000),
            'status_kepemilikan'   => $this->faker->randomElement(['milik_sendiri', 'sewa', 'menumpang']),
            'alamat'               => $this->faker->address,
            'latitude'             => $this->faker->latitude,
            'longitude'            => $this->faker->longitude,
            'wilayah_id'           => Wilayah::factory()->create(['tingkat' => 'rt'])->id,
            'surveyor_id'           => $surveyor->id,
            'status'               => RumahWarga::STATUS_DRAF,
        ];
    }
}