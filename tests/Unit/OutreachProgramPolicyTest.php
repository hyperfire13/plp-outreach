<?php

namespace Tests\Unit;

use App\Models\OutreachProgram;
use App\Models\Role;
use App\Models\User;
use App\Policies\OutreachProgramPolicy;
use Tests\TestCase;

class OutreachProgramPolicyTest extends TestCase
{
    public function test_creator_can_update_and_delete_their_program(): void
    {
        $creator = $this->userWithRole('college_admin', 10);
        $program = new OutreachProgram(['created_by' => 10]);
        $policy = new OutreachProgramPolicy;

        $this->assertTrue($policy->update($creator, $program));
        $this->assertTrue($policy->delete($creator, $program));
    }

    public function test_non_creator_cannot_update_or_delete_program(): void
    {
        $user = $this->userWithRole('super_admin', 20);
        $program = new OutreachProgram(['created_by' => 10]);
        $policy = new OutreachProgramPolicy;

        $this->assertFalse($policy->update($user, $program));
        $this->assertFalse($policy->delete($user, $program));
    }

    public function test_program_without_creator_cannot_be_managed(): void
    {
        $user = $this->userWithRole('super_admin', 10);
        $program = new OutreachProgram(['created_by' => null]);
        $policy = new OutreachProgramPolicy;

        $this->assertFalse($policy->update($user, $program));
        $this->assertFalse($policy->delete($user, $program));
    }

    private function userWithRole(string $roleName, int $id): User
    {
        $user = new User;
        $user->id = $id;
        $user->setRelation('role', new Role(['name' => $roleName]));

        return $user;
    }
}
