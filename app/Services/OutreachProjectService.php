<?php

namespace App\Services;

use App\Models\OutreachProject;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OutreachProjectService
{
    public function paginate(
        User $authUser,
        array $filters = []
    ): LengthAwarePaginator {
        $query = OutreachProject::query()
            ->with([
                'program:id,name,category',
                'college:id,name',
                'creator:id,first_name,middle_name,last_name',
                'coordinator:id,first_name,middle_name,last_name',
            ]);

        if (! $authUser->hasRole('super_admin', 'calo_administrator')) {
            $query->where('college_id', $authUser->college_id);
        }

        $query->when(
            ! empty($filters['search']),
            function (Builder $query) use ($filters) {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas(
                            'program',
                            fn (Builder $programQuery) => $programQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                        );
                });
            }
        );

        $query->when(
            ! empty($filters['status']),
            fn (Builder $query) => $query->where(
                'status',
                $filters['status']
            )
        );

        return $query
            ->latest()
            ->paginate(
                $filters['per_page'] ?? 10
            );
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
