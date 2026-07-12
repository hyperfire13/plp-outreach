<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser)
    {
        return in_array($authUser->role->name, ['super_admin', 'college_admin']);
    }

    public function create(User $authUser)
    {
        return in_array($authUser->role->name, ['super_admin', 'college_admin']);
    }

    public function update(User $authUser, User $user)
    {
        if ($authUser->role->name === 'super_admin') return true;

        if ($authUser->role->name === 'college_admin') {
            return $authUser->college_id === $user->college_id;
        }

        return false;
    }

    public function delete(User $authUser, User $user)
    {
        return $authUser->role->name === 'super_admin';
    }
}
