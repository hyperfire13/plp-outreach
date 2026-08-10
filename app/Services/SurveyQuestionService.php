<?php

namespace App\Services;

use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyQuestionService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        return SurveyQuestion::query()
            ->with([
                'template:id,title,version,status',
            ])
            ->withCount('answers')
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['survey_template_id'] ?? null),
                fn ($query) => $query->where(
                    'survey_template_id',
                    $filters['survey_template_id']
                )
            )
            ->when(
                filled($filters['section'] ?? null),
                fn ($query) => $query->where(
                    'section',
                    $filters['section']
                )
            )
            ->when(
                filled($filters['is_active'] ?? null),
                fn ($query) => $query->where(
                    'is_active',
                    filter_var(
                        $filters['is_active'],
                        FILTER_VALIDATE_BOOLEAN
                    )
                )
            )
            ->orderBy('survey_template_id')
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function find(
        SurveyQuestion $surveyQuestion
    ): SurveyQuestion {
        return $surveyQuestion->load([
            'template:id,title,version,status',
        ])->loadCount('answers');
    }

    public function store(array $data): SurveyQuestion
    {
        return DB::transaction(function () use ($data) {
            $template = SurveyTemplate::findOrFail(
                $data['survey_template_id']
            );

            if ($template->status === SurveyTemplate::STATUS_ARCHIVED) {
                throw ValidationException::withMessages([
                    'survey_template_id' => [
                        'Questions cannot be added to an archived template.',
                    ],
                ]);
            }

            if (
                !array_key_exists('sort_order', $data)
            ) {
                $data['sort_order'] =
                    ((int) $template->questions()->max('sort_order')) + 1;
            }

            return SurveyQuestion::create($data);
        });
    }

    public function update(
        SurveyQuestion $surveyQuestion,
        array $data
    ): SurveyQuestion {
        return DB::transaction(
            function () use ($surveyQuestion, $data) {
                if (
                    array_key_exists('question_type', $data) &&
                    $data['question_type'] !==
                    $surveyQuestion->question_type &&
                    $surveyQuestion->answers()->exists()
                ) {
                    throw ValidationException::withMessages([
                        'question_type' => [
                            'The question type cannot be changed because this question already has answers.',
                        ],
                    ]);
                }

                $finalType = $data['question_type']
                    ?? $surveyQuestion->question_type;

                if (
                    !in_array(
                        $finalType,
                        ['select', 'radio', 'checkbox'],
                        true
                    )
                ) {
                    $data['options'] = null;
                }

                $surveyQuestion->update($data);

                return $surveyQuestion->refresh();
            }
        );
    }

    public function delete(
        SurveyQuestion $surveyQuestion
    ): void {
        if ($surveyQuestion->answers()->exists()) {
            throw ValidationException::withMessages([
                'question' => [
                    'This question cannot be deleted because it already has answers. Deactivate it instead.',
                ],
            ]);
        }

        DB::transaction(function () use ($surveyQuestion) {
            $surveyQuestion->delete();
        });
    }
}
