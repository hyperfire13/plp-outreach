<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',

            'email' => 'required|email|max:255|unique:users,email',

            'password' => 'required|string|min:8|confirmed',

            'role_id' => 'required|exists:roles,id',

            'college_id' => 'nullable|exists:colleges,id',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',

            'birthday' => 'nullable|date',

            'contact_number' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            // 'name.required' => 'User name is required.',

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
