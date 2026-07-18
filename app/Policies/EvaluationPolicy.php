<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class EvaluationPolicy
{
    use HandlesRoleAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasRole(
            $user,
            'external_evaluator',
            'monitoring_evaluation_team',
            'calo_administrator',
            'super_admin'
        );
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($this->hasRole($user, 'monitoring_evaluation_team')) {
            return true;
        }

        return $evaluation->evaluator_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole(
            $user,
            'external_evaluator',
            'monitoring_evaluation_team'
        );
    }

    public function update(User $user, Evaluation $evaluation): bool
    {
        return $evaluation->evaluator_id === $user->id
            && $evaluation->status === 'draft';
    }

    public function submit(User $user, Evaluation $evaluation): bool
    {
        return $evaluation->evaluator_id === $user->id
            && $evaluation->status === 'draft';
    }

    public function validate(User $user, Evaluation $evaluation): bool
    {
        return $this->hasRole(
            $user,
            'monitoring_evaluation_team',
            'calo_administrator'
        );
    }

    public function delete(User $user, Evaluation $evaluation): bool
    {
        return $this->isAdministrator($user);
    }
}
