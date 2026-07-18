<?php

namespace App\Services;

use App\Models\User;
use App\Models\OutreachProject;
use Illuminate\Support\Facades\DB;

class OutreachProjectService
{
    public function paginate(
        User $authUser,
        int $perPage = 10
    ) {
        $query = OutreachProject::query()
            ->with([
                'program:id,name,category',
                'college:id,name',
                'creator:id,first_name,middle_name,last_name',
                'coordinator:id,first_name,middle_name,last_name',
            ]);

        if ($authUser->hasRole('project_proponent')) {
            $query->where('created_by', $authUser->id);
        }

        if ($authUser->hasRole(
            'college_admin',
            'faculty_extension_coordinator',
            'college_department_head'
        )) {
            $query->where(
                'college_id',
                $authUser->college_id
            );
        }

        if ($authUser->hasRole('community_partner')) {
            $query->whereHas(
                'communityPartners',
                fn ($partnerQuery) =>
                    $partnerQuery->where(
                        'users.id',
                        $authUser->id
                    )
            );
        }

        if ($authUser->hasRole('external_evaluator')) {
            $query->whereHas(
                'evaluators',
                fn ($evaluatorQuery) =>
                    $evaluatorQuery->where(
                        'users.id',
                        $authUser->id
                    )
            );
        }

        if ($authUser->hasRole(
            'student_volunteer',
            'alumni_partner'
        )) {
            $query->whereHas(
                'members',
                fn ($memberQuery) =>
                    $memberQuery->where(
                        'users.id',
                        $authUser->id
                    )
            );
        }

        return $query
            ->latest()
            ->paginate($perPage);
    }

    public function store(
        User $authUser,
        array $data
    ): OutreachProject {
        return DB::transaction(function () use (
            $authUser,
            $data
        ) {
            $data['created_by'] = $authUser->id;
            $data['status'] = $data['status'] ?? 'draft';

            return OutreachProject::create($data)
                ->load([
                    'program:id,name,category',
                    'college:id,name',
                    'creator',
                    'coordinator',
                ]);
        });
    }

    public function update(
        OutreachProject $project,
        array $data
    ): OutreachProject {
        return DB::transaction(function () use (
            $project,
            $data
        ) {
            $project->update($data);

            return $project->refresh()->load([
                'program:id,name,category',
                'college:id,name',
                'creator',
                'coordinator',
            ]);
        });
    }

    public function delete(
        OutreachProject $project
    ): void {
        DB::transaction(
            fn () => $project->delete()
        );
    }
}
