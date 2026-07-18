<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\OutreachProject;
use App\Http\Controllers\Controller;
use App\Services\OutreachProjectService;
use App\Http\Requests\StoreOutreachProjectRequest;
use App\Http\Requests\UpdateOutreachProjectRequest;

class OutreachProjectController extends Controller
{
    public function __construct(
        private OutreachProjectService $service
    ) {
        $this->authorizeResource(
            OutreachProject::class,
            'outreach_project'
        );
    }

    public function all(): JsonResponse
    {
        return response()->json(
            $this->service->all()
        );
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        return response()->json(
            $this->service->paginate(
                $request->user(),
                $filters
            )
        );
    }

    public function store(
        StoreOutreachProjectRequest $request
    ): JsonResponse {
        $project = $this->service->store(
            $request->user(),
            $request->validated()
        );

        return response()->json($project, 201);
    }

    public function show(
        OutreachProject $outreachProject
    ): JsonResponse {
        return response()->json(
            $outreachProject->load([
                'program:id,name,category',
                'college:id,name',
                'creator',
                'coordinator',
                'members',
                'communityPartners',
                'evaluators',
            ])
        );
    }

    public function update(
        UpdateOutreachProjectRequest $request,
        OutreachProject $outreachProject
    ): JsonResponse {
        return response()->json(
            $this->service->update(
                $outreachProject,
                $request->validated()
            )
        );
    }

    public function destroy(
        OutreachProject $outreachProject
    ): JsonResponse {
        $this->service->delete($outreachProject);

        return response()->json([
            'message' => 'Outreach project deleted successfully.',
        ]);
    }
}
