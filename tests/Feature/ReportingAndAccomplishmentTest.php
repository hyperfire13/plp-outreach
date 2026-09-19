<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Community;
use App\Models\EngagementRecord;
use App\Models\OutreachProgram;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportingAndAccomplishmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_their_verified_engagement_profile_as_pdf(): void
    {
        $user = $this->createUser('student_volunteer');
        EngagementRecord::query()->create([
            'user_id' => $user->id,
            'title' => 'Community Learning Session',
            'engagement_type' => EngagementRecord::TYPE_OUTREACH,
            'participation_role' => 'Volunteer',
            'activity_date' => '2026-06-15',
            'service_hours' => 8,
            'source_type' => EngagementRecord::SOURCE_MANUAL,
            'status' => EngagementRecord::STATUS_APPROVED,
            'encoded_by' => $user->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->get('/api/v1/engagement-profiles/me/pdf');

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_college_administrator_can_create_a_scoped_accomplishment_record(): void
    {
        $college = $this->createCollege('CET');
        $user = $this->createUser('college_admin', $college);
        $community = Community::query()->create([
            'name' => 'Barangay Example',
            'city' => 'Pasig City',
            'is_active' => true,
        ]);
        $program = OutreachProgram::query()->create([
            'name' => 'Community Skills Program',
            'category' => 'Education',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/outreach-records', [
            'community_id' => $community->id,
            'outreach_program_id' => $program->id,
            'college_id' => $college->id,
            'budget_used' => 25000,
            'volunteers_count' => 12,
            'impact_score' => 90,
            'success_rate' => 95,
            'satisfaction_rating' => 4.8,
            'execution_date' => '2026-06-15',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.created_by', $user->id);
        $this->assertDatabaseHas('outreach_records', [
            'college_id' => $college->id,
            'created_by' => $user->id,
        ]);
    }

    public function test_dashboard_returns_only_role_relevant_cards(): void
    {
        $user = $this->createUser('student_volunteer');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertOk();
        $keys = collect($response->json('data.cards'))->pluck('key');
        $this->assertTrue($keys->contains('projects'));
        $this->assertTrue($keys->contains('approved_engagements'));
        $this->assertFalse($keys->contains('survey_responses'));
        $this->assertFalse($keys->contains('accomplishments'));
    }

    public function test_engagement_activity_groups_multiple_participants(): void
    {
        $college = $this->createCollege('CAS');
        $administrator = $this->createUser('college_admin', $college);
        $participantRole = Role::query()->create([
            'name' => 'student_volunteer',
            'display_name' => 'Student Volunteer',
        ]);
        $participants = collect(['one', 'two'])->map(
            fn (string $suffix) => User::query()->create([
                'name' => "Participant {$suffix}",
                'first_name' => 'Participant',
                'last_name' => ucfirst($suffix),
                'email' => "participant.{$suffix}@example.com",
                'password' => 'password',
                'role_id' => $participantRole->id,
                'college_id' => $college->id,
            ])
        );
        Sanctum::actingAs($administrator);

        $createResponse = $this->postJson('/api/v1/engagement-records', [
            'user_ids' => $participants->pluck('id')->all(),
            'title' => 'Grouped Community Activity',
            'engagement_type' => EngagementRecord::TYPE_VOLUNTEERISM,
            'participation_role' => 'Volunteer',
            'activity_date' => '2026-09-15',
            'source_type' => EngagementRecord::SOURCE_MANUAL,
            'status' => EngagementRecord::STATUS_DRAFT,
        ]);

        $createResponse->assertCreated()->assertJsonCount(2, 'data');
        $this->assertSame(
            1,
            EngagementRecord::query()
                ->distinct()
                ->count('engagement_group_uuid')
        );

        $listResponse = $this->getJson('/api/v1/engagement-records');

        $listResponse
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.participant_count', 2)
            ->assertJsonCount(2, 'data.data.0.participants');

        $recordId = $listResponse->json('data.data.0.id');
        $updateResponse = $this->putJson(
            "/api/v1/engagement-records/{$recordId}",
            ['user_ids' => [$participants->first()->id]]
        );

        $updateResponse->assertOk();
        $this->assertSame(1, EngagementRecord::query()->count());

        $this->getJson('/api/v1/engagement-records')
            ->assertOk()
            ->assertJsonPath('data.data.0.participant_count', 1)
            ->assertJsonCount(1, 'data.data.0.participants');
    }

    private function createUser(string $roleName, ?College $college = null): User
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
            'college_id' => $college?->id,
        ]);
    }

    private function createCollege(string $code): College
    {
        return College::query()->create([
            'name' => 'College of Engineering and Technology',
            'code' => $code,
            'is_active' => true,
        ]);
    }
}
