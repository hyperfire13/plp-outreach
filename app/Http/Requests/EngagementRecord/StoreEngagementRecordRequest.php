<?php

namespace App\Http\Requests\EngagementRecord;

use App\Models\EngagementRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngagementRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EngagementRecord::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'outreach_project_id' => [
                'nullable',
                'integer',
                Rule::exists('outreach_projects', 'id')
                    ->whereNull('deleted_at'),
            ],
            'community_id' => [
                'nullable',
                'integer',
                Rule::exists('communities', 'id')
                    ->whereNull('deleted_at'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'engagement_type' => [
                'required',
                Rule::in(EngagementRecord::TYPES),
            ],
            'participation_role' => [
                'required',
                'string',
                'max:150',
            ],
            'activity_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'service_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'sdg' => [
                'nullable',
                Rule::in(EngagementRecord::SDGS),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'source_type' => [
                'sometimes',
                Rule::in(EngagementRecord::SOURCE_TYPES),
            ],
            'status' => [
                'sometimes',
                Rule::in([
                    EngagementRecord::STATUS_DRAFT,
                    EngagementRecord::STATUS_SUBMITTED,
                ]),
            ],
        ];
    }
}
