<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class UserPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $this->hasRole(
            $authUser,
            'super_admin',
            'calo_administrator',
            'college_admin'
        );
    }

    public function view(User $authUser, User $user): bool
    {
        if ($this->isAdministrator($authUser)) {
            return true;
        }

        if ($this->hasRole($authUser, 'college_admin')) {
            return $this->sameCollege(
                $authUser,
                $user->college_id
            );
        }

        return $authUser->is($user);
    }

    public function create(User $authUser): bool
    {
        return $this->hasRole(
            $authUser,
            'super_admin',
            'calo_administrator',
            'college_admin'
        );
    }

    public function update(User $authUser, User $user): bool
    {
        if ($this->isAdministrator($authUser)) {
            return true;
        }

        if ($this->hasRole($authUser, 'college_admin')) {
            return $this->sameCollege(
                $authUser,
                $user->college_id
            );
        }

        return $authUser->is($user);
    }

    public function delete(User $authUser, User $user): bool
    {
        if ($authUser->is($user)) {
            return false;
        }

        return $this->isAdministrator($authUser);
    }
}
