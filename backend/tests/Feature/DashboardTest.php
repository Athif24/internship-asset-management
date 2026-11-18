<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    public function test_ringkasan_dashboard_konsisten_dengan_tabel_aset_dan_pinjaman(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/assets');

        $response->assertOk();

        $summary = $response->json('data.summary');

        $this->assertEquals(10, $summary['TOTAL']);
        $this->assertEquals(5, $summary['AVAILABLE']);
        $this->assertEquals(4, $summary['BORROWED']);
        $this->assertEquals(1, $summary['MAINTENANCE']);
        $this->assertEquals(4, $summary['ACTIVE_LOANS']);
        $this->assertEquals(
            DB::table('assets_loan')->whereNull('returned_at')->count(),
            $summary['ACTIVE_LOANS']
        );
    }

    public function test_dashboard_diperbarui_setelah_pinjam_dan_kembalikan(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();
        $budi = User::findOrFail(2);

        $this->actingAs($budi, 'sanctum')
            ->postJson('/api/asset-loans', ['asset_id' => 9])
            ->assertStatus(201);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/assets')
            ->assertJsonPath('data.summary.BORROWED', 5)
            ->assertJsonPath('data.summary.AVAILABLE', 4)
            ->assertJsonPath('data.summary.ACTIVE_LOANS', 5);

        $this->actingAs($budi, 'sanctum')
            ->postJson('/api/asset-loans/9/return')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/assets')
            ->assertJsonPath('data.summary.BORROWED', 4)
            ->assertJsonPath('data.summary.AVAILABLE', 5)
            ->assertJsonPath('data.summary.ACTIVE_LOANS', 4);
    }

    public function test_dashboard_mengembalikan_aset_dengan_peminjam_aktif_dan_filter_q(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $all = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/assets');

        $all->assertOk()
            ->assertJsonStructure(['data' => ['summary', 'loans', 'assets']]);
        $this->assertCount(10, $all->json('data.assets'));

        $borrowed = collect($all->json('data.assets'))->firstWhere('status', 'BORROWED');
        $this->assertNotNull($borrowed['active_loan']['borrower']['name'] ?? null);
        $this->assertNotNull($borrowed['active_loan']['borrower']['division']['name'] ?? null);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/assets?q=AST-LPT-001')
            ->assertOk()
            ->assertJsonPath('data.assets.0.assets_code', 'AST-LPT-001');
    }

    public function test_employee_tidak_boleh_mengakses_dashboard(): void
    {
        $this->actingAs(User::findOrFail(2), 'sanctum')
            ->getJson('/api/dashboard/assets')
            ->assertStatus(403);
    }
}
