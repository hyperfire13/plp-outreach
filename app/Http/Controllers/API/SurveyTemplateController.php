<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyTemplate\StoreSurveyTemplateRequest;
use App\Http\Requests\SurveyTemplate\UpdateSurveyTemplateRequest;
use App\Models\SurveyTemplate;
use App\Services\SurveyTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SurveyTemplateController extends Controller
{
    public function __construct(
        private readonly SurveyTemplateService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Survey templates retrieved successfully.',
            'data' => $this->service->paginate(
                $request->only([
                    'search',
                    'status',
                    'per_page',
                ])
            ),
        ]);
    }

    public function store(
        StoreSurveyTemplateRequest $request
    ): JsonResponse {
        try {
            $template = $this->service->store(
                $request->validated(),
                $request->user()?->id
            );

            return response()->json([
                'message' => 'Survey template created successfully.',
                'data' => $template,
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to create the survey template.',
            ], 500);
        }
    }

    public function show(
        SurveyTemplate $surveyTemplate
    ): JsonResponse {
        return response()->json([
            'message' => 'Survey template retrieved successfully.',
            'data' => $this->service->find($surveyTemplate),
        ]);
    }

    public function update(
        UpdateSurveyTemplateRequest $request,
        SurveyTemplate $surveyTemplate
    ): JsonResponse {
        try {
            $template = $this->service->update(
                $surveyTemplate,
                $request->validated()
            );

            return response()->json([
                'message' => 'Survey template updated successfully.',
                'data' => $template,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update the survey template.',
            ], 500);
        }
    }

    public function destroy(
        SurveyTemplate $surveyTemplate
    ): JsonResponse {
        $this->service->delete($surveyTemplate);

        return response()->json([
            'message' => 'Survey template deleted successfully.',
        ]);
    }
}
