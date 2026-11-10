<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Chamada no controller: $this->authorize('update', [Organization::class, $id]).
 *
 * Cada regra combina duas checagens:
 *  1. Permissão do Spatie: o cargo do usuário permite essa ação? (ex.: organizations.update)
 *  2. Papel na organização: ele é owner/admin/member DESTA organização?
 *
 * Quem não é membro nem chega aqui com a organização: o TenantScope faz o findOrFail dar 404.
 */
class OrganizationPolicy
{
    /** Admin do sistema (role "admin" do Spatie) pode tudo, em qualquer organização. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('organizations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('organizations.create');
    }

    public function view(User $user, string $id): Response
    {
        if (! $user->can('organizations.view')) {
            return Response::deny('Você não tem permissão para visualizar organizações.');
        }

        Organization::findOrFail($id);

        return Response::allow();
    }

    public function update(User $user, string $id): Response
    {
        if (! $user->can('organizations.update')) {
            return Response::deny('Você não tem permissão para editar organizações.');
        }

        return $this->myRole($user, $id)?->canManage()
            ? Response::allow()
            : Response::deny('Apenas o dono ou um administrador da organização pode editá-la.');
    }

    public function delete(User $user, string $id): Response
    {
        if (! $user->can('organizations.delete')) {
            return Response::deny('Você não tem permissão para excluir organizações.');
        }

        return $this->myRole($user, $id) === OrganizationRole::OWNER
            ? Response::allow()
            : Response::deny('Apenas o dono pode excluir a organização.');
    }

    public function viewMembers(User $user, string $id): Response
    {
        if (! $user->can('organizations.members.view')) {
            return Response::deny('Você não tem permissão para visualizar membros.');
        }

        Organization::findOrFail($id);

        return Response::allow();
    }

    /** Adicionar membro. Só o owner pode adicionar outro owner. */
    public function addMember(User $user, string $id, OrganizationRole $newRole): Response
    {
        if (! $user->can('organizations.members.manage')) {
            return Response::deny('Você não tem permissão para gerenciar membros.');
        }

        $myRole = $this->myRole($user, $id);

        if (! $myRole?->canManage()) {
            return Response::deny('Apenas o dono ou um administrador pode adicionar membros.');
        }

        if ($newRole === OrganizationRole::OWNER && $myRole !== OrganizationRole::OWNER) {
            return Response::deny('Apenas o dono pode adicionar outro dono.');
        }

        return Response::allow();
    }

    /** Alterar o papel de um membro. Admin não mexe em owner nem promove a owner. */
    public function updateMember(User $user, string $id, string $memberId, OrganizationRole $newRole): Response
    {
        if (! $user->can('organizations.members.manage')) {
            return Response::deny('Você não tem permissão para gerenciar membros.');
        }

        $organization = Organization::findOrFail($id);
        $myRole = $organization->roleOf($user);

        if (! $myRole?->canManage()) {
            return Response::deny('Apenas o dono ou um administrador pode alterar membros.');
        }

        $touchesOwner = $newRole === OrganizationRole::OWNER
            || $organization->roleOf($memberId) === OrganizationRole::OWNER;

        if ($touchesOwner && $myRole !== OrganizationRole::OWNER) {
            return Response::deny('Apenas o dono pode alterar o papel de dono.');
        }

        return Response::allow();
    }

    /** Remover membro. Qualquer membro pode sair (remover a si mesmo). */
    public function removeMember(User $user, string $id, string $memberId): Response
    {
        $organization = Organization::findOrFail($id);

        if ($memberId === $user->getKey()) {
            return Response::allow();
        }

        if (! $user->can('organizations.members.manage')) {
            return Response::deny('Você não tem permissão para gerenciar membros.');
        }

        $myRole = $organization->roleOf($user);

        if (! $myRole?->canManage()) {
            return Response::deny('Apenas o dono ou um administrador pode remover membros.');
        }

        if ($organization->roleOf($memberId) === OrganizationRole::OWNER && $myRole !== OrganizationRole::OWNER) {
            return Response::deny('Apenas o dono pode remover outro dono.');
        }

        return Response::allow();
    }

    private function myRole(User $user, string $id): ?OrganizationRole
    {
        return Organization::findOrFail($id)->roleOf($user);
    }
}
