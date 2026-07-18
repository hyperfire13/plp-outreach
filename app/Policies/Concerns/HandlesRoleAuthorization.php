<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesRoleAuthorization
{
    protected function hasRole(User $user, string ...$roles): bool
    {
        return in_array(
            $user->role?->name,
            $roles,
            true
        );
    }

    protected function isAdministrator(User $user): bool
    {
        return $this->hasRole(
            $user,
            'super_admin',
            'calo_administrator'
        );
    }

    protected function sameCollege(User $user, ?int $collegeId): bool
    {
        return $collegeId !== null
            && $user->college_id === $collegeId;
    }

    protected function isOwner(User $user, ?int $ownerId): bool
    {
        return $ownerId !== null
            && $user->id === $ownerId;
    }
}
