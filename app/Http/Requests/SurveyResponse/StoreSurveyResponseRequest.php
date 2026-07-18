<?php

namespace App\Http\Requests\SurveyResponse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'community_id' => [
                'required',
                'integer',
                Rule::exists('communities', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'survey_template_id' => [
                'required',
                'integer',
                Rule::exists('survey_templates', 'id')
                    ->whereNull('deleted_at'),
            ],

            'survey_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'academic_department' => [
                'nullable',
                'string',
                'max:255',
            ],

            'conducted_by' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    'draft',
                    'submitted',
                ]),
            ],

            'suggested_outreach_program' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'answers' => [
                'sometimes',
                'array',
            ],

            'answers.*.survey_question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('survey_questions', 'id')
                    ->whereNull('deleted_at'),
            ],

            'answers.*.value' => [
                'nullable',
            ],

            'priority_needs' => [
                'sometimes',
                'array',
                'max:3',
            ],

            'priority_needs.*.need' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'priority_needs.*.priority_rank' => [
                'required',
                'integer',
                'between:1,3',
                'distinct',
            ],

            'priority_needs.*.description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
