<?php

namespace App\Services;

use App\Models\EngagementRecord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EngagementProfileService
{
    public function get(User $user, array $filters): array
    {
        $baseQuery = $this->baseQuery($user, $filters);

        return [
            'user' => $user->load(['role:id,name', 'college:id,name']),
            'summary' => $this->summary($baseQuery),
            'engagements' => $this->paginateRecords($baseQuery, $filters),
        ];
    }

    public function getForExport(User $user, array $filters): array
    {
        $baseQuery = $this->baseQuery($user, $filters);

        return [
            'user' => $user->load(['role:id,name', 'college:id,name']),
            'summary' => $this->summary($baseQuery),
            'engagements' => $this->records($baseQuery),
            'filters' => $filters,
            'generated_at' => now(),
        ];
    }

    private function baseQuery(User $user, array $filters): Builder
    {
        return EngagementRecord::query()
            ->approved()
            ->where('user_id', $user->id)
            ->when(
                filled($filters['year'] ?? null),
                fn (Builder $query) => $query->whereYear(
                    'activity_date',
                    $filters['year']
                )
            )
            ->when(
                filled($filters['engagement_type'] ?? null),
                fn (Builder $query) => $query->where(
                    'engagement_type',
                    $filters['engagement_type']
                )
            )
            ->when(
                filled($filters['sdg'] ?? null),
                fn (Builder $query) => $query->where(
                    'sdg',
                    $filters['sdg']
                )
            );
    }

    private function summary(Builder $baseQuery): array
    {
        return [
            'total_engagements' => (clone $baseQuery)->count(),
            'total_service_hours' => (float) (
                (clone $baseQuery)->sum('service_hours')
            ),
            'communities_served' => (clone $baseQuery)
                ->whereNotNull('community_id')
                ->distinct()
                ->count('community_id'),
            'projects_joined' => (clone $baseQuery)
                ->whereNotNull('outreach_project_id')
                ->distinct()
                ->count('outreach_project_id'),
            'by_type' => (clone $baseQuery)
                ->selectRaw('engagement_type, COUNT(*) as total')
                ->groupBy('engagement_type')
                ->orderBy('engagement_type')
                ->pluck('total', 'engagement_type'),
        ];
    }

    private function records(Builder $query): Collection
    {
        return $query
            ->with(['project:id,title', 'community:id,name,city'])
            ->latest('activity_date')
            ->latest('id')
            ->get();
    }

    private function paginateRecords(
        Builder $query,
        array $filters
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        return $query
            ->with([
                'project:id,title',
                'community:id,name,city',
            ])
            ->latest('activity_date')
            ->latest('id')
            ->paginate($perPage);
    }
}
