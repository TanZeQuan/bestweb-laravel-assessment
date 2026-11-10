<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_page(): void
    {
        $this->get('/admin/users')
            ->assertRedirect('/login');
    }

    public function test_admin_can_login(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'AdminPassword123',
        ])->assertRedirect('/admin/users');

        $this->assertAuthenticatedAs($admin);

        $this->get('/admin/users')->assertOk();
    }

    public function test_normal_user_cannot_login_as_admin(): void
    {
        User::create([
            'name' => 'Normal User',
            'email' => 'normal@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'normal@example.test',
            'password' => 'TestPassword123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
