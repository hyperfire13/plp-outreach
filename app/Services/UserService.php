<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function paginate($authUser, $perPage = 10)
    {
        $query = User::with(['role','college'])
            ->select('id','name','email','role_id','college_id','created_at');

        if ($authUser->role->name === 'college_admin') {
            $query->where('college_id', $authUser->college_id);
        }

        return $query->latest()->paginate($perPage);
    }

    public function store(array $data)
    {
        return User::create($data)->load('role','college');
    }
    //
    public function update(User $user, array $data)
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return $user->load('role','college');
    }

    public function delete(User $user)
    {
        $user->delete();
    }
}
