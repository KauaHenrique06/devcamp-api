<?php

namespace App\Services\User;

use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function index(array $data): UserCollection
    {
        $perPage = (int) ($data['per_page'] ?? 10);
        $page = (int) ($data['page'] ?? 1);
        $search = $data['search'] ?? null;

        $query = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return new UserCollection($paginator);
    }

    public function show(array $data): UserResource
    {
        $user = User::with('roles')->findOrFail($data['id']);

        return new UserResource($user);
    }

    public function store(array $data): UserResource
    {
        return DB::transaction(function () use ($data): UserResource {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            if (! empty($data['role_id'])) {
                $role = Role::findOrFail($data['role_id']);
                $user->syncRoles($role);
            }
            return new UserResource($user->load('roles'));
        });
    }

    public function update(array $data): UserResource
    {
        return DB::transaction(function () use ($data): UserResource {
            $user = User::findOrFail($data['id']);

            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }

            $user->update($updateData);

            if (array_key_exists('roles', $data)) {
                $roles = ! empty($data['roles'])
                    ? Role::whereIn('name', $data['roles'])->where('guard_name', 'api')->get()
                    : collect();
                $user->syncRoles($roles);
            }

            return new UserResource($user->load('roles'));
        });
    }

    public function delete(array $data): void
    {
        DB::transaction(function () use ($data) {
            $user = User::findOrFail($data['id']);
            $user->delete();
        });
    }
}
