<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Cada regra combina duas checagens:
 *  1. Permissão do Spatie: o cargo do usuário permite essa ação? (ex.: organizations.update)
 *  2. Papel na organização: ele é owner/admin/member DESTA organização?
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

    public function view(User $user, Organization $organization): Response
    {
        if (! $user->can('organizations.view')) {
            return Response::deny('Você não tem permissão para visualizar organizações.');
        }

        // Quem não é membro recebe 404: nem fica sabendo que a organização existe.
        return $organization->roleOf($user)
            ? Response::allow()
            : Response::denyAsNotFound('Organização não encontrada.');
    }

    public function update(User $user, Organization $organization): Response
    {
        if (! $user->can('organizations.update')) {
            return Response::deny('Você não tem permissão para editar organizações.');
        }

        return $this->requireRole($user, $organization, fn (OrganizationRole $role) => $role->canManage(),
            'Apenas o dono ou um administrador da organização pode editá-la.');
    }

    public function delete(User $user, Organization $organization): Response
    {
        if (! $user->can('organizations.delete')) {
            return Response::deny('Você não tem permissão para excluir organizações.');
        }

        return $this->requireRole($user, $organization, fn (OrganizationRole $role) => $role === OrganizationRole::OWNER,
            'Apenas o dono pode excluir a organização.');
    }

    public function viewMembers(User $user, Organization $organization): Response
    {
        if (! $user->can('organizations.members.view')) {
            return Response::deny('Você não tem permissão para visualizar membros.');
        }

        return $organization->roleOf($user)
            ? Response::allow()
            : Response::denyAsNotFound('Organização não encontrada.');
    }

    /** Adicionar membro. Só o owner pode adicionar outro owner. */
    public function addMember(User $user, Organization $organization, OrganizationRole $newRole): Response
    {
        if (! $user->can('organizations.members.manage')) {
            return Response::deny('Você não tem permissão para gerenciar membros.');
        }

        $myRole = $organization->roleOf($user);

        if (! $myRole) {
            return Response::denyAsNotFound('Organização não encontrada.');
        }

        if (! $myRole->canManage()) {
            return Response::deny('Apenas o dono ou um administrador pode adicionar membros.');
        }

        if ($newRole === OrganizationRole::OWNER && $myRole !== OrganizationRole::OWNER) {
            return Response::deny('Apenas o dono pode adicionar outro dono.');
        }

        return Response::allow();
    }

    /** Alterar o papel de um membro. Admin não mexe em owner nem promove a owner. */
    public function updateMember(User $user, Organization $organization, User $member, OrganizationRole $newRole): Response
    {
        if (! $user->can('organizations.members.manage')) {
            return Response::deny('Você não tem permissão para gerenciar membros.');
        }

        $myRole = $organization->roleOf($user);

        if (! $myRole) {
            return Response::denyAsNotFound('Organização não encontrada.');
        }

        if (! $myRole->canManage()) {
            return Response::deny('Apenas o dono ou um administrador pode alterar membros.');
        }

        $touchesOwner = $newRole === OrganizationRole::OWNER
            || $organization->roleOf($member) === OrganizationRole::OWNER;

        if ($touchesOwner && $myRole !== OrganizationRole::OWNER) {
            return Response::deny('Apenas o dono pode alterar o papel de dono.');
        }

        return Response::allow();
    }

    /** Remover membro. Qualquer membro pode sair (remover a si mesmo). */
    public function removeMember(User $user, Organization $organization, User $member): Response
    {
        $myRole = $organization->roleOf($user);

        if (! $myRole) {
            return Response::denyAsNotFound('Organização não encontrada.');
        }

        if ($member->is($user)) {
            return Response::allow();
        }

        if (! $user->can('organizations.members.manage')) {
            return Response::deny('Você não tem permissão para gerenciar membros.');
        }

        if (! $myRole->canManage()) {
            return Response::deny('Apenas o dono ou um administrador pode remover membros.');
        }

        if ($organization->roleOf($member) === OrganizationRole::OWNER && $myRole !== OrganizationRole::OWNER) {
            return Response::deny('Apenas o dono pode remover outro dono.');
        }

        return Response::allow();
    }

    /** @param  callable(OrganizationRole): bool  $allowed */
    private function requireRole(User $user, Organization $organization, callable $allowed, string $message): Response
    {
        $role = $organization->roleOf($user);

        if (! $role) {
            return Response::denyAsNotFound('Organização não encontrada.');
        }

        return $allowed($role) ? Response::allow() : Response::deny($message);
    }
}
