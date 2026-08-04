<?php

namespace Modules\AdminPermission\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserService
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => $data['status'],
            ]);

            $user->adminRoles()->sync($data['role_ids']);

            return $user->load('adminRoles');
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $payload = [
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->adminRoles()->sync($data['role_ids']);

            return $user->load('adminRoles');
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->adminRoles()->detach();
            $user->delete();
        });
    }
}
