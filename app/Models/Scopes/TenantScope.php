<?php

namespace App\Models\Scopes;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Regras de visualização por model. Todo model com a trait Tenantable passa por aqui:
 * adicione um `if ($model instanceof X)` com a regra de cada um.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin do sistema vê tudo.
        if ($user->hasRole('admin')) {
            return;
        }

        // Só as organizações das quais o usuário é membro.
        if ($model instanceof Organization) {
            $builder->whereHas('members', fn (Builder $q) => $q->whereKey($user->getKey()));
        }
    }
}
