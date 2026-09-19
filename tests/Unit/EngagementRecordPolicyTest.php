<?php

namespace Tests\Unit;

use App\Models\EngagementRecord;
use App\Models\Role;
use App\Models\User;
use App\Policies\EngagementRecordPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EngagementRecordPolicyTest extends TestCase
{
    #[DataProvider('reviewerRoles')]
    public function test_only_configured_reviewers_can_approve(
        string $role,
        bool $expected
    ): void {
        $user = $this->userWithRole($role);
        $record = new EngagementRecord([
            'status' => EngagementRecord::STATUS_SUBMITTED,
        ]);

        $this->assertSame(
            $expected,
            (new EngagementRecordPolicy)->approve($user, $record)
        );
    }

    public function test_submitted_record_cannot_be_edited_or_deleted(): void
    {
        $user = $this->userWithRole('super_admin');
        $record = new EngagementRecord([
            'status' => EngagementRecord::STATUS_SUBMITTED,
        ]);

        $policy = new EngagementRecordPolicy;

        $this->assertFalse($policy->update($user, $record));
        $this->assertFalse($policy->delete($user, $record));
    }

    public function test_user_can_view_their_own_record(): void
    {
        $user = $this->userWithRole('student_volunteer');
        $user->id = 10;

        $record = new EngagementRecord([
            'user_id' => 10,
            'status' => EngagementRecord::STATUS_APPROVED,
        ]);

        $this->assertTrue(
            (new EngagementRecordPolicy)->view($user, $record)
        );
    }

    public static function reviewerRoles(): array
    {
        return [
            'super administrator' => ['super_admin', true],
            'CALO administrator' => ['calo_administrator', true],
            'college administrator' => ['college_admin', false],
            'monitoring team' => ['monitoring_evaluation_team', true],
        ];
    }

    private function userWithRole(string $roleName): User
    {
        $user = new User;
        $role = new Role(['name' => $roleName]);
        $user->setRelation('role', $role);

        return $user;
    }
}
