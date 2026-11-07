<?php

namespace App\Services\Role;

use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function index(array $data): RoleCollection
    {
        return new RoleCollection(Role::with('permissions')
            ->when($data['search'], function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($data['per_page'], ['*'], 'page', $data['page']));

    }

    public function show(array $data): RoleResource
    {
        return new RoleResource(Role::with('permissions')->findOrFail($data['id']));
    }

    public function store(array $data): RoleResource
    {
        return DB::transaction(function () use ($data): RoleResource {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ]);

            if (! empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return new RoleResource($role->load('permissions'));
        });
    }

    public function update(array $data): RoleResource
    {
        return DB::transaction(function () use ($data): RoleResource {
            $role = Role::findOrFail($data['id']);

            $role->update([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ]);

            if (array_key_exists('permissions', $data)) {
                $role->syncPermissions($data['permissions'] ?? []);
            }

            return new RoleResource($role->load('permissions'));
        });
    }

    public function delete(array $data): void
    {
        DB::transaction(function () use ($data) {
            $role = Role::findOrFail($data['id']);
            $role->delete();
        });
    }

    public function addPermissions(array $data): RoleResource
    {
        return DB::transaction(function () use ($data): RoleResource {
            $role = Role::with('permissions')->findOrFail($data['id']);
            $role->syncPermissions($data['permissions']);

            return new RoleResource($role->load('permissions'));
        });
    }
}
