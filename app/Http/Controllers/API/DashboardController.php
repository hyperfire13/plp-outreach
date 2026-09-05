<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'message' => 'Dashboard retrieved successfully.',
            'data' => $service->get($request->user()),
        ]);
    }
}
