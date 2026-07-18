<?php

namespace App\Policies;

use App\Models\College;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class CollegePolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, College $college): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->sameCollege($user, $college->id);
    }

    public function create(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    public function update(User $user, College $college): bool
    {
        return $this->isAdministrator($user);
    }

    public function delete(User $user, College $college): bool
    {
        return $this->isAdministrator($user);
    }
}
