<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait Tenantable
{
    protected static function bootTenantable(): void
    {
        /** @disregard P1013 */
        static::addGlobalScope(new TenantScope());
    }
}
