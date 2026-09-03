<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\EngagementRecord;
use App\Models\OutreachProject;
use App\Models\User;
use Illuminate\Database\Seeder;

class EngagementRecordSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get()->keyBy('email');
        $projects = OutreachProject::query()->get()->keyBy('title');
        $communities = Community::query()->get()->keyBy('barangay_code');
        $email = static fn (
            string $college,
            string $role,
            int $sequence = 1
        ): string => UserSeeder::emailFor($college, $role, $sequence);
        $validator = $users['calo.admin@plp.edu.ph'];

        $records = [
            ['user' => $email('CET', 'student_volunteer'), 'project' => 'e-Kaalaman: Community Digital Skills Training', 'community' => 'PSG-SM-001', 'title' => 'e-Kaalaman Digital Literacy Workshop', 'type' => 'service_learning', 'role' => 'Digital mentor', 'date' => '2025-09-27', 'hours' => 24, 'sdg' => 'sdg_4', 'description' => 'Facilitated small-group smartphone productivity, online safety, and e-government portal exercises.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CET', 'coordinator')],
            ['user' => $email('CET', 'student_volunteer', 2), 'project' => 'e-Kaalaman: Community Digital Skills Training', 'community' => 'PSG-SM-001', 'title' => 'e-Kaalaman Digital Literacy Workshop', 'type' => 'service_learning', 'role' => 'Technical facilitator', 'date' => '2025-09-27', 'hours' => 28, 'sdg' => 'sdg_4', 'description' => 'Prepared training devices and guided residents through digital payments and online account security.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CET', 'coordinator')],
            ['user' => $email('CET', 'coordinator'), 'project' => 'e-Kaalaman: Community Digital Skills Training', 'community' => 'PSG-SM-001', 'title' => 'e-Kaalaman Digital Literacy Workshop', 'type' => 'extension', 'role' => 'Project coordinator', 'date' => '2025-09-27', 'hours' => 36, 'sdg' => 'sdg_10', 'description' => 'Coordinated partner meetings, training delivery, volunteer deployment, and accomplishment documentation.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CET', 'coordinator')],
            ['user' => $email('CON', 'student_volunteer'), 'project' => 'Healthy Families: Preventive Care and Nutrition Caravan', 'community' => 'PSG-PB-002', 'title' => 'Healthy Families Community Health Caravan', 'type' => 'service_learning', 'role' => 'Student health volunteer', 'date' => '2026-02-15', 'hours' => 18, 'sdg' => 'sdg_3', 'description' => 'Assisted with registration, vital-sign screening, health education, and referral documentation.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CON', 'faculty_extension_coordinator')],
            ['user' => $email('CON', 'student_volunteer', 2), 'project' => 'Healthy Families: Preventive Care and Nutrition Caravan', 'community' => 'PSG-PB-002', 'title' => 'Healthy Families Community Health Caravan', 'type' => 'volunteerism', 'role' => 'Registration volunteer', 'date' => '2026-02-15', 'hours' => 14, 'sdg' => 'sdg_3', 'description' => 'Managed beneficiary registration, queue coordination, and distribution of health-learning materials.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CON', 'faculty_extension_coordinator')],
            ['user' => $email('CON', 'faculty_extension_coordinator'), 'project' => 'Healthy Families: Preventive Care and Nutrition Caravan', 'community' => 'PSG-PB-002', 'title' => 'Healthy Families Community Health Caravan', 'type' => 'extension', 'role' => 'Faculty project lead', 'date' => '2026-02-15', 'hours' => 30, 'sdg' => 'sdg_3', 'description' => 'Led program planning, coordinated local health personnel, supervised student volunteers, and reviewed outcomes.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CON', 'faculty_extension_coordinator')],
            ['user' => $email('CON', 'community_partner'), 'project' => 'Healthy Families: Preventive Care and Nutrition Caravan', 'community' => 'PSG-PB-002', 'title' => 'Community Mobilization for Healthy Families', 'type' => 'outreach', 'role' => 'Community partner coordinator', 'date' => '2026-02-15', 'hours' => 20, 'sdg' => 'sdg_17', 'description' => 'Mobilized residents, coordinated the venue, and supported referrals with barangay health workers.', 'source' => 'project_roster', 'status' => 'approved', 'encoder' => $email('CON', 'faculty_extension_coordinator')],
            ['user' => $email('CBA', 'alumni_partner'), 'project' => null, 'community' => 'PSG-KL-005', 'title' => 'Microenterprise Mentoring Orientation', 'type' => 'training', 'role' => 'Volunteer business mentor', 'date' => '2026-07-26', 'hours' => 6, 'sdg' => 'sdg_8', 'description' => 'Completed mentor orientation and prepared sample costing and recordkeeping exercises for community entrepreneurs.', 'source' => 'manual', 'status' => 'submitted', 'encoder' => $email('CBA', 'coordinator')],
            ['user' => $email('COED', 'project_proponent'), 'project' => null, 'community' => 'PSG-ST-004', 'title' => 'Youth Reading Program Consultation', 'type' => 'outreach', 'role' => 'Faculty facilitator', 'date' => '2026-07-18', 'hours' => 5, 'sdg' => 'sdg_4', 'description' => 'Conducted initial consultation with youth leaders for a proposed reading and learning support project.', 'source' => 'manual', 'status' => 'rejected', 'encoder' => $email('COED', 'project_proponent'), 'remarks' => 'Please attach the final attendance list and confirm the consultation duration before resubmission.'],
            ['user' => $email('CET', 'student_volunteer', 2), 'project' => null, 'community' => 'PSG-MG-003', 'title' => 'Community Emergency Response Orientation', 'type' => 'training', 'role' => 'Student participant', 'date' => '2026-06-20', 'hours' => 8, 'sdg' => 'sdg_11', 'description' => 'Participated in basic disaster preparedness, emergency communication, and household evacuation planning.', 'source' => 'manual', 'status' => 'draft', 'encoder' => $email('CET', 'coordinator')],
        ];

        foreach ($records as $data) {
            $status = $data['status'];
            $submittedAt = $status === EngagementRecord::STATUS_DRAFT
                ? null
                : "{$data['date']} 18:00:00";
            $validatedAt = in_array($status, [EngagementRecord::STATUS_APPROVED, EngagementRecord::STATUS_REJECTED], true)
                ? date('Y-m-d H:i:s', strtotime($submittedAt . ' +2 days'))
                : null;

            EngagementRecord::query()->updateOrCreate(
                [
                    'user_id' => $users[$data['user']]->id,
                    'title' => $data['title'],
                    'activity_date' => $data['date'],
                    'participation_role' => $data['role'],
                ],
                [
                    'outreach_project_id' => $data['project'] ? $projects[$data['project']]->id : null,
                    'community_id' => $communities[$data['community']]->id,
                    'engagement_type' => $data['type'],
                    'service_hours' => $data['hours'],
                    'sdg' => $data['sdg'],
                    'description' => $data['description'],
                    'source_type' => $data['source'],
                    'status' => $status,
                    'encoded_by' => $users[$data['encoder']]->id,
                    'validated_by' => $validatedAt ? $validator->id : null,
                    'submitted_at' => $submittedAt,
                    'validated_at' => $validatedAt,
                    'validation_remarks' => $data['remarks'] ?? null,
                ]
            );
        }
    }
}
