<?php

namespace App\Http\Resources\Organization;

use App\Enums\OrganizationRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Usuário visto como membro de uma organização (lê o papel da tabela pivô).
 *
 * @mixin User
 */
class OrganizationMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = OrganizationRole::from($this->pivot->role);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role->value,
            'role_label' => $role->label(),
            'joined_at' => $this->pivot->created_at,
        ];
    }
}
