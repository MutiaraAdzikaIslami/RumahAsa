<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function loginAsUser(bool $mustChange = false, string $password = 'password123'): User
    {
        $role = Role::where('kode', 'admin')->first();
        $user = User::factory()->withPassword($password)->create([
            'role_id'              => $role->id,
            'username'             => 'testuser',
            'must_change_password' => $mustChange,
        ]);
        $this->post('/login', ['username' => 'testuser', 'password' => $password]);
        return $user;
    }

    public function test_user_bisa_ganti_password_dengan_kredensial_benar(): void
    {
        $user = $this->loginAsUser(false, 'password123');

        $response = $this->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'NewPassword2025',
            'password_confirmation' => 'NewPassword2025',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword2025', $user->password));
        $this->assertFalse($user->must_change_password);
        $this->assertNotNull($user->password_changed_at);
    }

    public function test_ganti_password_dengan_password_lama_salah_ditolak(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->from(route('password.change'))->put('/password', [
            'current_password'      => 'salahpassword',
            'password'              => 'NewPassword2025',
            'password_confirmation' => 'NewPassword2025',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHasErrors('current_password');
    }

    public function test_password_baru_tidak_boleh_sama_dengan_password_lama(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->from(route('password.change'))->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHasErrors('password');
    }

    public function test_password_harus_alphanumeric_tanpa_simbol(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->from(route('password.change'))->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'NewPassword@2025',
            'password_confirmation' => 'NewPassword@2025',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHasErrors('password');
    }

    public function test_ganti_password_mencatat_audit_log(): void
    {
        $user = $this->loginAsUser(false, 'password123');

        $this->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'NewPassword2025',
            'password_confirmation' => 'NewPassword2025',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id'    => $user->id,
            'aksi'       => AuditLog::AKSI['PASSWORD_CHANGE'],
            'entitas'    => 'users',
            'entitas_id' => $user->id,
        ]);
    }

    public function test_user_dengan_must_change_password_dipaksa_redirect_ke_form_ganti(): void
    {
        $this->loginAsUser(true, 'password123');

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHas('warning');
    }
}