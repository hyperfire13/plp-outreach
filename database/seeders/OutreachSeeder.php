<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Community;
use App\Models\OutreachProgram;
use App\Models\OutreachProject;
use App\Models\OutreachRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class OutreachSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');
        $email = static fn (
            string $college,
            string $role,
            int $sequence = 1
        ): string => UserSeeder::emailFor($college, $role, $sequence);

        $programs = collect([
            ['creator' => $email('CET', 'college_admin'), 'name' => 'Digital Literacy and Inclusion Program', 'category' => 'Education and Technology', 'description' => 'Builds practical digital skills, online safety awareness, and access to essential e-government services.', 'typical_budget' => 85000, 'typical_duration_days' => 30],
            ['creator' => $email('CON', 'faculty_extension_coordinator'), 'name' => 'Community Health and Wellness Program', 'category' => 'Health and Nutrition', 'description' => 'Provides preventive health education, basic screening, nutrition counseling, and referral support.', 'typical_budget' => 120000, 'typical_duration_days' => 14],
            ['creator' => $email('CBA', 'college_admin'), 'name' => 'Sustainable Livelihood Development Program', 'category' => 'Livelihood and Entrepreneurship', 'description' => 'Supports microenterprise planning, financial literacy, product development, and market readiness.', 'typical_budget' => 150000, 'typical_duration_days' => 60],
            ['creator' => $email('CAS', 'college_admin'), 'name' => 'Disaster Resilience and Environmental Stewardship', 'category' => 'Environment and Disaster Preparedness', 'description' => 'Strengthens disaster preparedness, waste management, and community-led environmental action.', 'typical_budget' => 95000, 'typical_duration_days' => 21],
        ])->mapWithKeys(function (array $data) use ($users) {
            $creatorEmail = $data['creator'];
            unset($data['creator']);

            $program = OutreachProgram::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    ...$data,
                    'is_active' => true,
                    'created_by' => $users[$creatorEmail],
                ]
            );

            return [$program->name => $program];
        });

        $colleges = College::query()->pluck('id', 'code');
        $communities = Community::query()->pluck('id', 'barangay_code');

        $projectData = [
            [
                'key' => 'digital',
                'program' => 'Digital Literacy and Inclusion Program',
                'college' => 'CET',
                'creator' => $email('CET', 'project_proponent'),
                'coordinator' => $email('CET', 'coordinator'),
                'title' => 'e-Kaalaman: Community Digital Skills Training',
                'description' => 'A four-week hands-on training covering smartphone productivity, online safety, digital payments, and government service portals.',
                'objectives' => 'Train residents in essential digital skills; improve safe use of online services; prepare youth volunteers as peer digital mentors.',
                'location' => 'Barangay San Miguel Multi-Purpose Hall, Pasig City',
                'start_date' => '2025-09-06', 'end_date' => '2025-09-27',
                'proposed_budget' => 78000, 'expected_beneficiaries' => 80,
                'status' => 'completed', 'submitted_at' => '2025-07-15 09:00:00',
                'approved_at' => '2025-07-22 14:00:00', 'completed_at' => '2025-09-27 17:00:00',
            ],
            [
                'key' => 'health',
                'program' => 'Community Health and Wellness Program',
                'college' => 'CON',
                'creator' => $email('CON', 'faculty_extension_coordinator'),
                'coordinator' => $email('CON', 'faculty_extension_coordinator'),
                'title' => 'Healthy Families: Preventive Care and Nutrition Caravan',
                'description' => 'Community health screening and education sessions for families, older persons, and at-risk adults.',
                'objectives' => 'Provide basic screening; promote nutrition and medication adherence; connect residents with local health services.',
                'location' => 'Barangay Pinagbuhatan Covered Court, Pasig City',
                'start_date' => '2026-02-14', 'end_date' => '2026-02-15',
                'proposed_budget' => 110000, 'expected_beneficiaries' => 250,
                'status' => 'completed', 'submitted_at' => '2025-12-01 10:00:00',
                'approved_at' => '2025-12-12 15:30:00', 'completed_at' => '2026-02-15 18:00:00',
            ],
            [
                'key' => 'livelihood',
                'program' => 'Sustainable Livelihood Development Program',
                'college' => 'CBA',
                'creator' => $email('CBA', 'project_proponent'),
                'coordinator' => $email('CBA', 'coordinator'),
                'title' => 'Negosyong Barangay: Microenterprise Readiness Series',
                'description' => 'Business fundamentals, costing, recordkeeping, digital marketing, and product-development workshops for aspiring entrepreneurs.',
                'objectives' => 'Improve basic business management; develop viable product concepts; connect participants with local market opportunities.',
                'location' => 'Barangay Kalawaan Training Center, Pasig City',
                'start_date' => '2026-08-22', 'end_date' => '2026-10-03',
                'proposed_budget' => 145000, 'expected_beneficiaries' => 60,
                'status' => 'approved', 'submitted_at' => '2026-06-10 08:30:00',
                'approved_at' => '2026-06-24 13:00:00', 'completed_at' => null,
            ],
        ];

        $projects = collect();
        foreach ($projectData as $data) {
            $project = OutreachProject::query()->updateOrCreate(
                ['title' => $data['title']],
                [
                    'outreach_program_id' => $programs[$data['program']]->id,
                    'college_id' => $colleges[$data['college']],
                    'created_by' => $users[$data['creator']],
                    'coordinator_id' => $users[$data['coordinator']],
                    'description' => $data['description'], 'objectives' => $data['objectives'],
                    'location' => $data['location'], 'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'], 'proposed_budget' => $data['proposed_budget'],
                    'expected_beneficiaries' => $data['expected_beneficiaries'], 'status' => $data['status'],
                    'submitted_at' => $data['submitted_at'], 'approved_at' => $data['approved_at'],
                    'completed_at' => $data['completed_at'], 'rejection_reason' => null,
                ]
            );
            $projects[$data['key']] = $project;
        }

        $projects['digital']->members()->syncWithoutDetaching([
            $users[$email('CET', 'student_volunteer')] => ['member_role' => 'Digital mentor', 'status' => 'active', 'joined_at' => '2025-08-20 09:00:00'],
            $users[$email('CET', 'student_volunteer', 2)] => ['member_role' => 'Technical facilitator', 'status' => 'active', 'joined_at' => '2025-08-20 09:00:00'],
        ]);
        $projects['health']->members()->syncWithoutDetaching([
            $users[$email('CON', 'student_volunteer')] => ['member_role' => 'Student health volunteer', 'status' => 'active', 'joined_at' => '2026-01-20 09:00:00'],
            $users[$email('CON', 'student_volunteer', 2)] => ['member_role' => 'Registration volunteer', 'status' => 'active', 'joined_at' => '2026-01-20 09:00:00'],
        ]);
        $projects['livelihood']->members()->syncWithoutDetaching([
            $users[$email('CBA', 'alumni_partner')] => ['member_role' => 'Business mentor', 'status' => 'active', 'joined_at' => '2026-07-15 09:00:00'],
        ]);

        foreach ($projects as $project) {
            $project->communityPartners()->syncWithoutDetaching([
                $users[$email('CET', 'community_partner')] => [
                    'organization_name' => 'Pasig Community Development Network',
                    'contact_person' => 'Nora F. Bautista',
                    'status' => 'active',
                ],
            ]);
            $project->evaluators()->syncWithoutDetaching([
                $users[$email('CET', 'external_evaluator')] => [
                    'evaluation_type' => 'Outcome and quality review',
                    'status' => $project->status === 'completed' ? 'completed' : 'assigned',
                    'assigned_at' => $project->approved_at,
                    'completed_at' => $project->completed_at,
                ],
            ]);
        }

        $records = [
            ['community' => 'PSG-SM-001', 'program' => 'Digital Literacy and Inclusion Program', 'college' => 'CET', 'creator' => $email('CET', 'faculty_extension_coordinator'), 'budget_used' => 74650, 'volunteers_count' => 18, 'impact_score' => 91.5, 'success_rate' => 93.75, 'satisfaction_rating' => 4.72, 'execution_date' => '2025-09-27'],
            ['community' => 'PSG-PB-002', 'program' => 'Community Health and Wellness Program', 'college' => 'CON', 'creator' => $email('CON', 'faculty_extension_coordinator'), 'budget_used' => 106480, 'volunteers_count' => 32, 'impact_score' => 94.2, 'success_rate' => 96.40, 'satisfaction_rating' => 4.81, 'execution_date' => '2026-02-15'],
            ['community' => 'PSG-MG-003', 'program' => 'Disaster Resilience and Environmental Stewardship', 'college' => 'CAS', 'creator' => $email('CAS', 'college_admin'), 'budget_used' => 68300, 'volunteers_count' => 24, 'impact_score' => 87.8, 'success_rate' => 89.10, 'satisfaction_rating' => 4.55, 'execution_date' => '2026-05-23'],
        ];

        foreach ($records as $record) {
            $communityId = $communities[$record['community']];
            $programId = $programs[$record['program']]->id;
            $collegeId = $colleges[$record['college']];

            $outreachRecord = OutreachRecord::query()
                ->where('community_id', $communityId)
                ->where('outreach_program_id', $programId)
                ->where('college_id', $collegeId)
                ->whereDate('execution_date', $record['execution_date'])
                ->firstOrNew();

            $outreachRecord->fill([
                'community_id' => $communityId,
                'outreach_program_id' => $programId,
                'college_id' => $collegeId,
                'execution_date' => $record['execution_date'],
                'created_by' => $users[$record['creator']],
                ...collect($record)->except([
                    'community',
                    'program',
                    'college',
                    'execution_date',
                    'creator',
                ])->all(),
            ])->save();
        }
    }
}
