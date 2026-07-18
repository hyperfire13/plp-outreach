<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutreachProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'outreach_program_id' => [
                'required',
                'integer',
                Rule::exists('outreach_programs', 'id')
                    ->whereNull('deleted_at'),
            ],

            'college_id' => [
                'required',
                'integer',
                'exists:colleges,id',
            ],

            'coordinator_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'objectives' => [
                'nullable',
                'string',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'proposed_budget' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'expected_beneficiaries' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    'draft',
                    'submitted',
                    'under_review',
                    'returned',
                    'department_approved',
                    'approved',
                    'ongoing',
                    'completed',
                    'cancelled',
                    'rejected',
                ]),
            ],
        ];
    }
}
