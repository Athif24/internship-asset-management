<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssetLoanTest extends TestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    private function user(int $id): User
    {
        return User::findOrFail($id);
    }

    public function test_ast_02_pinjam_aset_available_berhasil(): void
    {
        $budi = $this->user(2);

        $response = $this->actingAs($budi, 'sanctum')
            ->postJson('/api/asset-loans', ['asset_id' => 9]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.loan.asset.status', 'BORROWED');

        $this->assertDatabaseHas('assets', ['id' => 9, 'status' => 'BORROWED']);
        $this->assertDatabaseHas('assets_loan', [
            'id' => 9,
            'assets_id' => 9,
            'borrower_id' => 2,
            'status' => 'BORROWED',
        ]);
    }

    public function test_ast_03_pinjam_aset_borrowed_ditolak(): void
    {
        // Aset 2 sedang dipinjam Sari (loan id 5).
        $this->actingAs($this->user(4), 'sanctum')
            ->postJson('/api/asset-loans', ['asset_id' => 2])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'AST-03');

        $this->assertDatabaseCount('assets_loan', 8);
    }

    public function test_ast_04_pinjam_aset_maintenance_ditolak(): void
    {
        $this->actingAs($this->user(2), 'sanctum')
            ->postJson('/api/asset-loans', ['asset_id' => 7])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'AST-04');

        $this->assertDatabaseHas('assets', ['id' => 7, 'status' => 'MAINTENANCE']);
    }

    public function test_ast_05_return_oleh_peminjam_berhasil(): void
    {
        $this->actingAs($this->user(3), 'sanctum')
            ->postJson('/api/asset-loans/5/return')
            ->assertOk()
            ->assertJsonPath('data.loan.status', 'RETURNED')
            ->assertJsonPath('data.loan.asset.status', 'AVAILABLE');

        $this->assertDatabaseHas('assets', ['id' => 2, 'status' => 'AVAILABLE']);
        $this->assertNotNull(DB::table('assets_loan')->find(5)->returned_at);
    }

    public function test_ast_06_return_kedua_dan_tanpa_pinjaman_ditolak(): void
    {
        // Return kedua: loan 5 sudah dicoba dikembalikan agar returned_at terisi.
        DB::table('assets_loan')->where('id', 5)->update([
            'returned_at' => now(),
            'status' => 'RETURNED',
        ]);
        DB::table('assets')->where('id', 2)->update(['status' => 'AVAILABLE']);

        $this->actingAs($this->user(3), 'sanctum')
            ->postJson('/api/asset-loans/5/return')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'AST-06');

        // Aset yang tidak pernah dipinjam (id 10).
        $this->actingAs($this->user(3), 'sanctum')
            ->postJson('/api/asset-loans/1/return')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'AST-06');
    }

    public function test_ast_07_return_oleh_user_lain_ditolak(): void
    {
        // Aset 2 dipinjam Sari (3); Andi (4) mencoba mengembalikan.
        $this->actingAs($this->user(4), 'sanctum')
            ->postJson('/api/asset-loans/5/return')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'AST-07');

        $this->assertDatabaseHas('assets', ['id' => 2, 'status' => 'BORROWED']);
    }

    public function test_ast_08_hanya_satu_pinjaman_aktif_per_aset_di_database(): void
    {
        DB::table('assets')->where('id', 10)->update(['status' => 'BORROWED']);

        DB::table('assets_loan')->insert([
            'assets_id' => 10,
            'borrower_id' => 2,
            'borrowed_at' => now(),
            'status' => 'BORROWED',
        ]);

        $this->expectException(QueryException::class);

        DB::table('assets_loan')->insert([
            'assets_id' => 10,
            'borrower_id' => 3,
            'borrowed_at' => now(),
            'status' => 'BORROWED',
        ]);
    }

    public function test_ast_09_lock_timeout_tidak_membuat_data_setengah_jadi(): void
    {
        $config = config('database.connections.mysql');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['database']
        );
        $blocker = new \PDO($dsn, $config['username'], $config['password'], [
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]);
        $blocker->exec('START TRANSACTION');
        $blocker->query('SELECT * FROM assets WHERE id = 10 FOR UPDATE')->fetchAll();

        try {
            $this->actingAs($this->user(2), 'sanctum')
                ->postJson('/api/asset-loans', ['asset_id' => 10])
                ->assertStatus(409)
                ->assertJsonPath('error.code', 'AST-09');

            $this->assertDatabaseMissing('assets_loan', ['assets_id' => 10]);
            $this->assertDatabaseHas('assets', ['id' => 10, 'status' => 'AVAILABLE']);
        } finally {
            $blocker->exec('ROLLBACK');
        }
    }

    public function test_riwayat_employee_hanya_pinjamannya_sendiri(): void
    {
        $response = $this->actingAs($this->user(2), 'sanctum')
            ->getJson('/api/asset-loans/history');

        $response->assertOk()->assertJsonPath('data.total', 2);

        foreach ($response->json('data.loans') as $loan) {
            $this->assertEquals(2, $loan['borrower_id']);
        }
    }
}
