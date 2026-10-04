<?php

namespace Database\Seeders;

use App\Models\ProjectApplicationTemplate;
use Illuminate\Database\Seeder;

class ProjectApplicationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            ProjectApplicationTemplate::query()->updateOrCreate(
                ['code' => $template['code'], 'version' => $template['version']],
                $template
            );
        }
    }

    private function templates(): array
    {
        return [
            [
                'code' => 'community_extension_application',
                'title' => 'Community Extension and Outreach Project Application Form',
                'description' => 'Initial need-based application form for proposed community extension and outreach projects.',
                'version' => 1,
                'phase' => 'application',
                'status' => 'published',
                'is_required' => true,
                'sort_order' => 1,
                'published_at' => now(),
                'schema' => ['sections' => [
                    $this->section('I. Project Information', [
                        $this->field('college_department_org', 'College / Department / Organization', 'text', true),
                        $this->field('title', 'Project Title', 'text', true),
                        $this->field('project_leader', 'Project Leader', 'text', true),
                        $this->field('email', 'Email', 'email', true),
                        $this->field('partner_organization', 'Partner Organization / Barangay', 'text', true),
                        $this->field('implementation_date', 'Proposed Implementation Date', 'date', true),
                        $this->field('implementation_time', 'Time', 'time'),
                        $this->field('venue', 'Venue', 'text', true),
                        $this->field('duration', 'Duration', 'radio', true, ['One-time', 'Short-term (months / one semester)', 'Long-term (one year or more)']),
                    ]),
                    $this->section('II. Project Overview', [
                        $this->field('brief_project_description', 'Brief Project Description', 'textarea', true),
                        $this->field('rationale', 'Basis / Need for the Project', 'textarea', true),
                        $this->field('basis_sources', 'Applicable Basis or Sources', 'checkbox_group', false, [
                            'Community Needs Assessment', 'Community Request / Endorsement', 'Research Findings',
                            'Institutional / Department Agenda', 'Existing PLP Program', 'Emerging Community Concern', 'Others',
                        ]),
                        $this->field('objectives', 'Program Objectives', 'textarea', true),
                        $this->field('expected_outputs', 'Expected Outputs', 'textarea', true),
                        $this->field('expected_outcomes', 'Expected Outcomes', 'textarea', true),
                        $this->field('outcome_categories', 'Expected Outcome Categories', 'checkbox_group', false, [
                            'Knowledge / Skills Development', 'Health & Wellness', 'Livelihood', 'Student Service Learning',
                            'Environmental Protection', 'Community Empowerment', 'Research Generation',
                            'Partnership Development', 'Others',
                        ]),
                    ]),
                    $this->section('III. Project Classification', [
                        $this->field('project_type', 'Project Type', 'radio', true, ['Community Extension Program', 'Outreach Activity']),
                        $this->field('intervention_areas', 'Intervention / Key Development Areas', 'checkbox_group', true, [
                            'Education and Training', 'Economic Development', 'Health and Social Services',
                            'Environmental Programs', 'Community Development', 'Technology and Innovation',
                            'Cultural and Arts Development', 'Sports and Recreation',
                            'Policy Development / Technical Assistance', 'Others',
                        ]),
                        $this->field('beneficiaries', 'Target Beneficiaries', 'textarea', true),
                        $this->field('beneficiary_groups', 'Beneficiary Groups', 'checkbox_group', false, [
                            'Children', 'Youth / OSY', 'Senior Citizens', 'Students', 'Barangay Residents', 'Women', 'PWD', 'Others',
                        ]),
                        $this->field('geographic_coverage', 'Geographic Coverage', 'checkbox_group', true, [
                            'PLP', 'Barangay', 'City-wide', 'Regional', 'National', 'International',
                        ]),
                    ]),
                    $this->section('IV. Strategic Alignment', [
                        $this->field('development_plan_alignment', 'Development Plan Alignment', 'textarea', true),
                        $this->field('plp_vision_alignment', 'PLP Vision', 'checkbox_group', false, [
                            'Advances Future-Proofed Student Success', 'Contributes to Improved Quality of Life in Pasig Community',
                            'Anchored on the Common Good', 'Promotes Multicultural Awareness and Inclusivity',
                        ]),
                        $this->field('plp_mission_alignment', 'PLP Mission', 'checkbox_group', false, [
                            'Provides Inclusive and Transformative Education', 'Empowers Students to Succeed in an Ever-Changing World',
                            'Promotes Community Engagement and Global Awareness',
                            'Equips Learners with Knowledge, Skills, and Values for Total Human Development',
                        ]),
                        $this->field('sdg_alignment', 'SDG Alignment', 'textarea', true, [], 'Identify all applicable Sustainable Development Goals.'),
                    ]),
                    $this->section('V. Resources and Implementation Readiness', [
                        $this->field('resources_needed', 'Resources / Support Needed', 'checkbox_group', false, [
                            'Faculty Experts', 'Student Volunteers', 'Venue', 'Equipment', 'Transportation', 'Food',
                            'Printing', 'External Sponsor', 'Budget Support', 'Others',
                        ]),
                        $this->field('proposed_budget', 'Proposed Budget', 'number', true),
                        $this->field('partner_involvement', 'Partner Involvement', 'textarea'),
                        $this->field('sustainability_plan', 'Sustainability Plan', 'textarea', true),
                        $this->field('risk_assessment', 'Risk Assessment', 'textarea', true),
                        $this->field('monitoring_indicators', 'Monitoring Indicators', 'textarea', true),
                        $this->field('required_attachments', 'Required Attachments', 'checkbox_group', false, [
                            'Project Proposal', 'Food Requisition Slip', 'Budget Proposal',
                        ]),
                    ]),
                ]],
            ],
            [
                'code' => 'project_implementation_profile',
                'title' => 'Project Implementation Profile',
                'description' => 'Implementation profile completed by the focal person during and after project implementation.',
                'version' => 1,
                'phase' => 'implementation',
                'status' => 'published',
                'is_required' => false,
                'sort_order' => 2,
                'published_at' => now(),
                'schema' => ['sections' => [
                    $this->section('I. Project Information', [
                        $this->field('ppa_code', 'PPA Code', 'text'),
                        $this->field('program_title', 'Program Title', 'text'),
                        $this->field('project_title', 'Project Title', 'text'),
                        $this->field('activity_title', 'Activity Title', 'text'),
                        $this->field('project_lead', 'Project Lead', 'text'),
                        $this->field('focal_person', 'Focal Person', 'text'),
                        $this->field('partner_organization', 'Partner Organization / Barangay', 'text'),
                        $this->field('implementation_dates', 'Implementation Date(s)', 'text'),
                        $this->field('implementation_time', 'Time', 'time'),
                        $this->field('venue', 'Venue', 'text'),
                        $this->field('duration', 'Duration', 'text'),
                    ]),
                    $this->section('II. Project Overview', [
                        $this->field('brief_description', 'Brief Project Description', 'textarea'),
                        $this->field('basis_need', 'Basis / Need for the Project', 'textarea'),
                        $this->field('program_objectives', 'Program Objectives', 'textarea'),
                        $this->field('expected_outcomes', 'Expected Outcomes', 'textarea'),
                    ]),
                    $this->section('III. Classification and Reach', [
                        $this->field('project_type', 'Project Type', 'text'),
                        $this->field('intervention_area', 'Intervention / Key Development Area', 'textarea'),
                        $this->field('target_beneficiaries', 'Target Beneficiaries', 'textarea'),
                        $this->field('target_beneficiary_count', 'Number of Target Beneficiaries', 'number'),
                        $this->field('actual_beneficiary_count', 'Number of Actual Beneficiaries', 'number'),
                        $this->field('geographic_coverage', 'Geographic Coverage', 'text'),
                    ]),
                    $this->section('IV-VI. Implementation Results', [
                        $this->field('strategic_alignment', 'Strategic Alignment to Development Plans', 'textarea'),
                        $this->field('budgetary_requirements', 'Budgetary Requirements Based on Activity Design', 'number'),
                        $this->field('actual_budget_expenditures', 'Actual Budget Expenditures', 'number'),
                        $this->field('program_synopsis', 'Program Synopsis', 'textarea'),
                        $this->field('accomplishment_report', 'Accomplishment and Measurable Change', 'textarea'),
                        $this->field('supporting_evidence', 'Supporting Evidence / MOV Reference', 'textarea'),
                    ]),
                ]],
            ],
            [
                'code' => 'impact_outcomes_monitoring',
                'title' => 'Impact and Outcomes Monitoring & Evaluation Form',
                'description' => 'Post-implementation monitoring and evaluation form for project reach, outcomes, sustainability, and recommendations.',
                'version' => 1,
                'phase' => 'monitoring',
                'status' => 'published',
                'is_required' => false,
                'sort_order' => 3,
                'published_at' => now(),
                'schema' => ['sections' => [
                    $this->section('1. Project Reach', [
                        $this->field('target_beneficiaries', 'Target Beneficiaries', 'number'),
                        $this->field('actual_beneficiaries', 'Actual Beneficiaries Reached', 'number'),
                        $this->field('target_reached_percentage', 'Percentage of Target Reached', 'number'),
                    ]),
                    $this->section('2. Objective Achievement', [
                        $this->field('objective_results', 'Objectives, Indicators, Actual Results, and Evidence', 'textarea'),
                        $this->field('achievement_level', 'Overall Objective Achievement', 'radio', false, ['Met', 'Partially Met', 'Not Met']),
                        $this->field('evidence_sources', 'Evidence Sources', 'checkbox_group', false, [
                            'Pre-/post-assessment', 'Survey / feedback', 'Community records / data',
                            'Beneficiary testimonials', 'Other',
                        ]),
                    ]),
                    $this->section('3. Measurable Outcomes', [
                        $this->field('significant_change', 'Significant Change Among Beneficiaries / Community', 'textarea'),
                        $this->field('baseline_data', 'Baseline Data', 'textarea'),
                        $this->field('immediate_outcome', 'Outcome Immediately After the Program', 'textarea'),
                        $this->field('three_month_outcome', 'Outcome Three Months After', 'textarea'),
                        $this->field('six_month_outcome', 'Outcome Six Months After', 'textarea'),
                        $this->field('one_year_outcome', 'Outcome One Year After', 'textarea'),
                        $this->field('summary_outcomes', 'Summary Outcomes and Evidence', 'textarea'),
                    ]),
                    $this->section('4. Net Promoter Score', [
                        $this->field('promoters', 'Promoters (ratings 9-10)', 'number'),
                        $this->field('passives', 'Passives (ratings 7-8)', 'number'),
                        $this->field('detractors', 'Detractors (ratings 0-6)', 'number'),
                        $this->field('nps_score', 'Net Promoter Score', 'number'),
                    ]),
                    $this->section('5-6. Sustainability and Overall Assessment', [
                        $this->field('sustainable_outcome', 'Outcome or Capability That Will Continue', 'textarea'),
                        $this->field('sustaining_party', 'Who Will Sustain It?', 'text'),
                        $this->field('further_intervention_needed', 'Is Further PLP Intervention Needed?', 'radio', false, ['No', 'Yes']),
                        $this->field('further_intervention_details', 'Further Intervention Details', 'textarea'),
                        $this->field('overall_impact', 'Overall Impact', 'radio', false, ['High', 'Moderate', 'Low']),
                        $this->field('recommendations', 'Recommendation', 'checkbox_group', false, [
                            'Continue', 'Improve', 'Scale Up', 'Replicate', 'Conclude',
                        ]),
                    ]),
                ]],
            ],
        ];
    }

    private function section(string $title, array $fields): array
    {
        return compact('title', 'fields');
    }

    private function field(
        string $key,
        string $label,
        string $type,
        bool $required = false,
        array $options = [],
        ?string $helpText = null
    ): array {
        return array_filter([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'required' => $required,
            'options' => $options,
            'help_text' => $helpText,
        ], fn ($value) => $value !== null && $value !== []);
    }
}
