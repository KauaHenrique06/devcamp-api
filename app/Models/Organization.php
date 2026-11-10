<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasUuids, SoftDeletes, Tenantable;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'logo',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Papel do usuário (model ou id) nesta organização, ou null se ele não for membro. */
    public function roleOf(User|string $user): ?OrganizationRole
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $role = $this->members()->whereKey($userId)->value('organization_user.role');

        return $role ? OrganizationRole::from($role) : null;
    }

    public function ownersCount(): int
    {
        return $this->members()->wherePivot('role', OrganizationRole::OWNER->value)->count();
    }
}
