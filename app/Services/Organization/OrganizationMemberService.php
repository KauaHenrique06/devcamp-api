<?php

namespace App\Services\Organization;

use App\Enums\OrganizationRole;
use App\Exceptions\ApiException;
use App\Http\Resources\Organization\OrganizationMemberCollection;
use App\Http\Resources\Organization\OrganizationMemberResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OrganizationMemberService
{
    public function index(array $data): OrganizationMemberCollection
    {
        $organization = Organization::findOrFail($data['id']);

        $search = $data['search'] ?? null;

        $members = $organization->members()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereLike('users.name', "%{$search}%")
                        ->orWhereLike('users.email', "%{$search}%");
                });
            })
            ->orderBy('users.name')
            ->paginate((int) ($data['per_page'] ?? 10), ['users.*'], 'page', (int) ($data['page'] ?? 1));

        return new OrganizationMemberCollection($members);
    }

    public function store(array $data): OrganizationMemberResource
    {
        $organization = Organization::findOrFail($data['id']);
        $role = OrganizationRole::from($data['role']);
        $member = User::findOrFail($data['user_id']);

        if ($organization->roleOf($member)) {
            throw new ApiException('Este usuário já é membro da organização.', 422);
        }

        DB::transaction(fn () => $organization->members()->attach($member->getKey(), ['role' => $role->value]));

        return $this->memberResource($organization, $member);
    }

    public function update(array $data): OrganizationMemberResource
    {
        $organization = Organization::findOrFail($data['id']);
        $member = $this->findMember($organization, $data['user_id']);
        $newRole = OrganizationRole::from($data['role']);

        DB::transaction(function () use ($organization, $member, $newRole) {
            // lockForUpdate evita que duas requisições simultâneas removam os dois últimos donos.
            $this->lockMembers($organization);

            if ($newRole !== OrganizationRole::OWNER) {
                $this->ensureNotLastOwner($organization, $member);
            }

            $organization->members()->updateExistingPivot($member->getKey(), ['role' => $newRole->value]);
        });

        return $this->memberResource($organization, $member);
    }

    public function delete(array $data): void
    {
        $organization = Organization::findOrFail($data['id']);
        $member = $this->findMember($organization, $data['user_id']);

        DB::transaction(function () use ($organization, $member) {
            $this->lockMembers($organization);
            $this->ensureNotLastOwner($organization, $member);

            $organization->members()->detach($member->getKey());
        });
    }

    /** Busca o usuário só se ele for membro; senão, 404. */
    private function findMember(Organization $organization, string $userId): User
    {
        $member = $organization->members()->whereKey($userId)->first();

        if (! $member) {
            throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
        }

        return $member;
    }

    /** A organização nunca pode ficar sem dono. */
    private function ensureNotLastOwner(Organization $organization, User $member): void
    {
        if ($organization->roleOf($member) === OrganizationRole::OWNER && $organization->ownersCount() <= 1) {
            throw new ApiException('A organização precisa ter pelo menos um dono. Defina outro dono antes.', 422);
        }
    }

    private function lockMembers(Organization $organization): void
    {
        DB::table('organization_user')
            ->where('organization_id', $organization->getKey())
            ->lockForUpdate()
            ->get();
    }

    private function memberResource(Organization $organization, User $member): OrganizationMemberResource
    {
        return new OrganizationMemberResource($organization->members()->whereKey($member->getKey())->firstOrFail());
    }
}
