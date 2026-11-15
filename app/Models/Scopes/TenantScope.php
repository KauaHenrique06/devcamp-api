<?php

namespace App\Models\Scopes;

use App\Models\Organization;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Regras de visualização por model. Todo model com a trait Tenantable passa por aqui.
 *
 * - Organization: só as organizações das quais o usuário é membro (admin do sistema vê todas).
 * - Demais models: filtrados pela organização atual (header X-Organization-Id, resolvido pelo
 *   middleware `organization`). O COMO filtrar fica no model, em applyTenantFilter():
 *   o padrão da trait usa `organization_id`; models sem essa coluna sobrescrevem o método.
 * - Sem organização atual: em HTTP não retorna nada; no console (seeders, tinker, jobs) não filtra.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($model instanceof Organization) {
            $this->applyMembership($builder);

            return;
        }

        $current = app(CurrentOrganization::class);

        if ($current->check()) {
            /** @var Model&\App\Traits\Tenantable $model */
            $model->applyTenantFilter($builder, $current->id());

            return;
        }

        // Sem organização atual numa requisição HTTP, não retorna nada (falha fechada):
        // esquecer o middleware `organization` numa rota nunca vaza dados de outra organização.
        if (! app()->runningInConsole()) {
            $builder->whereRaw('1 = 0');
        }
    }

    private function applyMembership(Builder $builder): void
    {
        if (! Auth::check()) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin do sistema vê tudo.
        if ($user->hasRole('admin')) {
            return;
        }

        $builder->whereHas('members', fn (Builder $q) => $q->whereKey($user->getKey()));
    }
}
