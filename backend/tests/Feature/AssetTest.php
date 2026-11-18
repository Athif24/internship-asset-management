<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    public function test_ast_01_kode_aset_duplikat_ditolak_409(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/assets', [
            'assets_code' => 'AST-LPT-001',
            'name' => 'Duplikat',
            'category_id' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'AST-01');

        $this->assertDatabaseCount('assets', 10);
    }

    public function test_kategori_yang_masih_punya_aset_tidak_bisa_dihapus(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/asset-categories/1');

        $response->assertStatus(409)
            ->assertJsonPath('error.code', 'CATEGORY_HAS_ASSETS');

        $this->assertStringContainsString('masih memiliki', $response->json('message'));
        $this->assertDatabaseHas('assets_categories', ['id' => 1]);
    }

    public function test_admin_dapat_membuat_kategori_dan_aset(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/asset-categories', ['name' => 'Tablet'])
            ->assertStatus(201);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/assets', [
                'assets_code' => 'AST-TAB-001',
                'name' => 'Tablet Samsung',
                'category_id' => 6,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('assets', ['assets_code' => 'AST-TAB-001', 'status' => 'AVAILABLE']);
    }

    public function test_employee_tidak_boleh_membuat_aset(): void
    {
        $user = User::where('role', 'USER')->firstOrFail();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/assets', [
                'assets_code' => 'AST-X-001',
                'name' => 'Aset ilegal',
                'category_id' => 1,
            ])
            ->assertStatus(403);
    }

    public function test_filter_status_aset_berfungsi(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/assets?status=MAINTENANCE');

        $response->assertOk()->assertJsonPath('data.total', 1);

        $this->assertEquals(
            'AST-UKR-002',
            $response->json('data.assets.0.assets_code')
        );
    }

    public function test_admin_dapat_menghapus_aset_tanpa_pinjaman(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $created = $this->actingAs($admin, 'sanctum')->postJson('/api/assets', [
            'assets_code' => 'AST-TEMP-001',
            'name' => 'Aset Sementara',
            'category_id' => 1,
        ]);
        $created->assertStatus(201);

        $id = $created->json('data.asset.id');

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/assets/'.$id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('assets', ['id' => $id]);
    }

    public function test_hapus_aset_yang_punya_riwayat_ditolak(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/assets/1')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'ASSET_HAS_HISTORY');

        $this->assertDatabaseHas('assets', ['id' => 1]);
    }

    public function test_hapus_aset_yang_sedang_dipinjam_ditolak(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/assets/2')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'ASSET_STILL_BORROWED');

        $this->assertDatabaseHas('assets', ['id' => 2]);
    }

    public function test_detail_aset_memuat_divisi_peminjam_aktif(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/assets/2');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.asset.assets_code', 'AST-LPT-002')
            ->assertJsonPath('data.asset.loans.0.borrower.name', 'Sari Wulandari')
            ->assertJsonPath('data.asset.loans.0.borrower.division.name', 'HR');
    }

    public function test_ast_10_tetap_dipakai_saat_ubah_status_aset_dipinjam(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/assets/2', [
                'assets_code' => 'AST-LPT-002',
                'name' => 'Laptop Lenovo ThinkPad T14',
                'category_id' => 1,
                'status' => 'MAINTENANCE',
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'AST-10');

        $this->assertDatabaseHas('assets', ['id' => 2, 'status' => 'BORROWED']);
    }
}
