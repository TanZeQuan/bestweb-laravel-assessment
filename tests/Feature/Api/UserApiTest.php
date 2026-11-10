<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_list_users_without_private_fields(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test.api@example.test',
            'phone_number' => '0191234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/users');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.name', 'Test User');

        $response->assertJsonMissingPath('data.0.email');
        $response->assertJsonMissingPath('data.0.phone_number');
        $response->assertJsonMissingPath('data.0.password');
    }

    public function test_users_can_be_filtered_by_status(): void
    {
        User::create([
            'name' => 'Active User',
            'email' => 'active@example.test',
            'phone_number' => '0111111111',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.test',
            'phone_number' => '0122222222',
            'password' => 'TestPassword123',
            'status' => 'inactive',
        ]);

        $response = $this->getJson('/api/users?status=inactive');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Inactive User');
        $response->assertJsonPath('data.0.status', 'inactive');
    }

    public function test_users_list_is_paginated(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.test",
                'phone_number' => sprintf('011%07d', $i),
                'password' => 'TestPassword123',
                'status' => 'active',
            ]);
        }

        $response = $this->getJson('/api/users?per_page=2');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('total', 5);
        $response->assertJsonPath('current_page', 1);
        $response->assertJsonPath('last_page', 3);

        $secondPage = $this->getJson('/api/users?per_page=2&page=2');

        $secondPage->assertOk();
        $secondPage->assertJsonCount(2, 'data');
        $secondPage->assertJsonPath('current_page', 2);
    }

    public function test_guests_cannot_create_users(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.test',
            'phone_number' => '0111234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('users', [
            'email' => 'new@example.test',
        ]);
    }

    public function test_normal_users_cannot_create_users(): void
    {
        $user = User::create([
            'name' => 'Normal User',
            'email' => 'normal@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/users', [
            'name' => 'Another User',
            'email' => 'another@example.test',
            'phone_number' => '0131234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'another@example.test',
        ]);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Created User',
            'email' => 'created@example.test',
            'phone_number' => '0151234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'created@example.test',
            'phone_number' => '0151234567',
            'status' => 'active',
            'role' => 'user',
        ]);

        $createdUser = User::where('email', 'created@example.test')->firstOrFail();

        $this->assertTrue(
            Hash::check('TestPassword123', $createdUser->password)
        );
    }

    public function test_admin_cannot_create_user_with_duplicate_email(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Duplicate Email User',
            'email' => 'existing@example.test',
            'phone_number' => '0199999999',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);

        $this->assertDatabaseMissing('users', [
            'phone_number' => '0199999999',
        ]);
    }

    public function test_admin_cannot_create_user_with_duplicate_phone_number(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Duplicate Phone User',
            'email' => 'new@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['phone_number']);

        $this->assertDatabaseMissing('users', [
            'email' => 'new@example.test',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $user = User::create([
            'name' => 'Original User',
            'email' => 'original@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/users/{$user->id}", [
            'name' => 'Updated User',
            'email' => 'original@example.test',
            'phone_number' => '0121234567',
            'status' => 'inactive',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'original@example.test',
            'phone_number' => '0121234567',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_cannot_update_user_with_duplicate_email_or_phone(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $existingUser = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.test',
            'phone_number' => '0111111111',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Target User',
            'email' => 'target@example.test',
            'phone_number' => '0122222222',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        // Test duplicate email.
        $this->patchJson("/api/users/{$user->id}", [
            'email' => $existingUser->email,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        // Test duplicate phone number.
        $this->patchJson("/api/users/{$user->id}", [
            'phone_number' => $existingUser->phone_number,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone_number']);

        // Ensure the rejected updates did not change the user.
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'target@example.test',
            'phone_number' => '0122222222',
        ]);
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $user = User::create([
            'name' => 'Detail User',
            'email' => 'detail@example.test',
            'phone_number' => '0123456789',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Detail User']);
        $response->assertJsonFragment(['email' => 'detail@example.test']);
        $response->assertJsonFragment(['phone_number' => '0123456789']);

        $this->assertStringNotContainsString(
            $user->password,
            $response->getContent()
        );
    }

    public function test_guests_cannot_view_user_details(): void
    {
        $user = User::create([
            'name' => 'Detail User',
            'email' => 'detail@example.test',
            'phone_number' => '0123456789',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertUnauthorized();
    }

    public function test_normal_users_cannot_view_user_details(): void
    {
        $normalUser = User::create([
            'name' => 'Normal User',
            'email' => 'normal@example.test',
            'phone_number' => '0111111111',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@example.test',
            'phone_number' => '0122222222',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($normalUser);

        $response = $this->getJson("/api/users/{$targetUser->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_soft_delete_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $user = User::create([
            'name' => 'Delete Test User',
            'email' => 'delete@example.test',
            'phone_number' => '0121234567',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertNull(User::find($user->id));

        $this->assertNotNull(
            User::withTrashed()->find($user->id)
        );
    }

    public function test_admin_can_bulk_delete_users(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        $userA = User::create([
            'name' => 'Bulk User A',
            'email' => 'bulk.a@example.test',
            'phone_number' => '0111111111',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $userB = User::create([
            'name' => 'Bulk User B',
            'email' => 'bulk.b@example.test',
            'phone_number' => '0122222222',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users/bulk-delete', [
            'user_ids' => [$userA->id, $userB->id],
        ]);

        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
            'id' => $userA->id,
        ]);

        $this->assertSoftDeleted('users', [
            'id' => $userB->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/users/{$admin->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_guests_cannot_delete_users(): void
    {
        $user = User::create([
            'name' => 'Target User',
            'email' => 'target@example.test',
            'phone_number' => '0123456789',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $this->deleteJson("/api/users/{$user->id}")
            ->assertUnauthorized();

        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_normal_users_cannot_delete_users(): void
    {
        $normalUser = User::create([
            'name' => 'Normal User',
            'email' => 'normal@example.test',
            'phone_number' => '0111111111',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@example.test',
            'phone_number' => '0122222222',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($normalUser);

        $this->deleteJson("/api/users/{$targetUser->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_admin_can_export_users_to_excel(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone_number' => '0141234567',
            'password' => 'AdminPassword123',
            'status' => 'active',
        ]);

        $admin->forceFill(['role' => 'admin'])->save();

        Sanctum::actingAs($admin);

        $response = $this->get('/api/users/export');

        $response->assertOk();
        $response->assertDownload('users.xlsx');
    }

    public function test_normal_users_cannot_export_excel(): void
    {
        $user = User::create([
            'name' => 'Normal User',
            'email' => 'normal@example.test',
            'phone_number' => '0123456789',
            'password' => 'TestPassword123',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/users/export')
            ->assertForbidden();
    }

    public function test_guests_cannot_export_excel(): void
    {
        $this->getJson('/api/users/export')
            ->assertUnauthorized();
    }
}
