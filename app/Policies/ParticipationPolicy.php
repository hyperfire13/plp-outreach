<?php

namespace App\Policies;

use App\Models\Participation;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class ParticipationPolicy
{
    use HandlesRoleAuthorization;

    public function view(
        User $user,
        Participation $participation
    ): bool {
        return $participation->user_id === $user->id
            || $user->can('view', $participation->project);
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'student_volunteer',
            'alumni_partner',
            'community_partner'
        );
    }

    public function update(
        User $user,
        Participation $participation
    ): bool {
        return $participation->user_id === $user->id
            && $participation->status === 'pending';
    }

    public function confirm(
        User $user,
        Participation $participation
    ): bool {
        return $this->hasRole(
            $user,
            'project_proponent',
            'faculty_extension_coordinator',
            'calo_administrator'
        );
    }

    public function delete(
        User $user,
        Participation $participation
    ): bool {
        return $participation->user_id === $user->id
            || $this->isAdministrator($user);
    }
}
