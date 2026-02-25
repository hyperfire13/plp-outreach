<?php

namespace App\Services;

use App\Models\College;

class CollegeService
{
    public function paginate($perPage = 10)
    {
        return College::select('id','name','type','location','created_at')
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data)
    {
        return College::create($data);
    }

    public function update(College $college, array $data)
    {
        $college->update($data);
        return $college;
    }

    public function delete(College $college)
    {
        $college->delete();
    }
}