<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutreachProgramRequest;
use App\Http\Requests\UpdateOutreachProgramRequest;
use App\Models\OutreachProgram;
use App\Services\OutreachProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutreachProgramController extends Controller
{
    public function __construct(
        private OutreachProgramService $service
    ) {
        $this->authorizeResource(OutreachProgram::class, 'outreach_program');
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->paginate(
                $request->integer('per_page', 10)
            )
        );
    }

    public function store(
        StoreOutreachProgramRequest $request
    ): JsonResponse {

        $program = $this->service->store(
            $request->user(),
            $request->validated()
        );

        return response()->json(
            $program,
            201
        );

    }

    public function show(
        OutreachProgram $outreachProgram
    ): JsonResponse {

        return response()->json(
            $outreachProgram->load(
                'creator:id,first_name,middle_name,last_name'
            )
        );

    }

    public function update(
        UpdateOutreachProgramRequest $request,
        OutreachProgram $outreachProgram
    ): JsonResponse {

        return response()->json(

            $this->service->update(
                $outreachProgram,
                $request->validated()
            )

        );

    }

    public function destroy(
        OutreachProgram $outreachProgram
    ): JsonResponse {

        $this->service->delete(
            $outreachProgram
        );

        return response()->json([
            'message' => 'Outreach Program deleted successfully.'
        ]);

    }
    public function all(): JsonResponse
    {
        return response()->json(
            $this->service->all()
        );
}
}
