<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function makeUser(string $password = 'password123'): User
    {
        $role = Role::where('kode', 'admin')->first();
        return User::factory()->withPassword($password)->create([
            'role_id'              => $role->id,
            'username'             => 'testuser',
            'must_change_password' => false,
        ]);
    }

    public function test_user_bisa_login_dengan_kredensial_benar(): void
    {
        $this->makeUser('password123');

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $this->makeUser('password123');

        $response = $this->from('/login')->post('/login', [
            'username' => 'testuser',
            'password' => 'salahpassword',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_user_nonaktif_tidak_bisa_login(): void
    {
        $role = Role::where('kode', 'admin')->first();
        User::factory()->withPassword('password123')->nonaktif()->create([
            'role_id'  => $role->id,
            'username' => 'nonaktifuser',
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => 'nonaktifuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_user_soft_deleted_tidak_bisa_login(): void
    {
        $role = Role::where('kode', 'admin')->first();
        $user = User::factory()->withPassword('password123')->create([
            'role_id'  => $role->id,
            'username' => 'deleteduser',
        ]);
        $user->delete();

        $response = $this->from('/login')->post('/login', [
            'username' => 'deleteduser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_akun_terkunci_setelah_5_percobaan_gagal(): void
    {
        $this->makeUser('password123');

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'username' => 'testuser',
                'password' => 'salahpassword',
            ]);
        }

        $response = $this->from('/login')->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors('username');

        $user = User::where('username', 'testuser')->first();
        $this->assertNotNull($user->locked_until);
    }
}
