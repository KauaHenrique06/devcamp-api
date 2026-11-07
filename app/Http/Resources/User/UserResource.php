<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissions = $this->whenLoaded('roles')
            ? $this->roles->flatMap(fn ($role) => $role->permissions)->pluck('name')->unique()->values()->toArray()
            : [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'permissions' => $permissions,
            'role_id' => $this->roles[0]->id,
            'role' => $this->roles[0]->name,
            'email_verified_at' => $this->email_verified_at,
            'full_path_profile_picture' => $this->full_path_profile_picture ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
