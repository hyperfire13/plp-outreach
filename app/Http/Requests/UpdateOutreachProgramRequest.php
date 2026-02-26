<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutreachProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'super_admin';
    }

    public function rules(): array
    {
        $id = $this->route('outreach_program')->id;

        return [
            'name' => 'required|string|max:255|unique:outreach_programs,name,' . $id,
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',

            'typical_budget' => 'nullable|numeric|min:0',
            'typical_duration_days' => 'nullable|integer|min:1',

            'is_active' => 'boolean'
        ];
    }
}