<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyTemplate\StoreSurveyTemplateRequest;
use App\Http\Requests\SurveyTemplate\UpdateSurveyTemplateRequest;
use App\Models\SurveyTemplate;
use App\Services\SurveyTemplateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SurveyTemplateController extends Controller
{
    public function __construct(
        private readonly SurveyTemplateService $service
    ) {}

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
            $surveyTemplate = $this->service->store(
                $request->validated(),
                $request->user()?->id
            );

            return response()->json([
                'message' => 'Survey template created successfully.',
                'data' => $surveyTemplate,
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
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

    public function downloadPdf(
        SurveyTemplate $surveyTemplate
    ): Response {
        $surveyTemplate = $this->service->forPrintableForm(
            $surveyTemplate
        );

        $filename = str($surveyTemplate->title)
            ->slug('-')
            ->limit(70, '')
            ->append('-v'.$surveyTemplate->version.'-blank-form.pdf');

        return Pdf::loadView('pdf.survey-form', [
            'surveyTemplate' => $surveyTemplate,
            'questionsBySection' => $surveyTemplate
                ->activeQuestions
                ->groupBy(fn ($question) => $question->section ?: 'General'),
            'generatedAt' => now(),
        ])
            ->setPaper('a4', 'portrait')
            ->download((string) $filename);
    }

    public function update(
        UpdateSurveyTemplateRequest $request,
        SurveyTemplate $surveyTemplate
    ): JsonResponse {
        try {
            $surveyTemplate = $this->service->update(
                $surveyTemplate,
                $request->validated()
            );

            return response()->json([
                'message' => 'Survey template updated successfully.',
                'data' => $surveyTemplate,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
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
