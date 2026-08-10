<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\PriorityNeed;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $administrator = User::query()->where('email', 'calo.admin@plp.edu.ph')->firstOrFail();
        $fieldCoordinator = User::query()->where('email', 'faculty.extension@plp.edu.ph')->firstOrFail();
        $communityPartner = User::query()->where('email', 'community.partner@example.org')->firstOrFail();

        $template = SurveyTemplate::query()->updateOrCreate(
            ['title' => 'PLP Community Needs Assessment', 'version' => 1],
            [
                'description' => 'Standard baseline instrument for identifying community conditions, priority needs, available resources, and preferred PLP interventions.',
                'status' => SurveyTemplate::STATUS_PUBLISHED,
                'is_default' => true,
                'published_at' => '2025-07-01 09:00:00',
                'created_by' => $administrator->id,
            ]
        );

        SurveyTemplate::query()->updateOrCreate(
            ['title' => 'Post-Activity Community Feedback', 'version' => 1],
            [
                'description' => 'Draft instrument for beneficiary satisfaction and initial outcome feedback after an outreach activity.',
                'status' => SurveyTemplate::STATUS_DRAFT,
                'is_default' => false,
                'published_at' => null,
                'created_by' => $administrator->id,
            ]
        );

        $questionData = [
            ['section' => 'Community Profile', 'question' => 'What is the estimated number of households represented in this assessment?', 'question_type' => 'number', 'options' => null, 'is_required' => true, 'sort_order' => 1, 'help_text' => 'Enter the estimated household count.'],
            ['section' => 'Priority Concerns', 'question' => 'Which area requires the most immediate support?', 'question_type' => 'radio', 'options' => ['Education and digital literacy', 'Health and nutrition', 'Livelihood and employment', 'Environment and disaster preparedness', 'Youth development'], 'is_required' => true, 'sort_order' => 2, 'help_text' => null],
            ['section' => 'Priority Concerns', 'question' => 'Select all other concerns observed in the community.', 'question_type' => 'checkbox', 'options' => ['Limited access to training', 'Insufficient health information', 'Unstable household income', 'Flood and disaster exposure', 'Waste management issues', 'Limited youth activities'], 'is_required' => true, 'sort_order' => 3, 'help_text' => null],
            ['section' => 'Community Capacity', 'question' => 'Does the community have an available venue for regular training activities?', 'question_type' => 'boolean', 'options' => null, 'is_required' => true, 'sort_order' => 4, 'help_text' => null],
            ['section' => 'Program Planning', 'question' => 'What schedule is most practical for community participants?', 'question_type' => 'select', 'options' => ['Weekday mornings', 'Weekday afternoons', 'Saturday mornings', 'Saturday afternoons', 'Sunday mornings'], 'is_required' => true, 'sort_order' => 5, 'help_text' => null],
            ['section' => 'Program Planning', 'question' => 'Describe the expected support from PLP and the outcomes the community hopes to achieve.', 'question_type' => 'textarea', 'options' => null, 'is_required' => true, 'sort_order' => 6, 'help_text' => 'Include skills, services, or resources requested.'],
        ];

        $questions = collect();
        foreach ($questionData as $data) {
            $question = SurveyQuestion::query()->updateOrCreate(
                ['survey_template_id' => $template->id, 'sort_order' => $data['sort_order']],
                [...$data, 'is_active' => true]
            );
            $questions[$data['sort_order']] = $question;
        }

        $communities = Community::query()->get()->keyBy('barangay_code');
        $responseData = [
            ['community' => 'PSG-SM-001', 'date' => '2025-07-19', 'department' => 'College of Engineering and Technology', 'conductor' => 'PLP Extension Survey Team A', 'status' => 'submitted', 'program' => 'Digital literacy and online safety workshops', 'remarks' => 'Residents requested hands-on sessions and printed guides.', 'answers' => [1 => 180, 2 => 'Education and digital literacy', 3 => ['Limited access to training', 'Limited youth activities'], 4 => true, 5 => 'Saturday mornings', 6 => 'Provide practical smartphone, online safety, and e-government service training with youth peer mentors.'], 'needs' => [['Digital literacy training', 1, 'Adults and youth need practical skills for safe online transactions and government portals.'], ['Youth skills development', 2, 'Train youth leaders to serve as peer technology mentors.'], ['Employment readiness', 3, 'Residents requested basic online job-search and document preparation skills.']]],
            ['community' => 'PSG-PB-002', 'date' => '2025-10-18', 'department' => 'College of Nursing', 'conductor' => 'PLP Community Health Assessment Team', 'status' => 'submitted', 'program' => 'Preventive health screening and nutrition education', 'remarks' => 'Health center staff recommended focusing on hypertension and family nutrition.', 'answers' => [1 => 320, 2 => 'Health and nutrition', 3 => ['Insufficient health information', 'Unstable household income'], 4 => true, 5 => 'Saturday mornings', 6 => 'Conduct blood-pressure and glucose screening, nutrition counseling, and referral orientation for families.'], 'needs' => [['Preventive health services', 1, 'Accessible screening and risk education are needed for adults and senior citizens.'], ['Nutrition education', 2, 'Families requested affordable meal planning and child nutrition guidance.'], ['Livelihood support', 3, 'Income instability affects access to healthy food and medicine.']]],
            ['community' => 'PSG-MG-003', 'date' => '2026-01-24', 'department' => 'College of Arts and Sciences', 'conductor' => 'PLP Environmental Assessment Team', 'status' => 'submitted', 'program' => 'Flood preparedness and community waste management', 'remarks' => 'Riverside households identified recurring drainage and waste concerns.', 'answers' => [1 => 210, 2 => 'Environment and disaster preparedness', 3 => ['Flood and disaster exposure', 'Waste management issues'], 4 => true, 5 => 'Saturday afternoons', 6 => 'Support household preparedness planning, first-aid orientation, waste segregation, and community clean-up activities.'], 'needs' => [['Disaster preparedness', 1, 'Households need evacuation planning and basic emergency response training.'], ['Waste management', 2, 'Improve segregation practices and reduce waste near waterways.'], ['First-aid training', 3, 'Community volunteers requested basic first-aid and emergency communication skills.']]],
            ['community' => 'PSG-KL-005', 'date' => '2026-04-11', 'department' => 'College of Business Administration', 'conductor' => 'PLP Livelihood Assessment Team', 'status' => 'submitted', 'program' => 'Microenterprise readiness and financial literacy', 'remarks' => 'Home-based sellers want help with costing and digital promotion.', 'answers' => [1 => 145, 2 => 'Livelihood and employment', 3 => ['Unstable household income', 'Limited access to training'], 4 => true, 5 => 'Sunday mornings', 6 => 'Offer business planning, costing, recordkeeping, product packaging, and social-media marketing workshops.'], 'needs' => [['Livelihood and entrepreneurship', 1, 'Residents need practical support to start or improve home-based enterprises.'], ['Financial literacy', 2, 'Participants requested budgeting, savings, and basic business recordkeeping.'], ['Digital marketing', 3, 'Microentrepreneurs need skills to promote products through online channels.']]],
            ['community' => 'PSG-ST-004', 'date' => '2026-07-18', 'department' => 'College of Education', 'conductor' => 'PLP Student Extension Volunteers', 'status' => 'draft', 'program' => 'Reading support and youth enrichment', 'remarks' => 'Draft field notes awaiting validation with barangay youth leaders.', 'answers' => [1 => 95, 2 => 'Youth development', 3 => ['Limited youth activities', 'Limited access to training'], 4 => true, 5 => 'Saturday afternoons', 6 => 'Develop reading tutorials, study-skills sessions, and supervised youth enrichment activities.'], 'needs' => [['Reading and learning support', 1, 'Children need supplemental reading and study-skills activities.'], ['Youth development', 2, 'Youth leaders requested structured enrichment and leadership sessions.']]],
        ];

        foreach ($responseData as $data) {
            $community = $communities[$data['community']];
            $isSubmitted = $data['status'] === SurveyResponse::STATUS_SUBMITTED;
            $response = SurveyResponse::query()->updateOrCreate(
                ['community_id' => $community->id, 'survey_template_id' => $template->id, 'survey_date' => $data['date']],
                [
                    'academic_department' => $data['department'], 'conducted_by' => $data['conductor'],
                    'status' => $data['status'], 'suggested_outreach_program' => $data['program'],
                    'remarks' => $data['remarks'], 'created_by' => $fieldCoordinator->id,
                    'submitted_by' => $isSubmitted ? $communityPartner->id : null,
                    'submitted_at' => $isSubmitted ? "{$data['date']} 17:00:00" : null,
                ]
            );

            foreach ($data['answers'] as $sortOrder => $value) {
                $question = $questions[$sortOrder];
                $columns = ['answer_text' => null, 'answer_number' => null, 'answer_date' => null, 'answer_boolean' => null, 'answer_json' => null];
                match ($question->question_type) {
                    'number' => $columns['answer_number'] = $value,
                    'boolean' => $columns['answer_boolean'] = $value,
                    'checkbox' => $columns['answer_json'] = $value,
                    default => $columns['answer_text'] = $value,
                };
                SurveyAnswer::query()->updateOrCreate(
                    ['survey_response_id' => $response->id, 'survey_question_id' => $question->id],
                    $columns
                );
            }

            foreach ($data['needs'] as [$need, $rank, $description]) {
                PriorityNeed::query()->updateOrCreate(
                    ['survey_response_id' => $response->id, 'priority_rank' => $rank],
                    ['community_id' => $community->id, 'need' => $need, 'description' => $description]
                );
            }
        }
    }
}
