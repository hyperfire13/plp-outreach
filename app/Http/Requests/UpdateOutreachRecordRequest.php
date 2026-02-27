<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutreachRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role->name, [
            'super_admin',
            'college_admin'
        ]);
    }

    public function rules(): array
    {
        return [

            'community_id' => [
                'required',
                'integer',
                Rule::exists('communities', 'id')
            ],

            'outreach_program_id' => [
                'required',
                'integer',
                Rule::exists('outreach_programs', 'id')
            ],

            'college_id' => [
                'required',
                'integer',
                Rule::exists('colleges', 'id')
            ],

            'budget_used' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],

            'volunteers_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:100000'
            ],

            'impact_score' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],

            'success_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],

            'satisfaction_rating' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5'
            ],

            'execution_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
        ];
    }
}