<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use HasUuids, SoftDeletes, Tenantable, InteractsWithMedia;

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

    #[Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml']);
    }
}
