<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        // College::truncate();

        College::create([
            'name' => 'College of Engineering',
            'code' => 'ENG',
            'type' => 'academic',
            'location' => 'Main Campus'
        ]);

        College::create([
            'name' => 'College of Education',
            'code' => 'EDU',
            'type' => 'academic',
            'location' => 'Main Campus'
        ]);

        College::create([
            'name' => 'College of Business Administration',
            'code' => 'BUS',
            'type' => 'academic',
            'location' => 'Main Campus'
        ]);
    }
}