<?php

namespace App\Policies;

use App\Models\OutreachRecord;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class OutreachRecordPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...config('role_access.outreach_record_viewers'));
    }

    public function view(User $user, OutreachRecord $record): bool
    {
        return $this->isAdministrator($user)
            || $this->hasRole($user, 'monitoring_evaluation_team')
            || $this->isOwner($user, $record->created_by)
            || $this->sameCollege($user, $record->college_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...config('role_access.outreach_record_encoders'));
    }

    public function update(User $user, OutreachRecord $record): bool
    {
        return $this->isAdministrator($user)
            || $this->isOwner($user, $record->created_by)
            || ($this->hasRole($user, 'college_admin')
                && $this->sameCollege($user, $record->college_id));
    }

    public function delete(User $user, OutreachRecord $record): bool
    {
        return $this->update($user, $record);
    }
}
