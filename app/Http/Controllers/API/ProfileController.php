<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $service
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'data' => $this->service->get($request->user()),
        ]);
    }

    public function update(
        UpdateProfileRequest $request
    ): JsonResponse {
        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $this->service->update(
                $request->user(),
                $request->validated()
            ),
        ]);
    }

    public function updatePassword(
        UpdatePasswordRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $this->service->updatePassword(
            $request->user(),
            $data['current_password'],
            $data['password']
        );

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
