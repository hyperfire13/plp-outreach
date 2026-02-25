<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollegeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:colleges,name',
            'type' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'College name is required.',
            'name.unique' => 'This college already exists.',
            'type.required' => 'College type is required.',
        ];
    }
}