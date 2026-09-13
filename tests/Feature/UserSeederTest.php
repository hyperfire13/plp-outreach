<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_two_users_per_non_system_role_per_college(): void
    {
        $this->seed();
        $this->seed();

        $colleges = College::query()->where('is_active', true)->get();
        $roles = Role::query()
            ->whereNotIn('name', [
                'super_admin',
                'calo_administrator',
                'calo_staff',
                'academic_affairs_officer',
                'vice_president_academic_affairs',
                'university_president',
            ])
            ->get();

        $this->assertSame(5, $colleges->count());
        $this->assertSame(10, $roles->count());
        $this->assertSame(106, User::query()->count());
        $systemUsers = User::query()->whereHas(
                'role',
                fn ($query) => $query->whereIn('name', [
                    'super_admin',
                    'calo_administrator',
                    'calo_staff',
                    'academic_affairs_officer',
                    'vice_president_academic_affairs',
                    'university_president',
                ])
            )->get();

        $this->assertCount(6, $systemUsers);
        $this->assertTrue($systemUsers->every(
            fn (User $user) => $user->college_id === null
        ));
        $this->assertSame(
            [
                'academic_affairs_officer',
                'calo_administrator',
                'calo_staff',
                'super_admin',
                'university_president',
                'vice_president_academic_affairs',
            ],
            $systemUsers->load('role')
                ->pluck('role.name')
                ->sort()
                ->values()
                ->all()
        );

        foreach ($colleges as $college) {
            foreach ($roles as $role) {
                $users = User::query()
                    ->where('college_id', $college->id)
                    ->where('role_id', $role->id)
                    ->get();

                $this->assertCount(2, $users);
                $this->assertTrue(
                    $users->every(
                        fn (User $user) => Hash::check(
                            'password',
                            $user->password
                        )
                    )
                );
            }
        }
    }
}
