<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __invoke(HealthCheckService $service): JsonResponse
    {
        $health = $service->readiness();

        return response()->json([
            'status' => $health['ready'] ? 'ready' : 'unavailable',
            'checks' => $health['checks'],
            'timestamp' => now()->toIso8601String(),
        ], $health['ready'] ? 200 : 503)->header(
            'Cache-Control',
            'no-store, private'
        );
    }
}
