<?php

namespace App\Http\Controllers\Project;

use App\Builder\ReturnApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ProjectDeleteRequest;
use App\Http\Requests\Project\ProjectForceDeleteRequest;
use App\Http\Requests\Project\ProjectIndexRequest;
use App\Http\Requests\Project\ProjectRestoreRequest;
use App\Http\Requests\Project\ProjectShowRequest;
use App\Http\Requests\Project\ProjectStoreRequest;
use App\Http\Requests\Project\ProjectToggleArchiveRequest;
use App\Http\Requests\Project\ProjectUpdateRequest;
use App\Models\Project as ProjectModel;
use App\Services\Project\ProjectService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;

class Project extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    #[Endpoint(operationId: 'projectsIndex', title: 'Listar projetos', description: '**operationId:** `projectsIndex` — Lista os projetos da organização atual (header `X-Organization-Id`), paginados. Aceita `search` (filtra por `name` ou `description`), `per_page` e `page`. Em **200**, `data` segue o schema **Project Collection** (`App\\Http\\Resources\\Project\\ProjectCollection`). Requer a permissão `projects.view`.')]
    public function index(ProjectIndexRequest $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', ProjectModel::class);

            $data = $this->projectService->index($request->validated());

            return ReturnApi::success($data, 'Projetos listados com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao listar projetos: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsShow', title: 'Exibir projeto', description: '**operationId:** `projectsShow` — Exibe um projeto da organização atual (header `X-Organization-Id`) pelo `id` informado no path. O `id` é validado como `uuid` em `ProjectShowRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`). Retorna **404** quando o projeto não existe. Requer a permissão `projects.view`.')]
    public function show(ProjectShowRequest $request): JsonResponse
    {
        try {
            $this->authorize('view', ProjectModel::class);

            $data = $this->projectService->show($request->validated());

            return ReturnApi::success($data, 'Projeto encontrado.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao exibir projeto: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsStore', title: 'Cadastrar projeto', description: '**operationId:** `projectsStore` — Cria um projeto na organização atual (header `X-Organization-Id`); quem cria fica registrado em `created_by`. `name` e `description` são obrigatórios; `logo` é um arquivo de imagem opcional (`png`, `jpeg`, `webp` ou `svg`, até 2 MB), salvo via Media Library na coleção `logo` — por isso o corpo deve ser enviado como `multipart/form-data`. Roda dentro de uma transação. Em **201**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`). Requer a permissão `projects.create` e ser `owner` ou `admin` da organização.')]
    public function store(ProjectStoreRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', ProjectModel::class);

            $data = $this->projectService->store($request->validated());

            return ReturnApi::success($data, 'Projeto cadastrado com sucesso.', 201);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao cadastrar projeto: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsUpdate', title: 'Atualizar projeto', description: '**operationId:** `projectsUpdate` — Atualiza o projeto identificado pelo `id` do path, na organização atual (header `X-Organization-Id`). O `id` é validado como `uuid` em `ProjectUpdateRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. `name` e `description` são obrigatórios; um novo `logo` (`png`, `jpeg`, `webp` ou `svg`, até 2 MB) substitui o anterior (coleção `singleFile`). Para enviar arquivo, use `multipart/form-data` com **POST** e o campo `_method=PUT` — o PHP não lê arquivos de um PUT multipart. Roda dentro de uma transação. Em **200**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`). Retorna **404** quando o projeto não existe. Requer a permissão `projects.update` e ser `owner` ou `admin` da organização.')]
    public function update(ProjectUpdateRequest $request): JsonResponse
    {
        try {
            $this->authorize('update', ProjectModel::class);

            $data = $this->projectService->update($request->validated());

            return ReturnApi::success($data, 'Projeto atualizado com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao atualizar projeto: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsDelete', title: 'Excluir projeto', description: '**operationId:** `projectsDelete` — Exclui (soft delete) o projeto identificado pelo `id` do path, na organização atual (header `X-Organization-Id`), dentro de uma transação. O `id` é validado como `uuid` em `ProjectDeleteRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` é `null`. Retorna **404** quando o projeto não existe. Requer a permissão `projects.delete` e ser `owner` ou `admin` da organização.')]
    public function delete(ProjectDeleteRequest $request): JsonResponse
    {
        try {
            $this->authorize('delete', ProjectModel::class);

            $this->projectService->delete($request->validated());

            return ReturnApi::success(null, 'Projeto excluído com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao excluir projeto: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsRestore', title: 'Restaurar projeto', description: '**operationId:** `projectsRestore` — Restaura um projeto excluído (soft delete) identificado pelo `id` do path, na organização atual (header `X-Organization-Id`), dentro de uma transação. O `id` é validado como `uuid` em `ProjectRestoreRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`). Retorna **404** quando o projeto não existe. Requer a permissão `projects.restore` e ser `owner` ou `admin` da organização.')]
    public function restore(ProjectRestoreRequest $request): JsonResponse
    {
        try {
            $this->authorize('restore', ProjectModel::class);

            $data = $this->projectService->restore($request->validated());

            return ReturnApi::success($data, 'Projeto restaurado com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao restaurar projeto: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsToggleArchive', title: 'Arquivar/desarquivar projeto', description: '**operationId:** `projectsToggleArchive` — Alterna o campo `is_archived` do projeto identificado pelo `id` do path, na organização atual (header `X-Organization-Id`): se estiver arquivado, desarquiva; se não, arquiva. Roda dentro de uma transação. O `id` é validado como `uuid` em `ProjectToggleArchiveRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`) já com o novo valor de `is_archived`. Requer a permissão `projects.archive` e ser `owner` ou `admin` da organização.')]
    public function toggleArchive(ProjectToggleArchiveRequest $request): JsonResponse
    {
        try {
            $this->authorize('toggleArchive', ProjectModel::class);

            $data = $this->projectService->toggleArchive($request->validated());

            $message = $data->is_archived
                ? 'Projeto arquivado com sucesso.'
                : 'Projeto desarquivado com sucesso.';

            return ReturnApi::success($data, $message);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao arquivar/desarquivar projeto: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectsForceDelete', title: 'Remover projeto permanentemente', description: '**operationId:** `projectsForceDelete` — Remove permanentemente (force delete) o projeto identificado pelo `id` do path, na organização atual (header `X-Organization-Id`), dentro de uma transação. A ação não pode ser desfeita. O `id` é validado como `uuid` em `ProjectForceDeleteRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` é `null`. Retorna **404** quando o projeto não existe. Requer ser `owner` ou `admin` da organização.')]
    public function forceDelete(ProjectForceDeleteRequest $request): JsonResponse
    {
        try {
            $this->authorize('forceDelete', ProjectModel::class);

            $this->projectService->forceDelete($request->validated());

            return ReturnApi::success(null, 'Projeto removido permanentemente com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao remover projeto permanentemente: '.$e->getMessage(), null, 500);
        }
    }
}
