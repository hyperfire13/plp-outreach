<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriorityNeed;
use App\Services\PriorityNeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriorityNeedController extends Controller
{
    public function validateNeed(Request $request, PriorityNeed $priorityNeed): JsonResponse
    {
        $priorityNeed->update(['status' => 'validated', 'validated_by' => $request->user()->id, 'validated_at' => now(), 'validation_remarks' => $request->input('remarks')]);

        return response()->json(['message' => 'Priority need validated successfully.', 'data' => $priorityNeed->load('validator')]);
    }

    public function rejectNeed(Request $request, PriorityNeed $priorityNeed): JsonResponse
    {
        $data = $request->validate(['remarks' => ['required', 'string', 'max:5000']]);
        $priorityNeed->update(['status' => 'rejected', 'validated_by' => $request->user()->id, 'validated_at' => now(), 'validation_remarks' => $data['remarks']]);

        return response()->json(['message' => 'Priority need rejected successfully.', 'data' => $priorityNeed->load('validator')]);
    }

    public function __construct(
        private readonly PriorityNeedService $service
    ) {}

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
