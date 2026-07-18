<?php

namespace App\Services;

use App\Models\SurveyTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyTemplateService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        return SurveyTemplate::query()
            ->withCount([
                'questions',
                'responses',
            ])
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where(
                    'status',
                    $filters['status']
                )
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(
        SurveyTemplate $surveyTemplate
    ): SurveyTemplate {
        return $surveyTemplate->load([
            'creator:id,first_name,last_name,email',
            'questions' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
        ])->loadCount('responses');
    }

    public function store(
        array $data,
        ?int $userId
    ): SurveyTemplate {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;

            $data['published_at'] =
                ($data['status'] ?? null) ===
                SurveyTemplate::STATUS_PUBLISHED
                    ? now()
                    : null;

            if (($data['is_default'] ?? false) === true) {
                SurveyTemplate::query()->update([
                    'is_default' => false,
                ]);
            }

            return SurveyTemplate::create($data);
        });
    }

    public function update(
        SurveyTemplate $surveyTemplate,
        array $data
    ): SurveyTemplate {
        return DB::transaction(
            function () use ($surveyTemplate, $data) {
                if (
                    $surveyTemplate->responses()->exists() &&
                    (
                        array_key_exists('title', $data) ||
                        array_key_exists('version', $data)
                    )
                ) {
                    throw ValidationException::withMessages([
                        'template' => [
                            'The title and version cannot be changed because this template already has survey responses.',
                        ],
                    ]);
                }

                if (($data['is_default'] ?? false) === true) {
                    SurveyTemplate::query()
                        ->whereKeyNot($surveyTemplate->id)
                        ->update([
                            'is_default' => false,
                        ]);
                }

                if (
                    ($data['status'] ?? null) ===
                    SurveyTemplate::STATUS_PUBLISHED &&
                    !$surveyTemplate->published_at
                ) {
                    $data['published_at'] = now();
                }

                if (
                    ($data['status'] ?? null) ===
                    SurveyTemplate::STATUS_DRAFT
                ) {
                    $data['published_at'] = null;
                }

                $surveyTemplate->update($data);

                return $surveyTemplate->refresh();
            }
        );
    }

    public function delete(
        SurveyTemplate $surveyTemplate
    ): void {
        if ($surveyTemplate->responses()->exists()) {
            throw ValidationException::withMessages([
                'template' => [
                    'This template cannot be deleted because it already has survey responses. Archive it instead.',
                ],
            ]);
        }

        DB::transaction(function () use ($surveyTemplate) {
            $surveyTemplate->questions()->delete();
            $surveyTemplate->delete();
        });
    }
}
