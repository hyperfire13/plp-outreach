<?php

namespace App\Policies;

use App\Models\OutreachProject;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class OutreachProjectPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(
        User $user,
        OutreachProject $project
    ): bool {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($this->isOwner($user, $project->created_by)) {
            return true;
        }

        if ($this->sameCollege($user, $project->college_id)) {
            return $this->hasRole(
                $user,
                'college_admin',
                'faculty_extension_coordinator',
                'college_department_head'
            );
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($project->evaluators()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($project->communityPartners()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'super_admin',
            'calo_administrator',
            'project_proponent',
            'faculty_extension_coordinator'
        );
    }

    public function update(
        User $user,
        OutreachProject $project
    ): bool {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->isOwner($user, $project->created_by)
            && in_array($project->status, [
                'draft',
                'returned',
            ], true);
    }

    public function delete(
        User $user,
        OutreachProject $project
    ): bool {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->isOwner($user, $project->created_by)
            && $project->status === 'draft';
    }
}
