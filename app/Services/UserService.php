<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function paginate($authUser, int $perPage = 10)
    {
        $query = User::with(['role:id,name', 'college:id,name'])
            ->select([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'birthday',
                'contact_number',
                'email',
                'role_id',
                'college_id',
                'created_at',
            ]);

        if ($authUser->role?->name === 'college_admin') {
            $query->where('college_id', $authUser->college_id);
        }

        return $query->latest()->paginate($perPage);
    }

    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create($data)
                ->load(['role:id,name', 'college:id,name']);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (empty($data['password'])) {
                unset($data['password']);
            }

            $user->update($data);

            return $user->refresh()
                ->load(['role:id,name', 'college:id,name']);
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete();
        });
    }
}
