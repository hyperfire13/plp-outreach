<?php

namespace App\Services;

use DB;
use App\Models\OutreachRecord;

class OutreachRecordService
{
    public function paginate(int $perPage = 10)
    {
        return OutreachRecord::with([
                'community:id,name',
                'outreachProgram:id,name',
                'college:id,name'
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data): OutreachRecord
    {
        return DB::transaction(function () use ($data) {
            return OutreachRecord::create($data);
        });
    }

    public function update(OutreachRecord $record, array $data): OutreachRecord
    {
        return DB::transaction(function () use ($record, $data) {
            $record->update($data);
            return $record;
        });
    }

    public function delete(OutreachRecord $record): void
    {
        $record->delete();
    }
}