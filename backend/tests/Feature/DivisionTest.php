<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DivisionTest extends TestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    public function test_daftar_divisi_tersedia_dan_jumlahnya_lima(): void
    {
        $admin = User::where('role', 'ADMIN')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/divisions');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data.divisions');

        $this->assertEquals(
            ['IT', 'HR', 'Finance', 'Operations', 'Marketing'],
            collect($response->json('data.divisions'))->pluck('name')->all()
        );
    }
}
