<?php

namespace App\Models;

use App\Enums\ProjectRole;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use HasUuids, SoftDeletes, InteractsWithMedia;

    use Tenantable {
        applyTenantFilter as applyOrganizationFilter;
    }

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'description',
        'base_color_hex',
        'is_archived'
    ];

    protected $casts = [
        'is_archived' => 'boolean'
    ];

    protected $with = ['media'];

    protected $appends = ['logo_url'];

    protected $hidden = ['media'];

    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->getFirstMediaUrl('logo') ?: null
        );
    }

    #[Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml']);
    }

    /** Sobrescreve Tenantable::applyTenantFilter(). */
    public function applyTenantFilter(Builder $builder, string $organizationId): void
    {
        $this->applyOrganizationFilter($builder, $organizationId);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if(! $user || $user->hasRole('admin')) {
            return;
        }

        $builder->whereHas('members', fn(Builder $q) => $q->whereKey($user->getKey()));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class, 
            'created_by'
        );
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    /** Papel do usuário (model ou id) neste projeto, ou null se ele não for membro. */
    public function roleOf(User|string $user): ?ProjectRole
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $role = $this->members()->whereKey($userId)->value('project_user.role');

        return $role ? ProjectRole::from($role) : null;
    }

}
