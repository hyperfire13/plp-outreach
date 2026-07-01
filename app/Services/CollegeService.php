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

    public function all()
    {
        return College::select(
                'id',
                'name'
            )
            ->where('is_active', true) // if you have this column later
            ->orderBy('name')
            ->get();
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
