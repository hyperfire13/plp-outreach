<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SurveyTemplatePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_respondent_can_download_blank_survey_form(): void
    {
        $user = $this->createUser('community_partner');
        $template = $this->createTemplate($user);

        SurveyQuestion::query()->create([
            'survey_template_id' => $template->id,
            'section' => 'Community Profile',
            'question' => 'What is the primary livelihood in the community?',
            'question_type' => 'radio',
            'options' => ['Employment', 'Small business', 'Agriculture'],
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->get("/api/v1/survey-templates/{$template->id}/pdf");

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('community-needs-assessment-v1-blank-form.pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_role_outside_survey_respondents_cannot_download_form(): void
    {
        $creator = $this->createUser('super_admin');
        $template = $this->createTemplate($creator);
        $user = $this->createUser('student_volunteer');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/survey-templates/{$template->id}/pdf")
            ->assertForbidden();
    }

    private function createTemplate(User $creator): SurveyTemplate
    {
        return SurveyTemplate::query()->create([
            'title' => 'Community Needs Assessment',
            'description' => 'Printable community assessment form.',
            'version' => 1,
            'status' => SurveyTemplate::STATUS_PUBLISHED,
            'is_default' => true,
            'published_at' => now(),
            'created_by' => $creator->id,
        ]);
    }

    private function createUser(string $roleName): User
    {
        $role = Role::query()->create([
            'name' => $roleName,
            'display_name' => str($roleName)->replace('_', ' ')->title(),
        ]);

        return User::query()->create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $roleName.'@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }
}
