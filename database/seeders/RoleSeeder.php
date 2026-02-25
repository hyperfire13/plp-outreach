<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['name' => 'super_admin'],
            ['name' => 'college_admin'],
            ['name' => 'coordinator'],
        ]);
    }
}