<?php

namespace App\Policies;

use App\Models\ProjectProposal;
use App\Models\User;

class ProjectProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role_name, config('role_access.proposal_viewers', []), true);
    }

    public function view(User $user, ProjectProposal $proposal): bool
    {
        return $this->viewAny($user) && ($user->id === $proposal->applicant_id || $user->college_id === $proposal->college_id || $user->college_id === null);
    }

    public function create(User $user): bool
    {
        return in_array($user->role_name, config('role_access.proposal_applicants', []), true) && $user->college_id !== null;
    }

    public function update(User $user, ProjectProposal $proposal): bool
    {
        return $proposal->applicant_id === $user->id && $proposal->isEditable();
    }

    public function delete(User $user, ProjectProposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function submit(User $user, ProjectProposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function review(User $user, ProjectProposal $proposal): bool
    {
        return $proposal->status === 'under_review' && $this->roleForStep($proposal->current_step) === $user->role_name && ($proposal->current_step !== 'immediate_head' || $user->college_id === $proposal->college_id);
    }

    public function issueNtp(User $user, ProjectProposal $proposal): bool
    {
        return $proposal->status === 'approved' && $user->hasRole('super_admin', 'calo_administrator');
    }

    private function roleForStep(?string $step): ?string
    {
        return ['immediate_head' => 'college_department_head', 'calo_staff' => 'calo_staff', 'calo_head' => 'calo_administrator', 'academic_affairs_officer' => 'academic_affairs_officer', 'vpaa' => 'vice_president_academic_affairs', 'president' => 'university_president'][$step] ?? null;
    }
}
