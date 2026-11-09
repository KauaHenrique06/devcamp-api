<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Use em models que pertencem a uma organização (precisam da coluna `organization_id`).
 *
 * - Scope global: toda consulta é filtrada pela organização atual (header X-Organization-Id).
 * - Ao criar, `organization_id` é preenchido automaticamente.
 * - Sem organização atual numa requisição HTTP, a consulta não retorna nada (falha fechada),
 *   para que esquecer o middleware nunca vaze dados de outra organização.
 *
 * Para ignorar o filtro de propósito: Model::withoutGlobalScope('organization').
 */
trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            $current = app(CurrentOrganization::class);
            $column = $query->getModel()->qualifyColumn('organization_id');

            if ($current->check()) {
                $query->where($column, $current->id());
            } elseif (! app()->runningInConsole()) {
                $query->whereRaw('1 = 0');
            }
        });

        static::creating(function ($model) {
            $model->organization_id ??= app(CurrentOrganization::class)->id();
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
