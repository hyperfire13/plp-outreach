<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_personal_profile(): void
    {
        $user = $this->createUser('student_volunteer');
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'first_name' => 'Updated',
            'middle_name' => 'Middle',
            'last_name' => 'Person',
            'birthday' => '2001-05-10',
            'contact_number' => '09171234567',
            'email' => 'updated@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.email', 'updated@example.com')
            ->assertJsonPath('data.role.name', 'student_volunteer');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
            'role_id' => $user->role_id,
        ]);
    }

    public function test_user_cannot_change_role_or_college_through_profile(): void
    {
        $user = $this->createUser('student_volunteer');
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role_id' => 999,
            'college_id' => 999,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id', 'college_id']);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = $this->createUser('student_volunteer');
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile/password', [
            'current_password' => 'incorrect-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk();

        $this->assertTrue(
            Hash::check('new-password-123', $user->refresh()->password)
        );
    }

    private function createUser(string $roleName): User
    {
        $role = Role::query()->create([
            'name' => $roleName,
            'display_name' => 'Test Role',
        ]);

        return User::query()->create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $roleName.'@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }
}
