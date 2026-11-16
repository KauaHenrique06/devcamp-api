<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\Organization\OrganizationResource;
use App\Http\Resources\User\UserResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'base_color_hex' => $this->base_color_hex,
            'logo_url' => $this->logo_url,
            'is_archived' => $this->is_archived,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'members' => $this->whenLoaded('members', fn () => UserResource::collection($this->members)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
