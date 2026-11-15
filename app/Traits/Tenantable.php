<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Eloquent\Builder;
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
                /** @var Model&Tenantable $model */
                $model->assignTenant(app(CurrentOrganization::class)->id());
            }
        });
    }

    /**
     * Restringe a query à organização informada.
     *
     * Padrão: filtra pela coluna `organization_id` do próprio model.
     * Models sem essa coluna sobrescrevem este método (ex.: filtrar via relacionamento).
    */
    public function applyTenantFilter(Builder $builder, string $organizationId): void
    {
        $builder->where($this->qualifyColumn('organization_id'), $organizationId);
    }

    /**
     * Vincula o model à organização ao ser criado.
     *
     * Padrão: preenche `organization_id`, sem sobrescrever um valor já informado.
     * Models sem essa coluna sobrescrevem este método (normalmente com corpo vazio).
     */
    public function assignTenant(?string $organizationId): void
    {
        $this->organization_id ??= $organizationId;
    }
}
