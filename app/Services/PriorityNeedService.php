<?php

namespace App\Services;

use App\Models\PriorityNeed;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PriorityNeedService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        return PriorityNeed::query()
            ->with([
                'community:id,name,city',
                'response:id,survey_date,status',
            ])
            ->when(
                filled($filters['community_id'] ?? null),
                fn ($query) => $query->where(
                    'community_id',
                    $filters['community_id']
                )
            )
            ->when(
                filled($filters['need'] ?? null),
                fn ($query) => $query->where(
                    'need',
                    'like',
                    '%' . $filters['need'] . '%'
                )
            )
            ->when(
                filled($filters['priority_rank'] ?? null),
                fn ($query) => $query->where(
                    'priority_rank',
                    $filters['priority_rank']
                )
            )
            ->whereHas(
                'response',
                function ($query) use ($filters) {
                    $query->where('status', 'submitted')
                        ->when(
                            filled($filters['year'] ?? null),
                            fn ($query) => $query->whereYear(
                                'survey_date',
                                $filters['year']
                            )
                        )
                        ->when(
                            filled($filters['month'] ?? null),
                            fn ($query) => $query->whereMonth(
                                'survey_date',
                                $filters['month']
                            )
                        );
                }
            )
            ->orderBy('priority_rank')
            ->latest('id')
            ->paginate($perPage);
    }

    public function summary(array $filters): Collection
    {
        return PriorityNeed::query()
            ->select([
                'need',
                DB::raw('COUNT(*) as total_count'),
                DB::raw(
                    'AVG(priority_rank) as average_priority_rank'
                ),
            ])
            ->when(
                filled($filters['community_id'] ?? null),
                fn ($query) => $query->where(
                    'community_id',
                    $filters['community_id']
                )
            )
            ->whereHas(
                'response',
                function ($query) use ($filters) {
                    $query->where('status', 'submitted')
                        ->when(
                            filled($filters['year'] ?? null),
                            fn ($query) => $query->whereYear(
                                'survey_date',
                                $filters['year']
                            )
                        )
                        ->when(
                            filled($filters['month'] ?? null),
                            fn ($query) => $query->whereMonth(
                                'survey_date',
                                $filters['month']
                            )
                        );
                }
            )
            ->groupBy('need')
            ->orderByDesc('total_count')
            ->orderBy('average_priority_rank')
            ->get();
    }
}
