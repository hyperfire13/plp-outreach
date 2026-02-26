<?php

namespace App\Services;

use App\Models\OutreachProgram;

class OutreachProgramService
{
    public function paginate(int $perPage = 10)
    {
        return OutreachProgram::latest()->paginate($perPage);
    }

    public function store(array $data): OutreachProgram
    {
        return OutreachProgram::create($data);
    }

    public function update(OutreachProgram $program, array $data): OutreachProgram
    {
        $program->update($data);
        return $program;
    }

    public function delete(OutreachProgram $program): void
    {
        $program->delete();
    }
}