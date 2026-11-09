<?php

namespace App\Http\Middleware;

use App\Builder\ReturnApi;
use App\Models\Organization;
use App\Support\Tenancy\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lê o header X-Organization-Id, confere se o usuário é membro
 * e define a organização atual para o scope global BelongsToOrganization.
 *
 * Uso nas rotas (depois do auth.api): ->middleware(['auth.api', 'organization'])
 */
class ResolveCurrentOrganization
{
    public const HEADER = 'X-Organization-Id';

    public function __construct(private CurrentOrganization $current) {}

    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->header(self::HEADER);

        if (! $organizationId || ! Str::isUuid($organizationId)) {
            return ReturnApi::error('Informe a organização no header '.self::HEADER.'.', null, 400);
        }

        $user = $request->user();
        $organization = Organization::find($organizationId);

        $allowed = $organization
            && ($user->hasRole('admin') || $organization->roleOf($user) !== null);

        if (! $allowed) {
            return ReturnApi::error('Organização não encontrada.', null, 404);
        }

        $this->current->set($organization);

        return $next($request);
    }
}
