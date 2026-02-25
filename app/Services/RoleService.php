<?php

namespace App\Services;

use App\Models\Role;

class RoleService
{
    public function all()
    {
        return Role::select('id','name')
            ->orderBy('name')
            ->get();
    }
}