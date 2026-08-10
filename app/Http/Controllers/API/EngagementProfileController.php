<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EngagementRecord;
use App\Models\User;
use App\Services\EngagementProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EngagementProfileController extends Controller
{
    public function __construct(
        private readonly EngagementProfileService $service
    ) {}

    public function me(Request $request): JsonResponse
    {
        return $this->profileResponse($request, $request->user());
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize(
            'viewProfile',
            [EngagementRecord::class, $user]
        );

        return $this->profileResponse($request, $user);
    }

    private function profileResponse(
        Request $request,
        User $user
    ): JsonResponse {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'engagement_type' => [
                'nullable',
                Rule::in(EngagementRecord::TYPES),
            ],
            'sdg' => ['nullable', Rule::in(EngagementRecord::SDGS)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'message' => 'Engagement profile retrieved successfully.',
            'data' => $this->service->get($user, $filters),
        ]);
    }
}
