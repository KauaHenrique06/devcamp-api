<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * Para ignorar o filtro de propósito: Model::withoutGlobalScope(TenantScope::class).
 */
trait Tenantable
{
    protected static function bootTenantable(): void
    {
        /** @disregard P1013 */
        static::addGlobalScope(new TenantScope());

        // Ao criar dados de uma organização, `organization_id` vem da organização atual (header).
        static::creating(function (Model $model) {
            if (! $model instanceof Organization) {
                $model->organization_id ??= app(CurrentOrganization::class)->id();
            }
        });
    }
}
