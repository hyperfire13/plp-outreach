<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        $colleges = [
            ['name' => 'College of Engineering and Technology', 'code' => 'CET'],
            ['name' => 'College of Education', 'code' => 'COED'],
            ['name' => 'College of Business Administration', 'code' => 'CBA'],
            ['name' => 'College of Arts and Sciences', 'code' => 'CAS'],
            ['name' => 'College of Nursing', 'code' => 'CON'],
        ];

        foreach ($colleges as $college) {
            College::query()->updateOrCreate(
                ['name' => $college['name']],
                [
                    'code' => $college['code'],
                    'type' => 'academic',
                    'location' => 'PLP Main Campus',
                    'is_active' => true,
                ]
            );
        }
    }
}
