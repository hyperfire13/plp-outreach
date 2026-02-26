<?php

namespace App\Http\Controllers\API;

use App\Models\OutreachRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\OutreachRecordService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutreachRecordRequest;
use App\Http\Requests\UpdateOutreachRecordRequest;

class OutreachRecordController extends Controller
{
    public function __construct(private OutreachRecordService $service)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->paginate()
        );
    }

    public function store(StoreOutreachRecordRequest $request): JsonResponse
    {
        $record = $this->service->store($request->validated());

        return response()->json($record, 201);
    }

    public function show(OutreachRecord $outreach_record): JsonResponse
    {
        return response()->json(
            $outreach_record->load(['community','outreachProgram','college'])
        );
    }

    public function update(
        UpdateOutreachRecordRequest $request,
        OutreachRecord $outreach_record
    ): JsonResponse {
        return response()->json(
            $this->service->update($outreach_record, $request->validated())
        );
    }

    public function destroy(OutreachRecord $outreach_record): JsonResponse
    {
        $this->service->delete($outreach_record);

        return response()->json([
            'message' => 'Outreach record deleted successfully.'
        ]);
    }
}