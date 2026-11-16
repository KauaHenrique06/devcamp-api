<?php

namespace App\Services\Project;

use App\Enums\OrganizationRole;
use App\Enums\ProjectRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Collection;

class ProjectMemberService 
{
    public function syncProjectManagersBasedOnOrganizationManagers(Project $project): void
    {
        $this->upsertManagers(
            $this->getOrganizationOwnersAndAdminsIds($project->organization),
            collect([$project->getKey()])
        );
    }

    public function syncAllProjectManagers(Organization $organization)
    {
        $projectIds = $organization
            ->projects()
            ->withoutGlobalScope(TenantScope::class)
            ->pluck('id');
        
        $this->upsertManagers(
            $this->getOrganizationOwnersAndAdminsIds($organization),
            $projectIds
        );
    }

    private function getOrganizationOwnersAndAdminsIds(Organization $organization): Collection
    {
        return $organization
            ->members()
            ->wherePivotIn('role', [
                    OrganizationRole::OWNER->value, 
                    OrganizationRole::ADMIN->value
                ]
            )
            ->pluck('users.id');
    }

    private function upsertManagers(Collection $managerIds, Collection $projectIds)
    {
        if($managerIds->isEmpty() || $projectIds->isEmpty()) {
            return;
        }

        $now = now();

        $rows = $projectIds->crossJoin($managerIds)
            ->map(fn (array $pair) => [
                'project_id' => $pair[0],
                'user_id' => $pair[1],
                'role' => ProjectRole::MANAGER->value,
                'created_at' => $now,
                'updated_at' => $now
            ])
            ->all();
        
        (new Project)->members()->newPivotStatement()->upsert(
            $rows,
            ['project_id', 'user_id'],
            ['role', 'updated_at']   
        );
    }
}