<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function createTestUser(
        string $name,
        string $email,
        string $phone
    ): User {
        return User::create([
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone,
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);
    }

    private function createAdmin(): User
    {
        $admin = $this->createTestUser(
            'Admin User',
            'admin@example.test',
            '0141234567'
        );

        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    public function test_admin_can_view_user_management_page(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('User Management');
    }

    public function test_normal_user_cannot_access_admin_page(): void
    {
        $user = $this->createTestUser(
            'Normal User',
            'normal@example.test',
            '0121234567'
        );

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_user_from_web_form(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Created User',
                'email' => 'created@example.test',
                'phone_number' => '0111111111',
                'password' => 'TestPassword123',
                'status' => 'active',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Created User',
            'email' => 'created@example.test',
            'phone_number' => '0111111111',
        ]);
    }

    public function test_admin_can_update_user_from_web_form(): void
    {
        $admin = $this->createAdmin();

        $user = $this->createTestUser(
            'Original User',
            'original@example.test',
            '0122222222'
        );

        $this->actingAs($admin)
            ->patch("/admin/users/{$user->id}", [
                'name' => 'Updated User',
                'email' => 'original@example.test',
                'phone_number' => '0122222222',
                'status' => 'inactive',
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_soft_delete_user_from_web(): void
    {
        $admin = $this->createAdmin();

        $user = $this->createTestUser(
            'Delete User',
            'delete@example.test',
            '0133333333'
        );

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_bulk_delete_users_from_web(): void
    {
        $admin = $this->createAdmin();

        $userA = $this->createTestUser(
            'Bulk User A',
            'bulk.a@example.test',
            '0111111111'
        );

        $userB = $this->createTestUser(
            'Bulk User B',
            'bulk.b@example.test',
            '0122222222'
        );

        $this->actingAs($admin)
            ->post('/admin/users/bulk-delete', [
                'user_ids' => [$userA->id, $userB->id],
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $userA->id,
        ]);

        $this->assertSoftDeleted('users', [
            'id' => $userB->id,
        ]);

        $this->assertNotSoftDeleted('users', [
            'id' => $admin->id,
        ]);
    }
}
