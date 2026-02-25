<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only super_admin and college_admin can create users
        return in_array($this->user()->role->name, [
            'super_admin',
            'college_admin'
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',

            'email' => 'required|email|max:255|unique:users,email',

            'password' => 'required|string|min:8|confirmed',

            'role_id' => 'required|exists:roles,id',

            'college_id' => 'nullable|exists:colleges,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'User name is required.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',

            'role_id.required' => 'Please select a role.',
            'role_id.exists' => 'Selected role is invalid.',

            'college_id.exists' => 'Selected college is invalid.',
        ];
    }
}