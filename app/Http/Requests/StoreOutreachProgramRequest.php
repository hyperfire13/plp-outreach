<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutreachProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        // later:
        // return $this->user()->can('create', OutreachProgram::class);
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255',
                'unique:outreach_programs,name'
            ],

            'category' => [
                'required',
                'string',
                'max:100'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'typical_budget' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'typical_duration_days' => [
                'nullable',
                'integer',
                'min:1'
            ],

            'is_active' => [
                'required',
                'boolean'
            ]

        ];
    }

    public function messages(): array
    {
        return [

            'name.required' => 'Program name is required.',

            'name.unique' => 'Program name already exists.',

            'category.required' => 'Category is required.',

            'typical_budget.numeric' => 'Budget must be numeric.',

            'typical_duration_days.integer' => 'Duration must be a whole number.'

        ];
    }
}
