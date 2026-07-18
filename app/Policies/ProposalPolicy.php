<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class ProposalPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return $user->can('view', $proposal->project);
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'project_proponent',
            'faculty_extension_coordinator',
            'calo_administrator',
            'super_admin'
        );
    }

    public function update(User $user, Proposal $proposal): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        return $this->isOwner($user, $proposal->created_by)
            && in_array($proposal->status, [
                'draft',
                'returned',
            ], true);
    }

    public function submit(User $user, Proposal $proposal): bool
    {
        return $this->isOwner($user, $proposal->created_by)
            && in_array($proposal->status, [
                'draft',
                'returned',
            ], true);
    }

    public function departmentApprove(
        User $user,
        Proposal $proposal
    ): bool {
        return $this->hasRole(
            $user,
            'college_department_head'
        ) && $this->sameCollege(
            $user,
            $proposal->project->college_id
        );
    }

    public function finalApprove(
        User $user,
        Proposal $proposal
    ): bool {
        return $this->hasRole(
            $user,
            'calo_administrator',
            'super_admin'
        );
    }

    public function reject(User $user, Proposal $proposal): bool
    {
        return $this->hasRole(
            $user,
            'college_department_head',
            'calo_administrator',
            'super_admin'
        );
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $this->isAdministrator($user)
            || (
                $this->isOwner($user, $proposal->created_by)
                && $proposal->status === 'draft'
            );
    }
}
