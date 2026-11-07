<?php

namespace App\Services\Permission;

use App\Http\Resources\Permission\PermissionResource;
use App\Models\Permission;
use Illuminate\Support\Str;

class PermissionService
{
    public function indexGroupedByModule(): array
    {
        $permissions = Permission::where('guard_name', 'api')->orderBy('name')->get();

        $moduleLabels = config('permissions_sync.module_labels', []);
        $grouped = [];

        foreach ($permissions as $permission) {
            $module = Str::before($permission->name, '.');
            if (empty($module)) {
                $module = 'other';
            }

            if (! isset($grouped[$module])) {
                $grouped[$module] = [
                    'module' => $module,
                    'module_label' => $moduleLabels[$module] ?? Str::ucfirst(str_replace('_', ' ', $module)),
                    'permissions' => [],
                ];
            }

            $grouped[$module]['permissions'][] = (new PermissionResource($permission))->toArray(request());
        }

        return [
            'data' => array_values($grouped),
        ];
    }
}
