<?php

namespace App\Http\Requests\EngagementRecord;

use App\Models\EngagementRecord;
use Illuminate\Foundation\Http\FormRequest;

class RejectEngagementRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('engagement_record');

        return $record instanceof EngagementRecord
            && ($this->user()?->can('reject', $record) ?? false);
    }

    public function rules(): array
    {
        return [
            'validation_remarks' => [
                'required',
                'string',
                'max:3000',
            ],
        ];
    }
}
