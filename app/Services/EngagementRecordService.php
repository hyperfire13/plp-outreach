<?php

namespace App\Services;

use App\Models\Community;
use App\Models\EngagementRecord;
use App\Models\OutreachProject;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EngagementRecordService
{
    public function options(User $authUser): array
    {
        $participants = User::query()
            ->select([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'college_id',
            ])
            ->with('college:id,name')
            ->when(
                $authUser->hasRole(
                    'college_admin',
                    'faculty_extension_coordinator',
                    'college_department_head'
                ) && $authUser->college_id !== null,
                fn (Builder $query) => $query->where(
                    'college_id',
                    $authUser->college_id
                )
            )
            ->when(
                ! $authUser->hasRole(
                    'super_admin',
                    'calo_administrator',
                    'monitoring_evaluation_team',
                    'college_admin',
                    'faculty_extension_coordinator',
                    'college_department_head',
                    'coordinator',
                    'project_proponent'
                ),
                fn (Builder $query) => $query->whereKey($authUser->id)
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $projects = OutreachProject::query()
            ->select(['id', 'title', 'college_id', 'created_by', 'coordinator_id'])
            ->when(
                ! $authUser->hasRole(
                    'super_admin',
                    'calo_administrator',
                    'monitoring_evaluation_team'
                ),
                function (Builder $query) use ($authUser) {
                    $query->where(function (Builder $query) use ($authUser) {
                        if ($authUser->college_id !== null) {
                            $query->where('college_id', $authUser->college_id)
                                ->orWhere('created_by', $authUser->id)
                                ->orWhere('coordinator_id', $authUser->id);

                            return;
                        }

                        $query->where('created_by', $authUser->id)
                            ->orWhere('coordinator_id', $authUser->id);
                    });
                }
            )
            ->orderBy('title')
            ->get();

        return [
            'participants' => $participants,
            'projects' => $projects,
            'communities' => Community::query()
                ->select(['id', 'name', 'city', 'province'])
                ->orderBy('name')
                ->get(),
            'engagement_types' => EngagementRecord::TYPES,
            'source_types' => EngagementRecord::SOURCE_TYPES,
            'statuses' => EngagementRecord::STATUSES,
            'sdgs' => EngagementRecord::SDGS,
        ];
    }

    public function paginate(
        User $authUser,
        array $filters
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            100
        );

        $query = $this->visibleQuery($authUser)
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['user_id'] ?? null),
                fn (Builder $query) => $query->where(
                    'user_id',
                    $filters['user_id']
                )
            )
            ->when(
                filled($filters['college_id'] ?? null),
                fn (Builder $query) => $query->whereHas(
                    'user',
                    fn (Builder $userQuery) => $userQuery->where(
                        'college_id',
                        $filters['college_id']
                    )
                )
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where(
                    'status',
                    $filters['status']
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
            )
            ->when(
                filled($filters['source_type'] ?? null),
                fn (Builder $query) => $query->where(
                    'source_type',
                    $filters['source_type']
                )
            )
            ->when(
                filled($filters['year'] ?? null),
                fn (Builder $query) => $query->whereYear(
                    'activity_date',
                    $filters['year']
                )
            )
            ->when(
                filled($filters['date_from'] ?? null),
                fn (Builder $query) => $query->whereDate(
                    'activity_date',
                    '>=',
                    $filters['date_from']
                )
            )
            ->when(
                filled($filters['date_to'] ?? null),
                fn (Builder $query) => $query->whereDate(
                    'activity_date',
                    '<=',
                    $filters['date_to']
                )
            );

        $paginator = (clone $query)
            ->select('engagement_group_uuid')
            ->selectRaw('MAX(id) as representative_id')
            ->selectRaw('MAX(activity_date) as latest_activity_date')
            ->groupBy('engagement_group_uuid')
            ->orderByDesc('latest_activity_date')
            ->orderByDesc('representative_id')
            ->paginate($perPage);

        $groupUuids = $paginator->getCollection()
            ->pluck('engagement_group_uuid');

        $groupedRecords = $this->visibleQuery($authUser)
            ->with($this->recordRelations())
            ->whereIn('engagement_group_uuid', $groupUuids)
            ->get()
            ->groupBy('engagement_group_uuid');

        $records = $paginator->getCollection()->map(function ($group) use (
            $groupedRecords
        ) {
            $records = $groupedRecords->get(
                $group->engagement_group_uuid,
                collect()
            );
            $record = $records->firstWhere('id', $group->representative_id)
                ?? $records->first();

            if ($record !== null) {
                $record->setAttribute(
                    'participants',
                    $records->pluck('user')->filter()->unique('id')->values()
                );
                $record->setAttribute('participant_count', $records->count());
            }

            return $record;
        })->filter()->values();

        return $paginator->setCollection($records);
    }

    public function find(EngagementRecord $record): EngagementRecord
    {
        return $record->load($this->recordRelations());
    }

    public function store(User $authUser, array $data): EngagementRecord
    {
        $this->ensureCanEncodeForParticipant($authUser, $data);

        return DB::transaction(function () use ($authUser, $data) {
            $status = $data['status'] ?? EngagementRecord::STATUS_DRAFT;

            $data['encoded_by'] = $authUser->id;
            $data['source_type'] = $data['source_type']
                ?? EngagementRecord::SOURCE_MANUAL;
            $data['status'] = $status;
            $data['submitted_at'] = $status === EngagementRecord::STATUS_SUBMITTED
                ? now()
                : null;

            return $this->find(EngagementRecord::create($data));
        });
    }

    public function storeMany(User $authUser, array $data): Collection
    {
        $participantIds = collect($data['user_ids'] ?? [$data['user_id']])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        unset($data['user_ids']);
        $data['engagement_group_uuid'] = (string) Str::uuid();

        return DB::transaction(fn () => $participantIds->map(
            fn (int $participantId) => $this->store($authUser, [
                ...$data,
                'user_id' => $participantId,
            ])
        ));
    }

    public function update(
        User $authUser,
        EngagementRecord $record,
        array $data
    ): EngagementRecord {
        if (! $record->isEditable()) {
            throw ValidationException::withMessages([
                'status' => [
                    'Only draft or rejected engagement records can be edited.',
                ],
            ]);
        }

        $participantIds = collect($data['user_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($participantIds->isNotEmpty()) {
            $participantIds->each(function (int $participantId) use (
                $authUser,
                $data,
                $record
            ): void {
                $this->ensureCanEncodeForParticipant($authUser, [
                    ...$record->only(['outreach_project_id', 'source_type']),
                    ...$data,
                    'user_id' => $participantId,
                ]);
            });
        } else {
            $this->ensureCanEncodeForParticipant(
                $authUser,
                array_merge($record->only([
                    'user_id',
                    'outreach_project_id',
                    'source_type',
                ]), $data)
            );
        }

        return DB::transaction(function () use (
            $record,
            $data,
            $participantIds
        ) {
            $groupQuery = $this->groupQuery($record);

            if ($participantIds->isNotEmpty()) {
                $groupRecords = (clone $groupQuery)->lockForUpdate()->get();
                $existingParticipantIds = $groupRecords->pluck('user_id');
                $template = $record->only([
                    'outreach_project_id',
                    'community_id',
                    'title',
                    'engagement_type',
                    'participation_role',
                    'activity_date',
                    'service_hours',
                    'sdg',
                    'description',
                    'source_type',
                    'status',
                    'encoded_by',
                    'validated_by',
                    'submitted_at',
                    'validated_at',
                    'validation_remarks',
                ]);

                unset($data['user_ids'], $data['user_id']);
                $groupQuery->update($data);
                $this->groupQuery($record)
                    ->whereNotIn('user_id', $participantIds)
                    ->delete();

                $participantIds->diff($existingParticipantIds)->each(
                    function (int $participantId) use (
                        $data,
                        $record,
                        $template
                    ): void {
                        EngagementRecord::query()->create([
                            ...$template,
                            ...$data,
                            'user_id' => $participantId,
                            'engagement_group_uuid' => $record->engagement_group_uuid,
                        ]);
                    }
                );

                $updatedRecord = $this->groupQuery($record)->firstOrFail();

                return $this->find($updatedRecord);
            }

            unset($data['user_ids']);
            $groupQuery->update($data);

            return $this->find($record->refresh());
        });
    }

    public function submit(EngagementRecord $record): EngagementRecord
    {
        if (! $record->isEditable()) {
            throw ValidationException::withMessages([
                'status' => [
                    'Only draft or rejected engagement records can be submitted.',
                ],
            ]);
        }

        return DB::transaction(function () use ($record) {
            $this->groupQuery($record)->update([
                'status' => EngagementRecord::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'validated_by' => null,
                'validated_at' => null,
                'validation_remarks' => null,
            ]);

            return $this->find($record->refresh());
        });
    }

    public function approve(
        EngagementRecord $record,
        User $validator
    ): EngagementRecord {
        $this->ensureSubmitted($record);

        return DB::transaction(function () use ($record, $validator) {
            $this->groupQuery($record)->update([
                'status' => EngagementRecord::STATUS_APPROVED,
                'validated_by' => $validator->id,
                'validated_at' => now(),
                'validation_remarks' => null,
            ]);

            return $this->find($record->refresh());
        });
    }

    public function reject(
        EngagementRecord $record,
        User $validator,
        string $remarks
    ): EngagementRecord {
        $this->ensureSubmitted($record);

        return DB::transaction(function () use (
            $record,
            $validator,
            $remarks
        ) {
            $this->groupQuery($record)->update([
                'status' => EngagementRecord::STATUS_REJECTED,
                'validated_by' => $validator->id,
                'validated_at' => now(),
                'validation_remarks' => trim($remarks),
            ]);

            return $this->find($record->refresh());
        });
    }

    public function delete(EngagementRecord $record): void
    {
        if (! $record->isEditable()) {
            throw ValidationException::withMessages([
                'status' => [
                    'Only draft or rejected engagement records can be deleted.',
                ],
            ]);
        }

        DB::transaction(fn () => $this->groupQuery($record)->delete());
    }

    public function visibleQuery(User $authUser): Builder
    {
        $query = EngagementRecord::query();

        if (
            $authUser->hasRole('super_admin', 'calo_administrator')
            || $authUser->hasRole('monitoring_evaluation_team')
        ) {
            return $query;
        }

        if (
            $authUser->hasRole(
                'college_admin',
                'faculty_extension_coordinator',
                'college_department_head'
            )
            && $authUser->college_id !== null
        ) {
            return $query->where(function (Builder $query) use ($authUser) {
                $query->whereHas(
                    'user',
                    fn (Builder $userQuery) => $userQuery->where(
                        'college_id',
                        $authUser->college_id
                    )
                )->orWhere('user_id', $authUser->id)
                    ->orWhere('encoded_by', $authUser->id);
            });
        }

        return $query->where(function (Builder $query) use ($authUser) {
            $query->where('user_id', $authUser->id)
                ->orWhere('encoded_by', $authUser->id);
        });
    }

    private function ensureSubmitted(EngagementRecord $record): void
    {
        if ($record->status !== EngagementRecord::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'status' => [
                    'Only submitted engagement records can be reviewed.',
                ],
            ]);
        }
    }

    private function groupQuery(EngagementRecord $record): Builder
    {
        return EngagementRecord::query()->where(
            'engagement_group_uuid',
            $record->engagement_group_uuid
        );
    }

    private function recordRelations(): array
    {
        return [
            'user:id,first_name,middle_name,last_name,email,college_id',
            'user.college:id,name',
            'project:id,title,status',
            'community:id,name,city,province',
            'encoder:id,first_name,middle_name,last_name,email',
            'validator:id,first_name,middle_name,last_name,email',
        ];
    }

    private function ensureCanEncodeForParticipant(
        User $authUser,
        array $data
    ): void {
        if ($authUser->hasRole('super_admin', 'calo_administrator')) {
            return;
        }

        $participant = User::query()->findOrFail($data['user_id']);

        if (
            $authUser->hasRole(
                'college_admin',
                'faculty_extension_coordinator'
            )
            && $authUser->college_id !== null
            && $authUser->college_id === $participant->college_id
        ) {
            return;
        }

        if (
            $authUser->hasRole('coordinator', 'project_proponent')
            && ($data['source_type'] ?? null) ===
                EngagementRecord::SOURCE_PROJECT_ROSTER
            && filled($data['outreach_project_id'] ?? null)
        ) {
            $project = OutreachProject::query()->findOrFail(
                $data['outreach_project_id']
            );

            if (
                $project->created_by === $authUser->id
                || $project->coordinator_id === $authUser->id
            ) {
                return;
            }
        }

        throw new AuthorizationException(
            'You cannot encode an engagement record for this participant.'
        );
    }
}
