<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectApplicationTemplate\StoreProjectApplicationTemplateRequest;
use App\Http\Requests\ProjectApplicationTemplate\UpdateProjectApplicationTemplateRequest;
use App\Models\ProjectApplicationTemplate;
use App\Services\ProjectApplicationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectApplicationTemplateController extends Controller
{
    public function __construct(private readonly ProjectApplicationTemplateService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(ProjectApplicationTemplate::STATUSES)],
            'phase' => ['nullable', Rule::in(ProjectApplicationTemplate::PHASES)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'message' => 'Project application templates retrieved successfully.',
            'data' => $this->service->paginate($filters),
        ]);
    }

    public function store(StoreProjectApplicationTemplateRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Project application template created successfully.',
            'data' => $this->service->store($request->validated(), $request->user()->id),
        ], 201);
    }

    public function show(ProjectApplicationTemplate $projectApplicationTemplate): JsonResponse
    {
        return response()->json([
            'message' => 'Project application template retrieved successfully.',
            'data' => $projectApplicationTemplate->loadCount('responses'),
        ]);
    }

    public function update(
        UpdateProjectApplicationTemplateRequest $request,
        ProjectApplicationTemplate $projectApplicationTemplate
    ): JsonResponse {
        return response()->json([
            'message' => 'Project application template updated successfully.',
            'data' => $this->service->update($projectApplicationTemplate, $request->validated()),
        ]);
    }

    public function destroy(ProjectApplicationTemplate $projectApplicationTemplate): JsonResponse
    {
        abort_unless(
            in_array(request()->user()?->role_name, config('role_access.proposal_template_managers', []), true),
            403
        );

        $this->service->delete($projectApplicationTemplate);

        return response()->json(['message' => 'Project application template deleted successfully.']);
    }
}
