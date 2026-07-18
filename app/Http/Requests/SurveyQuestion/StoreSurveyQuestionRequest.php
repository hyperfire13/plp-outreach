<?php

namespace App\Http\Requests\SurveyQuestion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'survey_template_id' => [
                'required',
                'integer',
                Rule::exists('survey_templates', 'id')
                    ->whereNull('deleted_at'),
            ],

            'section' => [
                'nullable',
                'string',
                'max:150',
            ],

            'question' => [
                'required',
                'string',
                'max:2000',
            ],

            'question_type' => [
                'required',
                Rule::in([
                    'text',
                    'textarea',
                    'number',
                    'date',
                    'select',
                    'radio',
                    'checkbox',
                    'boolean',
                ]),
            ],

            'options' => [
                'nullable',
                'array',
                'required_if:question_type,select,radio,checkbox',
            ],

            'options.*' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'is_required' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'help_text' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
