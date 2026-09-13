<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
            ],
            [
                'name' => 'college_admin',
                'display_name' => 'College Administrator',
            ],
            [
                'name' => 'coordinator',
                'display_name' => 'Coordinator',
            ],
            [
                'name' => 'calo_administrator',
                'display_name' => 'CALO Administrator',
            ],
            [
                'name' => 'project_proponent',
                'display_name' => 'Project Proponents',
            ],
            [
                'name' => 'faculty_extension_coordinator',
                'display_name' => 'Faculty Extension Coordinators',
            ],
            [
                'name' => 'college_department_head',
                'display_name' => 'College/Department Heads',
            ],
            [
                'name' => 'community_partner',
                'display_name' => 'Community Partners',
            ],
            [
                'name' => 'external_evaluator',
                'display_name' => 'External Evaluators',
            ],
            [
                'name' => 'student_volunteer',
                'display_name' => 'Students/Volunteers',
            ],
            [
                'name' => 'alumni_partner',
                'display_name' => 'Alumni Partners',
            ],
            [
                'name' => 'monitoring_evaluation_team',
                'display_name' => 'Monitoring and Evaluation Team',
            ],
            ['name' => 'calo_staff', 'display_name' => 'CALO Staff'],
            ['name' => 'academic_affairs_officer', 'display_name' => 'Academic Affairs Officer'],
            ['name' => 'vice_president_academic_affairs', 'display_name' => 'Vice President for Academic Affairs'],
            ['name' => 'university_president', 'display_name' => 'University President'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']]
            );
        }
    }
}
