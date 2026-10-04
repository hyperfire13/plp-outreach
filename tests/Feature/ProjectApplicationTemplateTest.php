<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\PriorityNeed;
use App\Models\ProjectApplicationTemplate;
use App\Models\Role;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApplicationTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_calo_administrator_can_manage_project_application_templates(): void
    {
        $administrator = $this->createUser('calo_administrator');
        Sanctum::actingAs($administrator);

        $response = $this->postJson('/api/v1/project-application-templates', [
            'code' => 'test_application',
            'title' => 'Test Application',
            'description' => 'Test form',
            'version' => 1,
            'phase' => 'application',
            'status' => 'published',
            'is_required' => true,
            'sort_order' => 1,
            'schema' => $this->schema(),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'test_application');
        $this->assertDatabaseHas('project_application_templates', [
            'code' => 'test_application',
            'status' => 'published',
        ]);
    }

    public function test_non_manager_cannot_create_project_application_template(): void
    {
        Sanctum::actingAs($this->createUser('student_volunteer'));

        $this->postJson('/api/v1/project-application-templates', [
            'code' => 'blocked',
            'title' => 'Blocked Template',
            'version' => 1,
            'phase' => 'application',
            'status' => 'draft',
            'is_required' => false,
            'sort_order' => 1,
            'schema' => $this->schema(),
        ])->assertForbidden();
    }

    public function test_template_schema_rejects_duplicate_keys_and_missing_choice_options(): void
    {
        Sanctum::actingAs($this->createUser('calo_administrator'));

        $schema = [
            'sections' => [[
                'title' => 'Project Information',
                'fields' => [
                    [
                        'key' => 'project_title',
                        'label' => 'Project Title',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'key' => 'project_title',
                        'label' => 'Project Category',
                        'type' => 'select',
                        'required' => true,
                        'options' => [],
                    ],
                ],
            ]],
        ];

        $this->postJson('/api/v1/project-application-templates', [
            'code' => 'invalid_builder_template',
            'title' => 'Invalid Builder Template',
            'version' => 1,
            'phase' => 'application',
            'status' => 'draft',
            'is_required' => false,
            'sort_order' => 1,
            'schema' => $schema,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'schema.sections.0.fields.1.key',
                'schema.sections.0.fields.1.options',
            ]);
    }

    public function test_user_without_college_can_create_proposal_with_template_response(): void
    {
        $user = $this->createUser('community_partner');
        $community = Community::query()->create([
            'name' => 'Barangay Test',
            'city' => 'Pasig City',
            'province' => 'Metro Manila',
            'is_active' => true,
        ]);
        $surveyTemplate = SurveyTemplate::query()->create([
            'title' => 'Needs Survey',
            'version' => 1,
            'status' => 'published',
            'is_default' => true,
        ]);
        $surveyResponse = SurveyResponse::query()->create([
            'community_id' => $community->id,
            'survey_template_id' => $surveyTemplate->id,
            'survey_date' => now()->toDateString(),
            'status' => 'submitted',
            'created_by' => $user->id,
        ]);
        $priorityNeed = PriorityNeed::query()->create([
            'survey_response_id' => $surveyResponse->id,
            'community_id' => $community->id,
            'need' => 'Digital literacy training',
            'priority_rank' => 1,
            'status' => 'validated',
        ]);
        $template = ProjectApplicationTemplate::query()->create([
            'code' => 'required_application',
            'title' => 'Required Application',
            'version' => 1,
            'phase' => 'application',
            'status' => 'published',
            'is_required' => true,
            'sort_order' => 1,
            'schema' => $this->schema(),
        ]);
        Sanctum::actingAs($user);

        $payload = [
            'priority_need_id' => $priorityNeed->id,
            'title' => 'Digital Skills Project',
            'rationale' => 'Validated community need.',
            'objectives' => 'Improve practical digital skills.',
            'beneficiaries' => 'Community residents',
            'expected_outputs' => 'Training sessions',
            'expected_outcomes' => 'Improved digital literacy',
            'sustainability_plan' => 'Train community facilitators.',
            'risk_assessment' => 'Limited device availability.',
            'monitoring_indicators' => 'Completion and assessment results.',
            'sdg_alignment' => 'SDG 4',
            'development_plan_alignment' => 'Education and youth development',
            'proposed_budget' => 10000,
            'resources' => [],
            'workplans' => [],
            'template_responses' => [[
                'project_application_template_id' => $template->id,
                'response_data' => ['sample_field' => 'Completed response'],
            ]],
        ];

        $response = $this->postJson('/api/v1/project-proposals', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.college_id', null)
            ->assertJsonPath(
                'data.template_responses.0.response_data.sample_field',
                'Completed response'
            );
    }

    private function schema(): array
    {
        return [
            'sections' => [[
                'title' => 'Project Information',
                'fields' => [[
                    'key' => 'sample_field',
                    'label' => 'Sample Field',
                    'type' => 'text',
                    'required' => true,
                ]],
            ]],
        ];
    }

    private function createUser(string $roleName): User
    {
        $role = Role::query()->create([
            'name' => $roleName,
            'display_name' => str($roleName)->replace('_', ' ')->title(),
        ]);

        return User::query()->create([
            'name' => 'Template User',
            'first_name' => 'Template',
            'last_name' => 'User',
            'email' => "{$roleName}@example.com",
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }
}
