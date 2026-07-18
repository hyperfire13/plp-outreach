<?php

namespace App\Policies;

use App\Models\MonitoringRecord;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class MonitoringRecordPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasRole(
            $user,
            'monitoring_evaluation_team',
            'faculty_extension_coordinator',
            'college_department_head',
            'calo_administrator',
            'super_admin'
        );
    }

    public function view(
        User $user,
        MonitoringRecord $record
    ): bool {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($this->hasRole($user, 'monitoring_evaluation_team')) {
            return true;
        }

        return $user->can('view', $record->project);
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'monitoring_evaluation_team',
            'faculty_extension_coordinator'
        );
    }

    public function update(
        User $user,
        MonitoringRecord $record
    ): bool {
        return $this->hasRole(
            $user,
            'monitoring_evaluation_team',
            'calo_administrator'
        );
    }

    public function validate(
        User $user,
        MonitoringRecord $record
    ): bool {
        return $this->hasRole(
            $user,
            'monitoring_evaluation_team',
            'calo_administrator'
        );
    }

    public function delete(
        User $user,
        MonitoringRecord $record
    ): bool {
        return $this->isAdministrator($user);
    }
}
