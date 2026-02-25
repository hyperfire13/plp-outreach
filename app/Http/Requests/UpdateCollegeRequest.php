<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollegeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:colleges,name,' . $this->college->id,
            'type' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ];
    }
}