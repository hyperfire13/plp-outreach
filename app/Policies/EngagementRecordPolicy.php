<?php

namespace App\Policies;

use App\Models\EngagementRecord;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class EngagementRecordPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...config('role_access.engagement_viewers'));
    }

    public function view(User $user, EngagementRecord $record): bool
    {
        return $this->canAccessRecord($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...config('role_access.engagement_encoders'));
    }

    public function update(User $user, EngagementRecord $record): bool
    {
        return $record->isEditable()
            && $this->canManageRecord($user, $record);
    }

    public function delete(User $user, EngagementRecord $record): bool
    {
        return $record->isEditable()
            && $this->canManageRecord($user, $record);
    }

    public function submit(User $user, EngagementRecord $record): bool
    {
        return $record->isEditable()
            && $this->canManageRecord($user, $record);
    }

    public function approve(User $user, EngagementRecord $record): bool
    {
        return $record->status === EngagementRecord::STATUS_SUBMITTED
            && $user->hasRole(...config('role_access.engagement_reviewers'));
    }

    public function reject(User $user, EngagementRecord $record): bool
    {
        return $this->approve($user, $record);
    }

    public function viewProfile(User $user, User $profileUser): bool
    {
        if ($user->is($profileUser) || $this->isAdministrator($user)) {
            return true;
        }

        if ($this->hasRole($user, 'monitoring_evaluation_team')) {
            return true;
        }

        if (
            $this->hasRole(
                $user,
                'college_admin',
                'faculty_extension_coordinator',
                'college_department_head'
            )
            && $this->sameCollege($user, $profileUser->college_id)
        ) {
            return true;
        }

        return EngagementRecord::query()
            ->where('user_id', $profileUser->id)
            ->where('encoded_by', $user->id)
            ->exists();
    }

    private function canAccessRecord(
        User $user,
        EngagementRecord $record
    ): bool {
        if (
            $user->id === $record->user_id
            || $user->id === $record->encoded_by
            || $this->isAdministrator($user)
            || $this->hasRole($user, 'monitoring_evaluation_team')
        ) {
            return true;
        }

        return $this->hasRole(
            $user,
            'college_admin',
            'faculty_extension_coordinator',
            'college_department_head'
        ) && $this->sameCollege(
            $user,
            $record->user?->college_id
        );
    }

    private function canManageRecord(
        User $user,
        EngagementRecord $record
    ): bool {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($user->id === $record->encoded_by) {
            return true;
        }

        return $this->hasRole(
            $user,
            'college_admin',
            'faculty_extension_coordinator'
        ) && $this->sameCollege(
            $user,
            $record->user?->college_id
        );
    }
}
