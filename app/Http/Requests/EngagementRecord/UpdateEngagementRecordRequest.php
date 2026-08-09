<?php

namespace App\Http\Requests\EngagementRecord;

use App\Models\EngagementRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngagementRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('engagement_record');

        return $record instanceof EngagementRecord
            && ($this->user()?->can('update', $record) ?? false);
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'sometimes',
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'engagement_type' => [
                'sometimes',
                'required',
                Rule::in(EngagementRecord::TYPES),
            ],
            'participation_role' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'activity_date' => [
                'sometimes',
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
            'status' => ['prohibited'],
        ];
    }
}
