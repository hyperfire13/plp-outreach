<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityRequest extends FormRequest
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
        $id = $this->route('community')->id;

        return [
            'name' => 'required|string|max:255|unique:communities,name,' . $id,
            'region' => 'nullable|string|max:255',
            'population' => 'nullable|integer|min:0',

            'poverty_rate' => 'nullable|numeric|min:0|max:100',
            'unemployment_rate' => 'nullable|numeric|min:0|max:100',
            'literacy_rate' => 'nullable|numeric|min:0|max:100',

            'avg_income' => 'nullable|numeric|min:0',

            'urban_rural' => 'nullable|in:urban,rural',
            'disaster_risk_level' => 'nullable|in:low,medium,high',

            'health_risk_index' => 'nullable|numeric|min:0|max:100',
            'infrastructure_score' => 'nullable|numeric|min:0|max:100',
        ];
    }
}