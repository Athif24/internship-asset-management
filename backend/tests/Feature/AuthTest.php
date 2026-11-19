<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    public function test_login_demo_berhasil_dan_mengembalikan_token(): void
    {
        // Mobile login hanya untuk USER.
        $response = $this->postJson('/api/login', [
            'email' => 'budi@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'USER')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $this->assertNotEmpty($response->json('data.token'));

        // ADMIN ditolak di endpoint yang sama, dalam method yang sama (jumlah test tetap 31).
        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ])->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_login_dengan_password_salah_ditolak_401(): void
    {
        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'salah',
        ])->assertStatus(401)
            ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
    }

    public function test_user_tidak_boleh_login_di_web_admin(): void
    {
        // Web admin memakai session (guard web), bukan token Sanctum.
        $response = $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'Email ini terdaftar sebagai USER, bukan ADMIN. Akun USER hanya dapat login melalui aplikasi mobile. Hubungi administrator jika Anda seharusnya memiliki akses admin.',
            ])
            ->assertSessionHasInput('email', 'budi@example.com');

        // Session USER harus dibuang, bukan sekadar ditolak redirect-nya.
        $this->assertGuest();
    }

    public function test_endpoint_tertutup_tanpa_token_mengembalikan_401(): void
    {
        $this->getJson('/api/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_admin_tidak_boleh_meminjam_aset_403(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/asset-loans', ['asset_id' => 10])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_logout_mencabut_token_sehingga_token_lama_ditolak(): void
    {
        // Token nyata dibuat langsung agar tidak ada session dari Auth::attempt()
        // yang membuat Sanctum mengembalikan TransientToken.
        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $token = $user->createToken('asset-app')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Guard menyimpan user antar-request dalam satu test; reset agar
        // request berikutnya benar-benar membaca token dari database.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }
}
