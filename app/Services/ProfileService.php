<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function get(User $user): User
    {
        return $user->load([
            'role:id,name,display_name',
            'college:id,name,code',
        ]);
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update($data);

            return $this->get($user->refresh());
        });
    }

    public function updatePassword(
        User $user,
        string $currentPassword,
        string $newPassword
    ): void {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is incorrect.',
                ],
            ]);
        }

        DB::transaction(function () use ($user, $newPassword) {
            $user->update(['password' => $newPassword]);
        });
    }
}
