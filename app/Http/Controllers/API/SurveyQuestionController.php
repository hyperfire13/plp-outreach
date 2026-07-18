<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyQuestion\StoreSurveyQuestionRequest;
use App\Http\Requests\SurveyQuestion\UpdateSurveyQuestionRequest;
use App\Models\SurveyQuestion;
use App\Services\SurveyQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SurveyQuestionController extends Controller
{
    public function __construct(
        private readonly SurveyQuestionService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Survey questions retrieved successfully.',
            'data' => $this->service->paginate(
                $request->only([
                    'search',
                    'survey_template_id',
                    'section',
                    'is_active',
                    'per_page',
                ])
            ),
        ]);
    }

    public function store(
        StoreSurveyQuestionRequest $request
    ): JsonResponse {
        try {
            $question = $this->service->store(
                $request->validated()
            );

            return response()->json([
                'message' => 'Survey question created successfully.',
                'data' => $question,
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to create the survey question.',
            ], 500);
        }
    }

    public function show(
        SurveyQuestion $surveyQuestion
    ): JsonResponse {
        return response()->json([
            'message' => 'Survey question retrieved successfully.',
            'data' => $this->service->find($surveyQuestion),
        ]);
    }

    public function update(
        UpdateSurveyQuestionRequest $request,
        SurveyQuestion $surveyQuestion
    ): JsonResponse {
        try {
            $question = $this->service->update(
                $surveyQuestion,
                $request->validated()
            );

            return response()->json([
                'message' => 'Survey question updated successfully.',
                'data' => $question,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update the survey question.',
            ], 500);
        }
    }

    public function destroy(
        SurveyQuestion $surveyQuestion
    ): JsonResponse {
        $this->service->delete($surveyQuestion);

        return response()->json([
            'message' => 'Survey question deleted successfully.',
        ]);
    }
}
