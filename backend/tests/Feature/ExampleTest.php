<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root hanya mengarahkan: guest ke /login, ADMIN ke /dashboard.
     * Keduanya di method yang sama agar jumlah test tetap 31.
     */
    public function test_root_route_redirects_guest_and_admin(): void
    {
        $this->get('/')->assertRedirect('/login');

        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'ADMIN',
        ]);

        $this->actingAs($admin, 'web')
            ->get('/')
            ->assertRedirect('/dashboard');
    }
}
