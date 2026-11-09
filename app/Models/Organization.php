<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasUuids, SoftDeletes;

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

    /**
     * Scope local: só as organizações das quais o usuário é membro.
     * Uso: Organization::visibleTo($user)->get()
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('members', fn (Builder $q) => $q->whereKey($user->getKey()));
    }

    /** Papel do usuário nesta organização, ou null se ele não for membro. */
    public function roleOf(User $user): ?OrganizationRole
    {
        $role = $this->members()->whereKey($user->getKey())->value('organization_user.role');

        return $role ? OrganizationRole::from($role) : null;
    }

    public function ownersCount(): int
    {
        return $this->members()->wherePivot('role', OrganizationRole::OWNER->value)->count();
    }
}
