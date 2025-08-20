<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_admin_panel()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Assuming there's an admin route, e.g., /admin/dashboard
        // You might need to create this route in routes/web.php
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_access_admin_panel()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_admin_panel()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function admin_can_view_user_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['email' => 'user1@example.com', 'name' => 'User One', 'provider' => null, 'provider_id' => null]);
        $user2 = User::factory()->create(['email' => 'user2@google.com', 'name' => 'User Two Google', 'provider' => 'google', 'google_id' => 'google_id_1']);
        $user3 = User::factory()->create(['email' => 'user3@apple.com', 'name' => 'User Three Apple', 'provider' => 'apple', 'apple_id' => 'apple_id_1']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
        $response->assertSee('User One');
        $response->assertSee('user1@example.com');
        $response->assertSee('email');
        $response->assertSee('User Two Google');
        $response->assertSee('user2@google.com');
        $response->assertSee('Google');
        $response->assertSee('User Three Apple');
        $response->assertSee('user3@apple.com');
        $response->assertSee('Apple');
    }
}
