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

    private function loginAsUser(bool $mustChange = false, string $password = 'password123', string $username = 'testuser'): User
    {
        $role = Role::where('kode', 'admin')->first();
        $user = User::factory()->withPassword($password)->create([
            'role_id'              => $role->id,
            'username'             => $username,
            'must_change_password' => $mustChange,
        ]);
        $this->post('/login', ['username' => $username, 'password' => $password]);
        return $user;
    }

    /**
     * AC-01: Pengguna mengganti password dengan data valid.
     */
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

    /**
     * AC-02: Password lama salah -> perubahan ditolak.
     */
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

    /**
     * AC-03: Konfirmasi password tidak sesuai -> perubahan ditolak.
     */
    public function test_konfirmasi_password_tidak_cocok_ditolak(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->from(route('password.change'))->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'NewPassword2025',
            'password_confirmation' => 'BedaPassword2025',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHasErrors('password');
    }

    /**
     * AC-04: Password baru tidak memenuhi kebijakan (simbol) -> ditolak.
     */
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

    /**
     * AC-04: Password baru kurang dari 8 karakter -> ditolak.
     */
    public function test_password_kurang_dari_8_karakter_ditolak(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->from(route('password.change'))->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'Short1',
            'password_confirmation' => 'Short1',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHasErrors('password');
    }

    /**
     * AC-04: Password baru mengandung spasi -> ditolak.
     */
    public function test_password_mengandung_spasi_ditolak(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->from(route('password.change'))->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'Pass 12345',
            'password_confirmation' => 'Pass 12345',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHasErrors('password');
    }

    /**
     * AC-05: Password baru sama dengan password lama -> ditolak sesuai kebijakan.
     */
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

    /**
     * AC-06: Akun baru melakukan login pertama kali -> wajib mengganti password.
     */
    public function test_akun_baru_pertama_kali_login_wajib_ganti_password(): void
    {
        $role = Role::where('kode', 'admin')->first();
        // Akun baru dibuat dengan must_change_password=true
        User::factory()->withPassword('RumahAsa2025')->create([
            'role_id'              => $role->id,
            'username'             => 'akunbaru',
            'must_change_password' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'akunbaru',
            'password' => 'RumahAsa2025',
        ]);

        // Dialihkan langsung ke halaman ganti password dengan warning
        $response->assertRedirect(route('password.change'));
        $response->assertSessionHas('warning');
    }

    /**
     * AC-07: Akun hasil reset login dengan password sementara -> wajib mengganti password.
     */
    public function test_akun_hasil_reset_login_dengan_password_sementara_wajib_ganti_password(): void
    {
        $role = Role::where('kode', 'admin')->first();
        // Simulasi akun yang telah di-reset oleh admin
        User::factory()->withPassword('TempPass1234')->create([
            'role_id'              => $role->id,
            'username'             => 'akunreset',
            'must_change_password' => true,
            'password_changed_at'  => null,
        ]);

        $response = $this->post('/login', [
            'username' => 'akunreset',
            'password' => 'TempPass1234',
        ]);

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHas('warning');
    }

    /**
     * AC-08: Pengguna belum mengganti password wajib -> akses fitur lain dibatasi.
     */
    public function test_user_dengan_must_change_password_dipaksa_redirect_ke_form_ganti(): void
    {
        $this->loginAsUser(true, 'password123');

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('password.change'));
        $response->assertSessionHas('warning');
    }

    /**
     * AC-08: Pengguna wajib ganti password dibatasi aksesnya pada request JSON (HTTP 403).
     */
    public function test_user_dengan_must_change_password_request_json_mendapat_403(): void
    {
        $this->loginAsUser(true, 'password123');

        $response = $this->getJson('/dashboard');

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Anda wajib mengganti password sebelum dapat mengakses fitur ini.',
        ]);
    }

    /**
     * AC-09: Pengguna berhasil mengganti password -> status wajib ganti password diperbarui dan dapat akses normal.
     */
    public function test_pengguna_berhasil_mengganti_password_status_diperbarui_dan_bisa_akses_normal(): void
    {
        $user = $this->loginAsUser(true, 'password123');

        // Sebelum ganti: akses dashboard dialihkan
        $this->get('/dashboard')->assertRedirect(route('password.change'));

        // Proses ganti password
        $response = $this->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'BaruSukses2025',
            'password_confirmation' => 'BaruSukses2025',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertNotNull($user->password_changed_at);

        // Setelah ganti: akses dashboard terbuka normal (HTTP 200)
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    /**
     * AC-10: Pengguna mencoba mengganti password akun lain -> ditolak / tidak berpengaruh pada akun lain.
     */
    public function test_pengguna_tidak_bisa_mengganti_password_akun_lain(): void
    {
        // User target (korban)
        $role = Role::where('kode', 'admin')->first();
        $userLain = User::factory()->withPassword('PasswordAsliUserLain1')->create([
            'role_id'  => $role->id,
            'username' => 'userlain',
        ]);

        // User pelaku login
        $this->loginAsUser(false, 'password123', 'attacker');

        // Attacker mencoba menyisipkan user_id korban pada request PUT /password
        $this->put('/password', [
            'user_id'               => $userLain->id,
            'target_id'             => $userLain->id,
            'current_password'      => 'password123', // password attacker sendiri
            'password'              => 'HackedPassword2025',
            'password_confirmation' => 'HackedPassword2025',
        ]);

        // Password user korban TIDAK berubah
        $userLain->refresh();
        $this->assertTrue(Hash::check('PasswordAsliUserLain1', $userLain->password));
        $this->assertFalse(Hash::check('HackedPassword2025', $userLain->password));
    }

    /**
     * AC-11: Password berhasil diganti -> password lama tidak lagi berlaku.
     */
    public function test_setelah_password_diganti_password_lama_tidak_lagi_berlaku(): void
    {
        $this->loginAsUser(false, 'password123', 'usergantipwd');

        // Ganti password
        $this->put('/password', [
            'current_password'      => 'password123',
            'password'              => 'PasswordBaru2025',
            'password_confirmation' => 'PasswordBaru2025',
        ]);

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Coba login dengan password lama -> harus gagal
        $loginLamaResponse = $this->post('/login', [
            'username' => 'usergantipwd',
            'password' => 'password123',
        ]);
        $loginLamaResponse->assertSessionHasErrors('username');
        $this->assertGuest();

        // Coba login dengan password baru -> harus berhasil
        $loginBaruResponse = $this->post('/login', [
            'username' => 'usergantipwd',
            'password' => 'PasswordBaru2025',
        ]);
        $loginBaruResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    /**
     * Form penggantian password dapat dirender dengan benar.
     */
    public function test_halaman_form_ganti_password_dapat_dirender_dengan_baik(): void
    {
        $this->loginAsUser(false, 'password123');

        $response = $this->get(route('password.change'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.password-change');
        $response->assertSee('Ganti Password');
        $response->assertSee('Password Lama');
        $response->assertSee('Password Baru');
        $response->assertSee('Konfirmasi Password Baru');
    }

    /**
     * Audit log mencatat aksi PASSWORD_CHANGE.
     */
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
}
