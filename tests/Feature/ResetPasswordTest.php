<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $adminRole = Role::where('kode', 'admin')->first();
        $this->admin = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);
    }

    public function test_admin_bisa_reset_password_user_lain(): void
    {
        $target = User::factory()->withPassword('PasswordLama123')->create();

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $response->assertJsonStructure(['message', 'user', 'password_sementara']);

        // Password lama harus tidak valid lagi
        $target->refresh();
        $this->assertFalse(Hash::check('PasswordLama123', $target->password));

        // Password sementara yang baru harus valid
        $passwordBaru = $response->json('password_sementara');
        $this->assertTrue(Hash::check($passwordBaru, $target->password));
    }

    public function test_reset_password_set_must_change_password_true(): void
    {
        $target = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $target->refresh();
        $this->assertTrue($target->must_change_password);
        $this->assertNull($target->password_changed_at);
    }

    public function test_reset_password_unlock_akun_yang_terkunci(): void
    {
        $target = User::factory()->create([
            'locked_until'        => now()->addMinutes(15),
            'failed_login_count'  => 5,
        ]);

        $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $target->refresh();
        $this->assertNull($target->locked_until);
        $this->assertSame(0, $target->failed_login_count);
    }

    public function test_admin_tidak_bisa_reset_password_diri_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/users/{$this->admin->id}/reset-password");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak dapat mereset password akun sendiri'));
    }

    public function test_reset_password_mencatat_audit_log(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $this->assertDatabaseHas('audit_log', [
            'user_id'    => $this->admin->id,
            'aksi'       => AuditLog::AKSI['PASSWORD_RESET_BY_ADMIN'],
            'entitas'    => 'users',
            'entitas_id' => $target->id,
        ]);
    }
}