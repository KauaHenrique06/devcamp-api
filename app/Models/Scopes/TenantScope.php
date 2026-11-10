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
 * - Demais models: precisam da coluna `organization_id` e são filtrados pela organização
 *   atual (header X-Organization-Id, resolvido pelo middleware `organization`).
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
            $builder->where($model->qualifyColumn('organization_id'), $current->id());
        } elseif (! app()->runningInConsole()) {
            // Sem organização atual numa requisição HTTP, não retorna nada (falha fechada),
            // para que esquecer o middleware nunca vaze dados de outra organização.
            $builder->whereRaw('1 = 0');
        }
    }

    private function applyMembership(Builder $builder): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin do sistema vê tudo.
        if ($user->hasRole('admin')) {
            return;
        }

        $builder->whereHas('members', fn (Builder $q) => $q->whereKey($user->getKey()));
    }
}
