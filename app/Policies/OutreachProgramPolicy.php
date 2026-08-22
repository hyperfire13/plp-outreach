<?php

namespace App\Policies;

use App\Models\OutreachProgram;
use App\Models\User;

class OutreachProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, [
            'super_admin',
            'college_admin',
            'faculty_extension_coordinator',
            'calo_administrator',
        ]);
    }

    public function view(User $user, OutreachProgram $program): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role->name, [
            'super_admin',
            'college_admin',
            'faculty_extension_coordinator',
        ]);
    }

    public function update(User $user, OutreachProgram $program): bool
    {
        return $program->created_by !== null
            && $program->created_by === $user->id;
    }

    public function delete(User $user, OutreachProgram $program): bool
    {
        return $this->update($user, $program);
    }
}
