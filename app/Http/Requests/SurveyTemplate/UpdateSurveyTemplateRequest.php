<?php

namespace App\Http\Requests\SurveyTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSurveyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'version' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    'draft',
                    'published',
                    'archived',
                ]),
            ],

            'is_default' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
