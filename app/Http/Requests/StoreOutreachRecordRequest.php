<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutreachRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\OutreachRecord::class);
    }

    public function rules(): array
    {
        return [

            /* ===============================
               RELATION INTEGRITY
            =============================== */

            'community_id' => [
                'required',
                'integer',
                Rule::exists('communities', 'id'),
            ],

            'outreach_program_id' => [
                'required',
                'integer',
                Rule::exists('outreach_programs', 'id'),
            ],

            'college_id' => [
                'required',
                'integer',
                Rule::exists('colleges', 'id'),
            ],

            /* ===============================
               NUMERIC FEATURES (ML SAFE)
            =============================== */

            'budget_used' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'volunteers_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:100000',
            ],

            'impact_score' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'success_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'satisfaction_rating' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5',
            ],

            /* ===============================
               DATE VALIDATION
            =============================== */

            'execution_date' => [
                'required',
                'date',
                'before_or_equal:today',
                Rule::unique('outreach_records', 'execution_date')
                    ->where(fn ($query) => $query
                        ->where('community_id', $this->input('community_id'))
                        ->where('outreach_program_id', $this->input('outreach_program_id'))
                        ->where('college_id', $this->input('college_id'))),
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'community_id.required' => 'Community selection is required.',
            'community_id.exists' => 'Selected community does not exist.',

            'outreach_program_id.required' => 'Outreach program is required.',
            'outreach_program_id.exists' => 'Selected outreach program does not exist.',

            'college_id.required' => 'College is required.',
            'college_id.exists' => 'Selected college does not exist.',

            'impact_score.max' => 'Impact score must not exceed 100.',
            'success_rate.max' => 'Success rate must not exceed 100.',
            'satisfaction_rating.max' => 'Satisfaction rating must not exceed 5.',

            'execution_date.before_or_equal' => 'Execution date cannot be in the future.',
            'execution_date.unique' => 'An accomplishment record already exists for this community, program, college, and date.',
        ];
    }
}
