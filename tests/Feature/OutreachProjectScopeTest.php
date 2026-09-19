<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\OutreachProgram;
use App\Models\OutreachProject;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OutreachProjectScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_college_user_only_sees_projects_from_their_college(): void
    {
        [$firstCollege, $secondCollege] = $this->createColleges();
        $user = $this->createUser('college_admin', $firstCollege);
        $this->createProjects($firstCollege, $secondCollege, $user);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/outreach-projects');

        $response
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.college_id', $firstCollege->id)
            ->assertJsonMissing(['college_id' => $secondCollege->id]);
    }

    public function test_institutional_administrator_sees_projects_from_all_colleges(): void
    {
        [$firstCollege, $secondCollege] = $this->createColleges();
        $administrator = $this->createUser('super_admin');
        $this->createProjects($firstCollege, $secondCollege, $administrator);
        Sanctum::actingAs($administrator);

        $this->getJson('/api/v1/outreach-projects')
            ->assertOk()
            ->assertJsonPath('total', 2);
    }

    private function createProjects(
        College $firstCollege,
        College $secondCollege,
        User $creator
    ): void {
        $program = OutreachProgram::query()->create([
            'name' => 'College Outreach Program',
            'category' => 'Education',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        foreach ([$firstCollege, $secondCollege] as $index => $college) {
            OutreachProject::query()->create([
                'outreach_program_id' => $program->id,
                'college_id' => $college->id,
                'created_by' => $creator->id,
                'title' => 'Project '.($index + 1),
                'status' => 'draft',
            ]);
        }
    }

    private function createColleges(): array
    {
        return [
            College::query()->create([
                'name' => 'College One',
                'code' => 'CONE',
                'is_active' => true,
            ]),
            College::query()->create([
                'name' => 'College Two',
                'code' => 'CTWO',
                'is_active' => true,
            ]),
        ];
    }

    private function createUser(
        string $roleName,
        ?College $college = null
    ): User {
        $role = Role::query()->create([
            'name' => $roleName,
            'display_name' => str($roleName)->replace('_', ' ')->title(),
        ]);

        return User::query()->create([
            'name' => 'Project User',
            'first_name' => 'Project',
            'last_name' => 'User',
            'email' => "{$roleName}@example.com",
            'password' => 'password',
            'role_id' => $role->id,
            'college_id' => $college?->id,
        ]);
    }
}
