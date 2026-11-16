<?php

namespace App\Services\Project;

use App\Http\Resources\Project\ProjectCollection;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectService 
{
    public function __construct(
        protected CurrentOrganization $currentOrganization,
        protected ProjectMemberService $projectMemberService
    ) {}

    public function index(array $data): ProjectCollection
    {
        $page = $data['page'] ?? 1;
        $perPage = $data['per_page'] ?? 10;

        $search = $data['search'] ?? null;

        $projects = Project::query()
            ->when($search, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereLike('name', "%{$search}%")
                        ->orWhereLike('description', "%{$search}%");
                });
            })
            ->paginate($perPage, ['*'], 'page', $page);

        return new ProjectCollection($projects);
    }

    public function show(array $data): ProjectResource 
    {
        $project = Project::with(['organization', 'creator', 'members'])
            ->findOrFail($data['id']);

        return new ProjectResource($project);
    }

    public function store(array $data): ProjectResource
    {
        $authUserId = Auth::user()->id;

        return DB::transaction(function () use ($authUserId, $data): ProjectResource {
            $project = Project::create([
                'organization_id' => $this->currentOrganization->id(),
                'created_by' => $authUserId,
                'name' => $data['name'],
                'description' => $data['description'],
                'base_color_hex' => $data['base_color_hex'] ?? null,
            ]);

            if ( ! empty($data['logo']) ) {
                $project->addMedia($data['logo'])->toMediaCollection('logo');
            }

                        
            // Precisa vir antes de qualquer consulta a Project: até aqui o projeto não tem membros
            // e o scope de membro o esconde de todos (inclusive do criador).
            $this->projectMemberService->syncProjectManagersBasedOnOrganizationManagers($project);

            return new ProjectResource($project);
        });
    }

    public function update(array $data): ProjectResource
    {
        $project = Project::findOrFail($data['id']);

        return DB::transaction(function () use ($project, $data): ProjectResource {
            $project->update(Arr::only($data, ['name', 'description', 'base_color_hex']));

            if ( ! empty($data['logo']) ) {
                $project->addMedia($data['logo'])->toMediaCollection('logo');
                $project->load('media');
            }

            return new ProjectResource($project); 
        });
    }

    public function delete(array $data): void
    {
        $project = Project::findOrFail($data['id']);

        DB::transaction(function () use ($project): void {
            // Inserir aqui futuramente a delecao em cascata dos models filhos
            
            $project->delete();
        });
    }

    public function restore(array $data): ProjectResource
    {
        $project = Project::withTrashed()->findOrFail($data['id']);

        return DB::transaction(function () use ($project): ProjectResource {
            $project->restore();

            // Inserir aqui futuramente o restore em cascata do pai para os filhos

            return new ProjectResource($project);
        });
    }

    public function toggleArchive(array $data): ProjectResource
    {
        $project = Project::findOrFail($data['id']);

        return DB::transaction(function () use ($project): ProjectResource {
            $project->update([
                'is_archived' => ! $project->is_archived,
            ]);

            return new ProjectResource($project);
        });
    }

    public function forceDelete(array $data): void
    {
        $project = Project::withTrashed()->findOrFail($data['id']);

        DB::transaction(function () use ($project): void {
            // Inserir aqui futuramente a delecao em cascata dos models filhos
            
            $project->forceDelete();
        });
    }
}