<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EngagementRecord\RejectEngagementRecordRequest;
use App\Http\Requests\EngagementRecord\StoreEngagementRecordRequest;
use App\Http\Requests\EngagementRecord\UpdateEngagementRecordRequest;
use App\Models\EngagementRecord;
use App\Services\EngagementRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EngagementRecordController extends Controller
{
    public function __construct(
        private readonly EngagementRecordService $service
    ) {
        $this->authorizeResource(
            EngagementRecord::class,
            'engagement_record'
        );
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
            'status' => [
                'nullable',
                Rule::in(EngagementRecord::STATUSES),
            ],
            'engagement_type' => [
                'nullable',
                Rule::in(EngagementRecord::TYPES),
            ],
            'sdg' => ['nullable', Rule::in(EngagementRecord::SDGS)],
            'source_type' => [
                'nullable',
                Rule::in(EngagementRecord::SOURCE_TYPES),
            ],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'message' => 'Engagement records retrieved successfully.',
            'data' => $this->service->paginate(
                $request->user(),
                $filters
            ),
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EngagementRecord::class);

        return response()->json([
            'message' => 'Engagement options retrieved successfully.',
            'data' => $this->service->options($request->user()),
        ]);
    }

    public function store(
        StoreEngagementRecordRequest $request
    ): JsonResponse {
        $records = $this->service->storeMany(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => $records->count() === 1
                ? 'Engagement record created successfully.'
                : "{$records->count()} engagement records created successfully.",
            'data' => $records,
        ], 201);
    }

    public function show(EngagementRecord $engagementRecord): JsonResponse
    {
        return response()->json([
            'message' => 'Engagement record retrieved successfully.',
            'data' => $this->service->find($engagementRecord),
        ]);
    }

    public function update(
        UpdateEngagementRecordRequest $request,
        EngagementRecord $engagementRecord
    ): JsonResponse {
        $record = $this->service->update(
            $request->user(),
            $engagementRecord,
            $request->validated()
        );

        return response()->json([
            'message' => 'Engagement record updated successfully.',
            'data' => $record,
        ]);
    }

    public function destroy(
        EngagementRecord $engagementRecord
    ): JsonResponse {
        $this->service->delete($engagementRecord);

        return response()->json([
            'message' => 'Engagement record deleted successfully.',
        ]);
    }

    public function submit(
        Request $request,
        EngagementRecord $engagementRecord
    ): JsonResponse {
        $this->authorize('submit', $engagementRecord);

        return response()->json([
            'message' => 'Engagement record submitted successfully.',
            'data' => $this->service->submit($engagementRecord),
        ]);
    }

    public function approve(
        Request $request,
        EngagementRecord $engagementRecord
    ): JsonResponse {
        $this->authorize('approve', $engagementRecord);

        return response()->json([
            'message' => 'Engagement record approved successfully.',
            'data' => $this->service->approve(
                $engagementRecord,
                $request->user()
            ),
        ]);
    }

    public function reject(
        RejectEngagementRecordRequest $request,
        EngagementRecord $engagementRecord
    ): JsonResponse {
        return response()->json([
            'message' => 'Engagement record rejected successfully.',
            'data' => $this->service->reject(
                $engagementRecord,
                $request->user(),
                $request->validated('validation_remarks')
            ),
        ]);
    }
}
