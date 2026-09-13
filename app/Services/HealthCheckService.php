<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckService
{
    public function readiness(): array
    {
        $checks = [
            'database' => $this->databaseIsReady(),
            'cache' => $this->cacheIsReady(),
            'storage' => is_writable(storage_path('framework')),
        ];

        return [
            'ready' => ! in_array(false, $checks, true),
            'checks' => $checks,
        ];
    }

    private function databaseIsReady(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function cacheIsReady(): bool
    {
        $key = 'health:ready:'.bin2hex(random_bytes(8));

        try {
            Cache::put($key, 'ready', 10);

            return Cache::pull($key) === 'ready';
        } catch (Throwable) {
            return false;
        }
    }
}
