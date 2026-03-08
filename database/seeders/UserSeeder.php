<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\College;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::truncate();

        $superAdminRole = Role::where('name','super_admin')->first();
        $collegeAdminRole = Role::where('name','college_admin')->first();

        $college = College::first();

        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@outreach.ai',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRole->id,
            'college_id' => $college->id
        ]);

        User::create([
            'name' => 'College Admin',
            'email' => 'college_admin@outreach.ai',
            'password' => Hash::make('password'),
            'role_id' => $collegeAdminRole->id,
            'college_id' => $college->id
        ]);
    }
}