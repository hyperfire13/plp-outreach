<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('communities', 'name')
                    ->where(
                        fn ($query) => $query
                            ->where('city', $this->input('city', 'Pasig City'))
                            ->whereNull('deleted_at')
                    ),
            ],

            'barangay_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('communities', 'barangay_code')
                    ->whereNull('deleted_at'),
            ],

            'city' => [
                'nullable',
                'string',
                'max:150',
            ],

            'province' => [
                'nullable',
                'string',
                'max:150',
            ],

            'estimated_population' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'estimated_households' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'community_type' => [
                'nullable',
                Rule::in([
                    'urban',
                    'rural',
                    'mixed',
                ]),
            ],

            'predominant_livelihood' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'city' => $this->input('city', 'Pasig City'),
        ]);
    }
}
