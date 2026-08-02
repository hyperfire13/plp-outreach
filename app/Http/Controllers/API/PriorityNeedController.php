<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PriorityNeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriorityNeedController extends Controller
{
    public function __construct(
        private readonly PriorityNeedService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Priority needs retrieved successfully.',
            'data' => $this->service->paginate(
                $request->only([
                    'community_id',
                    'need',
                    'priority_rank',
                    'year',
                    'month',
                    'per_page',
                ])
            ),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Priority needs summary retrieved successfully.',
            'data' => $this->service->summary(
                $request->only([
                    'community_id',
                    'year',
                    'month',
                ])
            ),
        ]);
    }
}
