<?php

namespace App\Policies;

use App\Models\ProjectReport;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class ProjectReportPolicy
{
    use HandlesRoleAuthorization;

    public function view(User $user, ProjectReport $report): bool
    {
        return $user->can('view', $report->project);
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'project_proponent',
            'faculty_extension_coordinator'
        );
    }

    public function update(User $user, ProjectReport $report): bool
    {
        return $this->isOwner($user, $report->created_by)
            && in_array($report->status, [
                'draft',
                'returned',
            ], true);
    }

    public function submit(User $user, ProjectReport $report): bool
    {
        return $this->isOwner($user, $report->created_by)
            && in_array($report->status, [
                'draft',
                'returned',
            ], true);
    }

    public function validate(User $user, ProjectReport $report): bool
    {
        return $this->hasRole(
            $user,
            'faculty_extension_coordinator',
            'monitoring_evaluation_team',
            'calo_administrator'
        );
    }

    public function approve(User $user, ProjectReport $report): bool
    {
        return $this->hasRole(
            $user,
            'college_department_head',
            'calo_administrator',
            'super_admin'
        );
    }
}
