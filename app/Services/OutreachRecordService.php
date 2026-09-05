<?php

namespace App\Services;

use App\Models\College;
use App\Models\Community;
use App\Models\OutreachProgram;
use App\Models\OutreachRecord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OutreachRecordService
{
    public function paginate(User $authUser, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);

        return $this->visibleQuery($authUser)
            ->with([
                'community:id,name,city,province',
                'outreachProgram:id,name,category',
                'college:id,name',
                'creator:id,first_name,middle_name,last_name',
            ])
            ->when(filled($filters['search'] ?? null), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('community', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('outreachProgram', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('college', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(filled($filters['college_id'] ?? null), fn (Builder $query) => $query->where('college_id', $filters['college_id']))
            ->when(filled($filters['community_id'] ?? null), fn (Builder $query) => $query->where('community_id', $filters['community_id']))
            ->when(filled($filters['outreach_program_id'] ?? null), fn (Builder $query) => $query->where('outreach_program_id', $filters['outreach_program_id']))
            ->when(filled($filters['year'] ?? null), fn (Builder $query) => $query->whereYear('execution_date', $filters['year']))
            ->latest('execution_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function options(User $authUser): array
    {
        return [
            'communities' => Community::query()->select(['id', 'name', 'city'])->orderBy('name')->get(),
            'programs' => OutreachProgram::query()->select(['id', 'name'])->where('is_active', true)->orderBy('name')->get(),
            'colleges' => College::query()
                ->select(['id', 'name'])
                ->when(
                    ! $authUser->hasRole('super_admin', 'calo_administrator') && $authUser->college_id,
                    fn (Builder $query) => $query->whereKey($authUser->college_id)
                )
                ->orderBy('name')
                ->get(),
        ];
    }

    public function find(OutreachRecord $record): OutreachRecord
    {
        return $record->load([
            'community:id,name,city,province',
            'outreachProgram:id,name,category',
            'college:id,name',
            'creator:id,first_name,middle_name,last_name',
        ]);
    }

    public function store(User $authUser, array $data): OutreachRecord
    {
        $this->ensureCollegeAccess($authUser, (int) $data['college_id']);

        return DB::transaction(function () use ($authUser, $data) {
            $data['created_by'] = $authUser->id;

            return $this->find(OutreachRecord::create($data));
        });
    }

    public function update(User $authUser, OutreachRecord $record, array $data): OutreachRecord
    {
        $this->ensureCollegeAccess($authUser, (int) $data['college_id']);

        return DB::transaction(function () use ($record, $data) {
            $record->update($data);

            return $this->find($record->refresh());
        });
    }

    public function delete(OutreachRecord $record): void
    {
        DB::transaction(fn () => $record->delete());
    }

    public function visibleQuery(User $authUser): Builder
    {
        $query = OutreachRecord::query();

        if ($authUser->hasRole('super_admin', 'calo_administrator', 'monitoring_evaluation_team')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($authUser) {
            $query->where('created_by', $authUser->id);

            if ($authUser->college_id !== null) {
                $query->orWhere('college_id', $authUser->college_id);
            }
        });
    }

    private function ensureCollegeAccess(User $authUser, int $collegeId): void
    {
        if ($authUser->hasRole('super_admin', 'calo_administrator')) {
            return;
        }

        if ($authUser->college_id === $collegeId) {
            return;
        }

        throw ValidationException::withMessages([
            'college_id' => ['You can only manage accomplishment records for your assigned college.'],
        ]);
    }
}
