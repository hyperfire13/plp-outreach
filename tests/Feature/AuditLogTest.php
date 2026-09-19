<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_administrator_can_filter_audit_logs(): void
    {
        $administrator = $this->createUser('super_admin');
        AuditLog::query()->create([
            'actor_id' => $administrator->id,
            'action' => 'post',
            'module' => 'engagement_records',
            'description' => 'POST api/v1/engagement-records',
            'ip_address' => '127.0.0.1',
            'request_id' => 'audit-test-request',
            'http_method' => 'POST',
            'new_values' => ['title' => 'Community activity'],
            'created_at' => '2026-09-19 10:00:00',
        ]);
        Sanctum::actingAs($administrator);

        $response = $this->getJson(
            '/api/v1/audit-logs?module=engagement_records&action=post&date_from=2026-09-19&date_to=2026-09-19'
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.request_id', 'audit-test-request')
            ->assertJsonPath('meta.modules.0', 'engagement_records')
            ->assertJsonPath('meta.actions.0', 'post');
    }

    public function test_role_without_audit_permission_cannot_view_logs(): void
    {
        Sanctum::actingAs($this->createUser('college_admin'));

        $this->getJson('/api/v1/audit-logs')->assertForbidden();
    }

    private function createUser(string $roleName): User
    {
        $role = Role::query()->create([
            'name' => $roleName,
            'display_name' => str($roleName)->replace('_', ' ')->title(),
        ]);

        return User::query()->create([
            'name' => 'Audit User',
            'first_name' => 'Audit',
            'last_name' => 'User',
            'email' => "{$roleName}@example.com",
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }
}
