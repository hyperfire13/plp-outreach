<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $colleges = College::query()->pluck('id', 'code');
        $roles = Role::query()->pluck('id', 'name');
        $password = Hash::make('password');

        $users = [
            ['role' => 'super_admin', 'college' => null, 'first_name' => 'Adrian', 'middle_name' => 'Reyes', 'last_name' => 'Santos', 'email' => 'super.admin@plp.edu.ph', 'birthday' => '1985-04-12', 'contact_number' => '09170000001'],
            ['role' => 'calo_administrator', 'college' => null, 'first_name' => 'Maria Elena', 'middle_name' => 'Cruz', 'last_name' => 'Villanueva', 'email' => 'calo.admin@plp.edu.ph', 'birthday' => '1982-09-03', 'contact_number' => '09170000002'],
            ['role' => 'college_admin', 'college' => 'CET', 'first_name' => 'Ramon', 'middle_name' => 'Diaz', 'last_name' => 'Mendoza', 'email' => 'college.admin@plp.edu.ph', 'birthday' => '1980-11-21', 'contact_number' => '09170000003'],
            ['role' => 'coordinator', 'college' => 'CET', 'first_name' => 'Liza', 'middle_name' => 'Garcia', 'last_name' => 'Navarro', 'email' => 'coordinator@plp.edu.ph', 'birthday' => '1988-06-18', 'contact_number' => '09170000004'],
            ['role' => 'project_proponent', 'college' => 'COED', 'first_name' => 'Paolo', 'middle_name' => 'Torres', 'last_name' => 'Aquino', 'email' => 'project.proponent@plp.edu.ph', 'birthday' => '1990-02-14', 'contact_number' => '09170000005'],
            ['role' => 'faculty_extension_coordinator', 'college' => 'COED', 'first_name' => 'Catherine', 'middle_name' => 'Lopez', 'last_name' => 'Ramos', 'email' => 'faculty.extension@plp.edu.ph', 'birthday' => '1987-07-09', 'contact_number' => '09170000006'],
            ['role' => 'college_department_head', 'college' => 'CBA', 'first_name' => 'Roberto', 'middle_name' => 'Lim', 'last_name' => 'Castillo', 'email' => 'department.head@plp.edu.ph', 'birthday' => '1978-12-05', 'contact_number' => '09170000007'],
            ['role' => 'community_partner', 'college' => null, 'first_name' => 'Nora', 'middle_name' => 'Flores', 'last_name' => 'Bautista', 'email' => 'community.partner@example.org', 'birthday' => '1975-08-27', 'contact_number' => '09170000008'],
            ['role' => 'external_evaluator', 'college' => null, 'first_name' => 'Victor', 'middle_name' => 'Sy', 'last_name' => 'Chua', 'email' => 'external.evaluator@example.org', 'birthday' => '1981-03-30', 'contact_number' => '09170000009'],
            ['role' => 'student_volunteer', 'college' => 'CAS', 'first_name' => 'Jasmine', 'middle_name' => 'Perez', 'last_name' => 'Dela Cruz', 'email' => 'student.volunteer@plp.edu.ph', 'birthday' => '2004-05-16', 'contact_number' => '09170000010'],
            ['role' => 'alumni_partner', 'college' => 'CBA', 'first_name' => 'Miguel', 'middle_name' => 'Ocampo', 'last_name' => 'Fernandez', 'email' => 'alumni.partner@plp.edu.ph', 'birthday' => '1995-10-10', 'contact_number' => '09170000011'],
            ['role' => 'monitoring_evaluation_team', 'college' => null, 'first_name' => 'Teresa', 'middle_name' => 'Manalo', 'last_name' => 'Domingo', 'email' => 'monitoring.evaluation@plp.edu.ph', 'birthday' => '1986-01-25', 'contact_number' => '09170000012'],
            ['role' => 'student_volunteer', 'college' => 'CET', 'first_name' => 'Joshua', 'middle_name' => 'Tan', 'last_name' => 'Reyes', 'email' => 'joshua.reyes@plp.edu.ph', 'birthday' => '2003-09-11', 'contact_number' => '09170000013'],
            ['role' => 'student_volunteer', 'college' => 'CON', 'first_name' => 'Angela', 'middle_name' => 'Sison', 'last_name' => 'Mercado', 'email' => 'angela.mercado@plp.edu.ph', 'birthday' => '2004-01-29', 'contact_number' => '09170000014'],
        ];

        foreach ($users as $data) {
            $roleId = $roles[$data['role']] ?? null;
            $collegeId = $data['college'] ? ($colleges[$data['college']] ?? null) : null;

            if (! $roleId || ($data['college'] && ! $collegeId)) {
                throw new RuntimeException("Missing role or college for {$data['email']}.");
            }

            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'last_name' => $data['last_name'],
                    'birthday' => $data['birthday'],
                    'contact_number' => $data['contact_number'],
                    'email_verified_at' => now(),
                    'password' => $password,
                    'role_id' => $roleId,
                    'college_id' => $collegeId,
                ]
            );
        }
    }
}
