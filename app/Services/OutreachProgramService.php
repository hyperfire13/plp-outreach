<?php

namespace App\Services;

use App\Models\OutreachProgram;
use App\Models\User;

class OutreachProgramService
{
    public function paginate(int $perPage = 10)
    {
        return OutreachProgram::query()
            ->with('creator:id,first_name,middle_name,last_name')
            ->latest()
            ->paginate($perPage);
    }

    public function store(User $authUser, array $data): OutreachProgram
    {
        $data['created_by'] = $authUser->id;

        return OutreachProgram::create($data)->load(
            'creator:id,first_name,middle_name,last_name'
        );
    }

    public function update(OutreachProgram $program, array $data): OutreachProgram
    {
        $program->update($data);
        return $program->load(
            'creator:id,first_name,middle_name,last_name'
        );
    }

    public function delete(OutreachProgram $program): void
    {
        $program->delete();
    }
    public function all()
{
    return OutreachProgram::query()
        ->select([
            'id',
            'name',
            'category',
            'typical_budget',
            'typical_duration_days',
        ])
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
}
}
