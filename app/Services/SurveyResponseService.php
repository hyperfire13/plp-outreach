<?php

namespace App\Services;

use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyResponseService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        return SurveyResponse::query()
            ->with([
                'community:id,name,city',
                'template:id,title,version',
                'creator:id,first_name,last_name',
                'submitter:id,first_name,last_name',
            ])
            ->withCount([
                'answers',
                'priorityNeeds',
            ])
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['community_id'] ?? null),
                fn ($query) => $query->where(
                    'community_id',
                    $filters['community_id']
                )
            )
            ->when(
                filled($filters['survey_template_id'] ?? null),
                fn ($query) => $query->where(
                    'survey_template_id',
                    $filters['survey_template_id']
                )
            )
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where(
                    'status',
                    $filters['status']
                )
            )
            ->when(
                filled($filters['year'] ?? null),
                fn ($query) => $query->whereYear(
                    'survey_date',
                    $filters['year']
                )
            )
            ->when(
                filled($filters['month'] ?? null),
                fn ($query) => $query->whereMonth(
                    'survey_date',
                    $filters['month']
                )
            )
            ->latest('survey_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(
        SurveyResponse $surveyResponse
    ): SurveyResponse {
        return $surveyResponse->load([
            'community',
            'template',
            'creator:id,first_name,last_name,email',
            'submitter:id,first_name,last_name,email',

            'answers' => fn ($query) => $query
                ->with([
                    'question:id,survey_template_id,section,question,question_type,options,is_required,sort_order',
                ])
                ->join(
                    'survey_questions',
                    'survey_questions.id',
                    '=',
                    'survey_answers.survey_question_id'
                )
                ->orderBy('survey_questions.sort_order')
                ->select('survey_answers.*'),

            'priorityNeeds',
        ]);
    }

    public function store(
        array $data,
        ?int $userId
    ): SurveyResponse {
        return DB::transaction(function () use ($data, $userId) {
            $answers = collect($data['answers'] ?? []);
            $priorityNeeds = collect(
                $data['priority_needs'] ?? []
            );

            unset(
                $data['answers'],
                $data['priority_needs']
            );

            $template = $this->validateTemplate(
                (int) $data['survey_template_id']
            );

            $status = $data['status']
                ?? SurveyResponse::STATUS_DRAFT;

            $this->validateAnswersAgainstTemplate(
                $template,
                $answers,
                $status
            );

            $data['created_by'] = $userId;

            if ($status === SurveyResponse::STATUS_SUBMITTED) {
                $data['submitted_by'] = $userId;
                $data['submitted_at'] = now();
            }

            $response = SurveyResponse::create($data);

            $this->syncAnswers(
                $response,
                $template,
                $answers
            );

            $this->syncPriorityNeeds(
                $response,
                $priorityNeeds
            );

            return $this->find($response);
        });
    }

    public function update(
        SurveyResponse $surveyResponse,
        array $data,
        ?int $userId
    ): SurveyResponse {
        if ($surveyResponse->isSubmitted()) {
            throw ValidationException::withMessages([
                'response' => [
                    'Submitted survey responses are locked and cannot be edited.',
                ],
            ]);
        }

        return DB::transaction(
            function () use ($surveyResponse, $data, $userId) {
                $answers = array_key_exists('answers', $data)
                    ? collect($data['answers'])
                    : null;

                $priorityNeeds =
                    array_key_exists('priority_needs', $data)
                        ? collect($data['priority_needs'])
                        : null;

                unset(
                    $data['answers'],
                    $data['priority_needs']
                );

                $templateId = (int) (
                    $data['survey_template_id']
                    ?? $surveyResponse->survey_template_id
                );

                $template = $this->validateTemplate($templateId);

                $status = $data['status']
                    ?? $surveyResponse->status;

                $answersForValidation = $answers
                    ?? $this->existingAnswersAsPayload(
                        $surveyResponse
                    );

                $this->validateAnswersAgainstTemplate(
                    $template,
                    $answersForValidation,
                    $status
                );

                if (
                    $status === SurveyResponse::STATUS_SUBMITTED
                ) {
                    $data['submitted_by'] = $userId;
                    $data['submitted_at'] = now();
                }

                $surveyResponse->update($data);

                if ($answers !== null) {
                    $this->syncAnswers(
                        $surveyResponse,
                        $template,
                        $answers
                    );
                }

                if ($priorityNeeds !== null) {
                    $this->syncPriorityNeeds(
                        $surveyResponse,
                        $priorityNeeds
                    );
                }

                return $this->find(
                    $surveyResponse->refresh()
                );
            }
        );
    }

    public function delete(
        SurveyResponse $surveyResponse
    ): void {
        if ($surveyResponse->isSubmitted()) {
            throw ValidationException::withMessages([
                'response' => [
                    'Submitted survey responses cannot be deleted.',
                ],
            ]);
        }

        DB::transaction(function () use ($surveyResponse) {
            $surveyResponse->answers()->delete();
            $surveyResponse->priorityNeeds()->delete();
            $surveyResponse->delete();
        });
    }

    private function validateTemplate(
        int $templateId
    ): SurveyTemplate {
        $template = SurveyTemplate::query()
            ->with([
                'activeQuestions',
            ])
            ->findOrFail($templateId);

        if ($template->status === SurveyTemplate::STATUS_ARCHIVED) {
            throw ValidationException::withMessages([
                'survey_template_id' => [
                    'Archived survey templates cannot receive new responses.',
                ],
            ]);
        }

        return $template;
    }

    private function validateAnswersAgainstTemplate(
        SurveyTemplate $template,
        Collection $answers,
        string $status
    ): void {
        $questionIds = $answers
            ->pluck('survey_question_id')
            ->map(fn ($id) => (int) $id);

        $validQuestions = $template
            ->activeQuestions
            ->keyBy('id');

        $invalidQuestionIds = $questionIds
            ->filter(
                fn ($questionId) =>
                    !$validQuestions->has($questionId)
            )
            ->values();

        if ($invalidQuestionIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => [
                    'One or more questions do not belong to the selected survey template.',
                ],
                'invalid_question_ids' => $invalidQuestionIds->all(),
            ]);
        }

        foreach ($answers as $index => $answer) {
            $question = $validQuestions->get(
                (int) $answer['survey_question_id']
            );

            $this->validateAnswerValue(
                $question,
                $answer['value'] ?? null,
                "answers.{$index}.value"
            );
        }

        if ($status !== SurveyResponse::STATUS_SUBMITTED) {
            return;
        }

        $answerMap = $answers->keyBy(
            fn ($answer) =>
                (int) $answer['survey_question_id']
        );

        $missingRequired = $template
            ->activeQuestions
            ->where('is_required', true)
            ->filter(function (SurveyQuestion $question) use ($answerMap) {
                $answer = $answerMap->get($question->id);

                if (!$answer) {
                    return true;
                }

                return $this->isEmptyAnswer(
                    $answer['value'] ?? null
                );
            })
            ->pluck('id')
            ->values();

        if ($missingRequired->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => [
                    'All required questions must be answered before submitting the survey.',
                ],
                'missing_required_question_ids' =>
                    $missingRequired->all(),
            ]);
        }
    }

    private function validateAnswerValue(
        SurveyQuestion $question,
        mixed $value,
        string $attribute
    ): void {
        if ($this->isEmptyAnswer($value)) {
            return;
        }

        switch ($question->question_type) {
            case 'number':
                if (!is_numeric($value)) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The answer must be numeric.',
                        ],
                    ]);
                }
                break;

            case 'date':
                if (
                    !is_string($value) ||
                    strtotime($value) === false
                ) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The answer must be a valid date.',
                        ],
                    ]);
                }
                break;

            case 'boolean':
                if (
                    !in_array(
                        $value,
                        [true, false, 0, 1, '0', '1'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The answer must be true or false.',
                        ],
                    ]);
                }
                break;

            case 'checkbox':
                if (!is_array($value)) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The answer must be an array.',
                        ],
                    ]);
                }

                $invalidOptions = collect($value)->diff(
                    $question->options ?? []
                );

                if ($invalidOptions->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The answer contains an invalid option.',
                        ],
                    ]);
                }
                break;

            case 'select':
            case 'radio':
                if (
                    !in_array(
                        $value,
                        $question->options ?? [],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The selected answer is invalid.',
                        ],
                    ]);
                }
                break;

            default:
                if (!is_scalar($value)) {
                    throw ValidationException::withMessages([
                        $attribute => [
                            'The answer must be a valid text value.',
                        ],
                    ]);
                }
        }
    }

    private function syncAnswers(
        SurveyResponse $response,
        SurveyTemplate $template,
        Collection $answers
    ): void {
        $response->answers()->delete();

        if ($answers->isEmpty()) {
            return;
        }

        $questions = $template
            ->activeQuestions
            ->keyBy('id');

        $records = $answers->map(
            function (array $answer) use ($response, $questions) {
                $question = $questions->get(
                    (int) $answer['survey_question_id']
                );

                return array_merge(
                    [
                        'survey_response_id' => $response->id,
                        'survey_question_id' => $question->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    $this->mapAnswerColumns(
                        $question,
                        $answer['value'] ?? null
                    )
                );
            }
        )->all();

        SurveyAnswer::insert($records);
    }

    private function syncPriorityNeeds(
        SurveyResponse $response,
        Collection $priorityNeeds
    ): void {
        $response->priorityNeeds()->delete();

        if ($priorityNeeds->isEmpty()) {
            return;
        }

        $records = $priorityNeeds->map(
            fn (array $need) => [
                'survey_response_id' => $response->id,
                'community_id' => $response->community_id,
                'need' => trim($need['need']),
                'priority_rank' => $need['priority_rank'],
                'description' => $need['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        )->all();

        $response->priorityNeeds()->insert($records);
    }

    private function mapAnswerColumns(
        SurveyQuestion $question,
        mixed $value
    ): array {
        $columns = [
            'answer_text' => null,
            'answer_number' => null,
            'answer_date' => null,
            'answer_boolean' => null,
            'answer_json' => null,
        ];

        if ($this->isEmptyAnswer($value)) {
            return $columns;
        }

        switch ($question->question_type) {
            case 'number':
                $columns['answer_number'] = $value;
                break;

            case 'date':
                $columns['answer_date'] = $value;
                break;

            case 'boolean':
                $columns['answer_boolean'] =
                    filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'checkbox':
                $columns['answer_json'] = json_encode($value);
                break;

            default:
                $columns['answer_text'] = (string) $value;
        }

        return $columns;
    }

    private function existingAnswersAsPayload(
        SurveyResponse $response
    ): Collection {
        return $response
            ->answers()
            ->with('question')
            ->get()
            ->map(function (SurveyAnswer $answer) {
                return [
                    'survey_question_id' =>
                        $answer->survey_question_id,

                    'value' => match (
                        $answer->question->question_type
                    ) {
                        'number' => $answer->answer_number,
                        'date' => optional(
                            $answer->answer_date
                        )->toDateString(),
                        'boolean' => $answer->answer_boolean,
                        'checkbox' => $answer->answer_json,
                        default => $answer->answer_text,
                    },
                ];
            });
    }

    private function isEmptyAnswer(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        return false;
    }
}
