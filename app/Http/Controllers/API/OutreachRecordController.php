<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutreachRecordRequest;
use App\Http\Requests\UpdateOutreachRecordRequest;
use App\Models\OutreachRecord;
use App\Services\OutreachRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutreachRecordController extends Controller
{
    public function __construct(private readonly OutreachRecordService $service)
    {
        $this->authorizeResource(OutreachRecord::class, 'outreach_record');
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
            'community_id' => ['nullable', 'integer', 'exists:communities,id'],
            'outreach_program_id' => ['nullable', 'integer', 'exists:outreach_programs,id'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'message' => 'Accomplishment records retrieved successfully.',
            'data' => $this->service->paginate($request->user(), $filters),
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OutreachRecord::class);

        return response()->json([
            'message' => 'Accomplishment record options retrieved successfully.',
            'data' => $this->service->options($request->user()),
        ]);
    }

    public function store(StoreOutreachRecordRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Accomplishment record created successfully.',
            'data' => $this->service->store($request->user(), $request->validated()),
        ], 201);
    }

    public function show(OutreachRecord $outreachRecord): JsonResponse
    {
        return response()->json([
            'message' => 'Accomplishment record retrieved successfully.',
            'data' => $this->service->find($outreachRecord),
        ]);
    }

    public function update(UpdateOutreachRecordRequest $request, OutreachRecord $outreachRecord): JsonResponse
    {
        return response()->json([
            'message' => 'Accomplishment record updated successfully.',
            'data' => $this->service->update($request->user(), $outreachRecord, $request->validated()),
        ]);
    }

    public function destroy(OutreachRecord $outreachRecord): JsonResponse
    {
        $this->service->delete($outreachRecord);

        return response()->json(['message' => 'Accomplishment record deleted successfully.']);
    }
}
