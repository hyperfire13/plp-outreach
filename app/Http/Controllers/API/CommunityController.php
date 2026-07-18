<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommunityRequest;
use App\Http\Requests\Community\UpdateCommunityRequest;
use App\Models\Community;
use App\Services\CommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CommunityController extends Controller
{
    public function __construct(
        private readonly CommunityService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Communities retrieved successfully.',
            'data' => $this->service->paginate(
                $request->only([
                    'search',
                    'is_active',
                    'community_type',
                    'per_page',
                ])
            ),
        ]);
    }

    public function all(): JsonResponse
    {
        return response()->json([
            'message' => 'Active communities retrieved successfully.',
            'data' => $this->service->allActive(),
        ]);
    }

    public function store(
        StoreCommunityRequest $request
    ): JsonResponse {
        try {
            $community = $this->service->store(
                $request->validated(),
                $request->user()?->id
            );

            return response()->json([
                'message' => 'Community created successfully.',
                'data' => $community,
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to create the community.',
            ], 500);
        }
    }

    public function show(
        Community $community
    ): JsonResponse {
        return response()->json([
            'message' => 'Community retrieved successfully.',
            'data' => $this->service->find($community),
        ]);
    }

    public function update(
        UpdateCommunityRequest $request,
        Community $community
    ): JsonResponse {
        try {
            $community = $this->service->update(
                $community,
                $request->validated()
            );

            return response()->json([
                'message' => 'Community updated successfully.',
                'data' => $community,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update the community.',
            ], 500);
        }
    }

    public function destroy(
        Community $community
    ): JsonResponse {
        $this->service->delete($community);

        return response()->json([
            'message' => 'Community deleted successfully.',
        ]);
    }
}
