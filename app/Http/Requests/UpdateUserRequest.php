<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authUser = $this->user();
        $targetUser = $this->route('user');

        // Super admin can update anyone
        if ($authUser->role->name === 'super_admin') {
            return true;
        }

        // College admin can update users within their college
        if (
            $authUser->role->name === 'college_admin' &&
            $authUser->college_id === $targetUser->college_id
        ) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'required|string|max:255',

            'email' => 'required|email|max:255|unique:users,email,' . $userId,

            'password' => 'nullable|string|min:8|confirmed',

            'role_id' => 'required|exists:roles,id',

            'college_id' => 'nullable|exists:colleges,id',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already used by another user.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}