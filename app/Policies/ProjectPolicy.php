<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function __construct(protected CurrentOrganization $currentOrganization) {}

    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can('projects.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if( !$user->can('projects.create') ) {
            return Response::deny('Voce nao tem permissao para criar um projeto!');
        }

        return $this->currentOrganization->get()?->roleOf($user)?->canManage() 
            ? Response::allow()
            : Response::deny("Apenas o dono da organizacao e os administradores podem criar projetos");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): Response
    {
        if( !$user->can('projects.update') ) {
            return Response::deny('Voce nao tem permissao para atualizar um projeto!');
        }

        return $this->currentOrganization->get()?->roleOf($user)?->canManage() 
            ? Response::allow()
            : Response::deny("Apenas o dono da organizacao e os administradores podem atualizar projetos");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): Response
    {
        if( !$user->can('projects.delete') ) {
            return Response::deny('Voce nao tem permissao para deletar um projeto!');
        }

        return $this->currentOrganization->get()?->roleOf($user)?->canManage() 
            ? Response::allow()
            : Response::deny("Apenas o dono da organizacao e os administradores podem deletar projetos");
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): Response
    {
        if( !$user->can('projects.restore') ) {
            return Response::deny('Voce nao tem permissao para restaurar um projeto!');
        }

        return $this->currentOrganization->get()?->roleOf($user)?->canManage() 
            ? Response::allow()
            : Response::deny("Apenas o dono da organizacao e os administradores podem restaurar projetos");
    }

    /**
     * Determine whether the user can archive/unarchive the model.
     */
    public function toggleArchive(User $user): Response
    {
        if( !$user->can('projects.archive') ) {
            return Response::deny('Voce nao tem permissao para arquivar um projeto!');
        }

        return $this->currentOrganization->get()?->roleOf($user)?->canManage()
            ? Response::allow()
            : Response::deny("Apenas o dono da organizacao e os administradores podem arquivar projetos");
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): Response
    {
        if( !$user->can('projects.update') ) {
            return Response::deny('Voce nao tem permissao para remover permanentemente um projeto!');
        }

        return $this->currentOrganization->get()?->roleOf($user)?->canManage() 
            ? Response::allow()
            : Response::deny("Apenas o dono da organizacao e os administradores podem remover permanentemente projetos");
    }
}
