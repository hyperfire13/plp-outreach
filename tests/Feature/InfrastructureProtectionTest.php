<?php

namespace Tests\Feature;

use App\Services\HealthCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class InfrastructureProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_by_email_and_ip(): void
    {
        config([
            'rate_limits.api_per_minute' => 100,
            'rate_limits.login_per_minute' => 2,
            'rate_limits.login_ip_per_minute' => 100,
        ]);

        $payload = [
            'email' => 'unknown@example.com',
            'password' => 'incorrect-password',
        ];

        $this->postJson('/api/v1/login', $payload)->assertUnprocessable();
        $this->postJson('/api/v1/login', $payload)->assertUnprocessable();
        $this->postJson('/api/v1/login', $payload)
            ->assertTooManyRequests()
            ->assertHeader('retry-after');
    }

    public function test_general_api_requests_are_rate_limited(): void
    {
        config(['rate_limits.api_per_minute' => 2]);

        $this->getJson('/api/v1/not-a-real-endpoint')->assertNotFound();
        $this->getJson('/api/v1/not-a-real-endpoint')->assertNotFound();
        $this->getJson('/api/v1/not-a-real-endpoint')
            ->assertTooManyRequests()
            ->assertHeader('retry-after');
    }

    public function test_liveness_and_readiness_endpoints_are_available(): void
    {
        $this->get('/up')->assertOk();

        $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.cache', true)
            ->assertJsonPath('checks.storage', true);
    }

    public function test_readiness_returns_service_unavailable_when_a_dependency_fails(): void
    {
        $this->mock(
            HealthCheckService::class,
            function (MockInterface $mock): void {
                $mock->shouldReceive('readiness')->once()->andReturn([
                    'ready' => false,
                    'checks' => [
                        'database' => false,
                        'cache' => true,
                        'storage' => true,
                    ],
                ]);
            }
        );

        $this->getJson('/health/ready')
            ->assertServiceUnavailable()
            ->assertHeader('cache-control', 'no-store, private')
            ->assertJsonPath('status', 'unavailable')
            ->assertJsonPath('checks.database', false);
    }
}
