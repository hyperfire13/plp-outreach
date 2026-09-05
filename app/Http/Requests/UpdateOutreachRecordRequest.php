<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutreachRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('outreach_record');

        return $record !== null && $this->user()->can('update', $record);
    }

    public function rules(): array
    {
        return [

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

            'execution_date' => [
                'required',
                'date',
                'before_or_equal:today',
                Rule::unique('outreach_records', 'execution_date')
                    ->where(fn ($query) => $query
                        ->where('community_id', $this->input('community_id'))
                        ->where('outreach_program_id', $this->input('outreach_program_id'))
                        ->where('college_id', $this->input('college_id')))
                    ->ignore($this->route('outreach_record')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'execution_date.unique' => 'An accomplishment record already exists for this community, program, college, and date.',
            'execution_date.before_or_equal' => 'Execution date cannot be in the future.',
        ];
    }
}
