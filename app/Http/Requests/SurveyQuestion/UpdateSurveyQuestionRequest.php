<?php

namespace App\Http\Requests\SurveyQuestion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'section' => [
                'nullable',
                'string',
                'max:150',
            ],

            'question' => [
                'sometimes',
                'required',
                'string',
                'max:2000',
            ],

            'question_type' => [
                'sometimes',
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
