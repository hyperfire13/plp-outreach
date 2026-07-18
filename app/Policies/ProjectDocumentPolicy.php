<?php

namespace App\Policies;

use App\Models\ProjectDocument;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class ProjectDocumentPolicy
{
    use HandlesRoleAuthorization;

    public function view(
        User $user,
        ProjectDocument $document
    ): bool {
        return $user->can('view', $document->project);
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'project_proponent',
            'faculty_extension_coordinator',
            'community_partner',
            'student_volunteer',
            'alumni_partner',
            'monitoring_evaluation_team'
        );
    }

    public function update(
        User $user,
        ProjectDocument $document
    ): bool {
        return $this->isOwner($user, $document->uploaded_by)
            || $this->isAdministrator($user);
    }

    public function delete(
        User $user,
        ProjectDocument $document
    ): bool {
        return $this->isOwner($user, $document->uploaded_by)
            || $this->isAdministrator($user);
    }
}
