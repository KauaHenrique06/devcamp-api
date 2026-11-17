<?php

namespace App\Http\Controllers\Project;

use App\Builder\ReturnApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMember\AttachProjectNewMembersRequest;
use App\Http\Requests\ProjectMember\DetachProjectMembersRequest;
use App\Models\Project;
use App\Services\Project\ProjectMemberService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ProjectMember extends Controller
{
    public function __construct(protected ProjectMemberService $projectMemberService) {}

    #[Endpoint(operationId: 'projectMembersAttach', title: 'Adicionar membros ao projeto', description: '**operationId:** `projectMembersAttach` — Adiciona um ou mais usuários ao projeto do `id` do path, na organização atual (header `X-Organization-Id`). O `id` é validado como `uuid` em `AttachProjectNewMembersRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. O corpo traz `users`, uma lista de objetos `{ id, role }`: `id` é o `uuid` do usuário e `role` é o papel no projeto (`manager`, `lead` ou `developer`). Usuários que já são membros não são duplicados: apenas têm o `role` atualizado. Nenhum membro existente é removido. Roda dentro de uma transação. Em **201**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`). Retorna **422** quando o corpo é inválido e **403** quando o usuário não tem permissão. Requer ser `manager` do projeto (ou ter a role `admin`).')]
    public function attachNewMembers(AttachProjectNewMembersRequest $request): JsonResponse
    {
        try {
            $this->authorize('addMember', [Project::class, $request->validated('id')]);

            $data = $this->projectMemberService->attachNewMembers($request->validated());

            return ReturnApi::success($data, 'Novos membros adicionados com sucesso.', 201);
        } catch (AuthorizationException $e) {
            /**
             * @status 403
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), null, $e->status() ?? 403);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao adicionar os novos membros: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'projectMembersDetach', title: 'Remover membros do projeto', description: '**operationId:** `projectMembersDetach` — Remove um ou mais usuários do projeto do `id` do path, na organização atual (header `X-Organization-Id`). O `id` é validado como `uuid` em `DetachProjectMembersRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. O corpo traz `users`, uma lista de `uuid`s dos usuários a remover. IDs que não são membros do projeto são ignorados. Roda dentro de uma transação. Em **200**, `data` segue o schema **Project Resource** (`App\\Http\\Resources\\Project\\ProjectResource`). Retorna **422** quando o corpo é inválido e **403** quando o usuário não tem permissão. Requer ser `manager` do projeto (ou ter a role `admin`).')]
    public function detachMembers(DetachProjectMembersRequest $request): JsonResponse
    {
        try {
            $this->authorize('removeMember', [Project::class, $request->validated('id')]);

            $data = $this->projectMemberService->detachMembers($request->validated());

            return ReturnApi::success($data, 'Membros removidos com sucesso.');
        } catch (AuthorizationException $e) {
            /**
             * @status 403
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), null, $e->status() ?? 403);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao remover os membros: '.$e->getMessage(), null, 500);
        }
    }
}
