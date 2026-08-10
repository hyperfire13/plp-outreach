<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyResponse\StoreSurveyResponseRequest;
use App\Http\Requests\SurveyResponse\UpdateSurveyResponseRequest;
use App\Models\SurveyResponse;
use App\Services\SurveyResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SurveyResponseController extends Controller
{
    public function __construct(
        private readonly SurveyResponseService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Survey responses retrieved successfully.',
            'data' => $this->service->paginate(
                $request->only([
                    'search',
                    'community_id',
                    'survey_template_id',
                    'status',
                    'year',
                    'month',
                    'per_page',
                ])
            ),
        ]);
    }

    public function store(
        StoreSurveyResponseRequest $request
    ): JsonResponse {
        try {
            $surveyResponse = $this->service->store(
                $request->validated(),
                $request->user()?->id
            );

            return response()->json([
                'message' => 'Survey response saved successfully.',
                'data' => $surveyResponse,
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to save the survey response.',
            ], 500);
        }
    }

    public function show(
        SurveyResponse $surveyResponse
    ): JsonResponse {
        return response()->json([
            'message' => 'Survey response retrieved successfully.',
            'data' => $this->service->find($surveyResponse),
        ]);
    }

    public function update(
        UpdateSurveyResponseRequest $request,
        SurveyResponse $surveyResponse
    ): JsonResponse {
        try {
            $surveyResponse = $this->service->update(
                $surveyResponse,
                $request->validated(),
                $request->user()?->id
            );

            return response()->json([
                'message' => 'Survey response updated successfully.',
                'data' => $surveyResponse,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update the survey response.',
            ], 500);
        }
    }

    public function destroy(
        SurveyResponse $surveyResponse
    ): JsonResponse {
        $this->service->delete($surveyResponse);

        return response()->json([
            'message' => 'Survey response deleted successfully.',
        ]);
    }
}
