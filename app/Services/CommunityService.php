<?php

namespace App\Services;

use App\Models\Community;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        return Community::query()
            ->withCount('surveyResponses')
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['is_active'] ?? null),
                fn ($query) => $query->where(
                    'is_active',
                    filter_var(
                        $filters['is_active'],
                        FILTER_VALIDATE_BOOLEAN
                    )
                )
            )
            ->when(
                filled($filters['community_type'] ?? null),
                fn ($query) => $query->where(
                    'community_type',
                    $filters['community_type']
                )
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Community::query()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'barangay_code',
                'city',
            ]);
    }

    public function find(Community $community): Community
    {
        return $community->load([
            'creator:id,first_name,last_name,email',
        ])->loadCount([
            'surveyResponses',
            'priorityNeeds',
        ]);
    }

    public function store(array $data, ?int $userId): Community
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;

            return Community::create($data);
        });
    }

    public function update(
        Community $community,
        array $data
    ): Community {
        return DB::transaction(function () use ($community, $data) {
            $community->update($data);

            return $community->refresh();
        });
    }

    public function delete(Community $community): void
    {
        if ($community->surveyResponses()->exists()) {
            throw ValidationException::withMessages([
                'community' => [
                    'This community cannot be deleted because it already has survey responses. Deactivate it instead.',
                ],
            ]);
        }

        DB::transaction(function () use ($community) {
            $community->delete();
        });
    }
}
