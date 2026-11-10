<?php

namespace App\Http\Controllers\Organization;

use App\Builder\ReturnApi;
use App\Enums\OrganizationRole;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\OrganizationMemberDeleteRequest;
use App\Http\Requests\Organization\OrganizationMemberIndexRequest;
use App\Http\Requests\Organization\OrganizationMemberStoreRequest;
use App\Http\Requests\Organization\OrganizationMemberUpdateRequest;
use App\Models\Organization;
use App\Services\Organization\OrganizationMemberService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class OrganizationMember extends Controller
{
    public function __construct(public OrganizationMemberService $memberService) {}

    #[Endpoint(operationId: 'organizationMembersIndex', title: 'Listar membros da organização', description: '**operationId:** `organizationMembersIndex` — Lista os membros da organização do `id` do path, paginados e ordenados por nome. Aceita `search` (filtra por `name` ou `email`), `per_page` e `page`. Cada item segue o schema **Organization Member Resource** (`id`, `name`, `email`, `role`, `role_label`, `joined_at`). Requer a permissão `organizations.members.view` e ser membro da organização (quem não é membro recebe **404**).')]
    public function index(OrganizationMemberIndexRequest $request): JsonResponse
    {
        try {
            $this->authorize('viewMembers', [Organization::class, $request->validated('id')]);

            $data = $this->memberService->index($request->validated());

            return ReturnApi::success($data, 'Membros listados com sucesso.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Organização não encontrada.', null, 404);
        } catch (AuthorizationException $e) {
            /**
             * @status 403
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), null, $e->status() ?? 403);
        }
    }

    #[Endpoint(operationId: 'organizationMembersStore', title: 'Adicionar membro', description: '**operationId:** `organizationMembersStore` — Adiciona o usuário `user_id` à organização do `id` do path com o papel `role` (`owner`, `admin` ou `member`; padrão `member`). Retorna **422** se o usuário já for membro. Requer a permissão `organizations.members.manage` e ser `owner` ou `admin` da organização; apenas um `owner` pode adicionar outro `owner`.')]
    public function store(OrganizationMemberStoreRequest $request): JsonResponse
    {
        try {
            $this->authorize('addMember', [
                Organization::class,
                $request->validated('id'),
                OrganizationRole::from($request->validated('role')),
            ]);

            $data = $this->memberService->store($request->validated());

            return ReturnApi::success($data, 'Membro adicionado com sucesso.', 201);
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Organização não encontrada.', null, 404);
        } catch (AuthorizationException $e) {
            /**
             * @status 403
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), null, $e->status() ?? 403);
        } catch (ApiException $e) {
            /**
             * @status 422
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'organizationMembersUpdate', title: 'Alterar papel do membro', description: '**operationId:** `organizationMembersUpdate` — Altera o papel (`role`) do membro `userId` na organização `id`. Retorna **404** se o usuário não for membro e **422** ao rebaixar o último `owner`. Requer a permissão `organizations.members.manage` e ser `owner` ou `admin`; apenas um `owner` pode promover alguém a `owner` ou alterar outro `owner`.')]
    public function update(OrganizationMemberUpdateRequest $request): JsonResponse
    {
        try {
            $this->authorize('updateMember', [
                Organization::class,
                $request->validated('id'),
                $request->validated('user_id'),
                OrganizationRole::from($request->validated('role')),
            ]);

            $data = $this->memberService->update($request->validated());

            return ReturnApi::success($data, 'Membro atualizado com sucesso.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Organização ou membro não encontrado.', null, 404);
        } catch (AuthorizationException $e) {
            /**
             * @status 403
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), null, $e->status() ?? 403);
        } catch (ApiException $e) {
            /**
             * @status 422
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'organizationMembersDelete', title: 'Remover membro', description: '**operationId:** `organizationMembersDelete` — Remove o membro `userId` da organização `id`. Qualquer membro pode remover a si mesmo (sair da organização). Remover outra pessoa requer a permissão `organizations.members.manage` e ser `owner` ou `admin`; apenas um `owner` remove outro `owner`. Retorna **422** ao remover o último `owner`.')]
    public function delete(OrganizationMemberDeleteRequest $request): JsonResponse
    {
        try {
            $this->authorize('removeMember', [Organization::class, $request->validated('id'), $request->validated('user_id')]);

            $this->memberService->delete($request->validated());

            return ReturnApi::success(null, 'Membro removido com sucesso.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Organização ou membro não encontrado.', null, 404);
        } catch (AuthorizationException $e) {
            /**
             * @status 403
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), null, $e->status() ?? 403);
        } catch (ApiException $e) {
            /**
             * @status 422
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }
}
