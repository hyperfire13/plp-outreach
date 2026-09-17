<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const EXCLUDED_ROLES = [
        'super_admin',
        'calo_administrator',
        'calo_staff',
        'academic_affairs_officer',
        'vice_president_academic_affairs',
        'university_president',
    ];

    public static function emailFor(
        string $collegeCode,
        string $roleName,
        int $sequence = 1
    ): string {
        $roleSlug = str_replace('_', '-', $roleName);

        return strtolower(
            "{$collegeCode}.{$roleSlug}.{$sequence}@plp.edu.ph"
        );
    }

    public function run(): void
    {
        $colleges = College::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'code', 'name']);

        $roles = Role::query()
            ->whereNotIn('name', self::EXCLUDED_ROLES)
            ->orderBy('id')
            ->get(['id', 'name']);

        $firstNames = [
            'Andrea', 'Benjamin', 'Camille', 'Daniel', 'Elena',
            'Francis', 'Gabriela', 'Harold', 'Isabela', 'Jerome',
            'Kristine', 'Luis', 'Marianne', 'Nathan', 'Olivia',
            'Patrick', 'Queenie', 'Rafael', 'Sophia', 'Tristan',
        ];
        $lastNames = [
            'Aquino', 'Bautista', 'Castillo', 'Dela Cruz', 'Evangelista',
            'Fernandez', 'Garcia', 'Hernandez', 'Ignacio', 'Jimenez',
            'Lim', 'Mendoza', 'Navarro', 'Ocampo', 'Pascual',
            'Reyes', 'Santos', 'Torres', 'Valdez', 'Villanueva',
        ];
        $middleNames = ['Reyes', 'Cruz', 'Santos', 'Garcia', 'Flores'];
        $password = Hash::make('password');
        $roleIds = Role::query()->pluck('id', 'name');

        $systemUsers = [
            [
                'role' => 'super_admin',
                'first_name' => 'Adrian',
                'middle_name' => 'Reyes',
                'last_name' => 'Santos',
                'email' => 'super.admin@plp.edu.ph',
                'birthday' => '1985-04-12',
                'contact_number' => '09170000001',
            ],
            [
                'role' => 'calo_administrator',
                'first_name' => 'Maria Elena',
                'middle_name' => 'Cruz',
                'last_name' => 'Villanueva',
                'email' => 'calo.admin@plp.edu.ph',
                'birthday' => '1982-09-03',
                'contact_number' => '09170000002',
            ],
            ['role' => 'calo_staff', 'first_name' => 'Paolo', 'middle_name' => 'Garcia', 'last_name' => 'Mendoza', 'email' => 'calo.staff@plp.edu.ph', 'birthday' => '1990-06-18', 'contact_number' => '09170000003'],
            ['role' => 'academic_affairs_officer', 'first_name' => 'Lourdes', 'middle_name' => 'Santos', 'last_name' => 'Navarro', 'email' => 'academic.affairs@plp.edu.ph', 'birthday' => '1986-02-21', 'contact_number' => '09170000004'],
            ['role' => 'vice_president_academic_affairs', 'first_name' => 'Roberto', 'middle_name' => 'Cruz', 'last_name' => 'Aquino', 'email' => 'vpaa@plp.edu.ph', 'birthday' => '1978-11-08', 'contact_number' => '09170000005'],
            ['role' => 'university_president', 'first_name' => 'Teresa', 'middle_name' => 'Reyes', 'last_name' => 'Bautista', 'email' => 'president@plp.edu.ph', 'birthday' => '1972-01-25', 'contact_number' => '09170000006'],
        ];

        foreach ($systemUsers as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => "{$data['first_name']} {$data['last_name']}",
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'last_name' => $data['last_name'],
                    'birthday' => $data['birthday'],
                    'contact_number' => $data['contact_number'],
                    'email_verified_at' => now(),
                    'password' => $password,
                    'role_id' => $roleIds[$data['role']],
                    'college_id' => null,
                ]
            );
        }

        $counter = 0;

        foreach ($colleges as $college) {
            foreach ($roles as $role) {
                foreach ([1, 2] as $sequence) {
                    $firstName = $firstNames[$counter % count($firstNames)];
                    $lastName = $lastNames[($counter * 3) % count($lastNames)];
                    $middleName = $middleNames[$counter % count($middleNames)];
                    $email = self::emailFor(
                        $college->code,
                        $role->name,
                        $sequence
                    );
                    $birthYear = $role->name === 'student_volunteer'
                        ? 2003 + ($sequence - 1)
                        : 1980 + ($counter % 16);

                    User::query()->updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => "{$firstName} {$lastName}",
                            'first_name' => $firstName,
                            'middle_name' => $middleName,
                            'last_name' => $lastName,
                            'birthday' => sprintf(
                                '%04d-%02d-%02d',
                                $birthYear,
                                ($counter % 12) + 1,
                                ($counter % 27) + 1
                            ),
                            'contact_number' => '0918'.str_pad(
                                (string) ($counter + 1),
                                7,
                                '0',
                                STR_PAD_LEFT
                            ),
                            'email_verified_at' => now(),
                            'password' => $password,
                            'role_id' => $role->id,
                            'college_id' => $college->id,
                        ]
                    );

                    $counter++;
                }
            }
        }
    }
}
