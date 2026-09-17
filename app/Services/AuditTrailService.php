<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditTrailService
{
    private const SENSITIVE_KEYS = ['password', 'password_confirmation', 'current_password', 'token', 'authorization'];

    public function record(?User $actor, string $action, string $module, string $description, ?Model $auditable = null, ?array $oldValues = null, ?array $newValues = null, ?Request $request = null): AuditLog
    {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id, 'action' => $action, 'module' => $module,
            'auditable_type' => $auditable?->getMorphClass(), 'auditable_id' => $auditable?->getKey(),
            'description' => $description, 'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues), 'ip_address' => $request?->ip(),
            'request_id' => $request?->attributes->get('request_id'),
            'route_name' => $request?->route()?->getName(), 'http_method' => $request?->method(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 1000),
        ]);
    }

    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }
        array_walk_recursive($values, function (&$value, $key): void {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $value = '[REDACTED]';
            }
        });

        return $values;
    }
}
