<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function loginAsUser(): User
    {
        $role = Role::where('kode', 'admin')->first();
        $user = User::factory()->withPassword('password123')->create([
            'role_id'              => $role->id,
            'username'             => 'testuser',
            'must_change_password' => false,
        ]);
        $this->post('/login', ['username' => 'testuser', 'password' => 'password123']);
        return $user;
    }

    public function test_logout_manual_menghapus_sesi_dan_redirect_ke_login(): void
    {
        $user = $this->loginAsUser();
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHas('status');
    }

    public function test_logout_mencatat_audit_log(): void
    {
        $user = $this->loginAsUser();

        $this->post('/logout');

        $this->assertDatabaseHas('audit_log', [
            'user_id'  => $user->id,
            'aksi'     => AuditLog::AKSI['LOGOUT'],
            'entitas'  => 'users',
            'entitas_id' => $user->id,
        ]);
    }

    public function test_session_ping_mengembalikan_json_ok(): void
    {
        $this->loginAsUser();

        $response = $this->postJson('/session/ping');

        $response->assertOk();
        $response->assertJson(['ok' => true]);
    }

    public function test_ping_tanpa_auth_ditolak(): void
    {
        $response = $this->postJson('/session/ping');

        $response->assertUnauthorized();
    }

    public function test_session_berakhir_setelah_idle_melebihi_lifetime(): void
    {
        $user = $this->loginAsUser();

        // Simulasikan idle > 60 menit dengan manipulasi session
        // Set last_activity ke 61 menit yang lalu
        session(['last_activity' => now()->subMinutes(61)->timestamp]);

        // Akses route manapun yang butuh auth -> harus redirect ke login
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('warning');
        $this->assertGuest();
    }
}