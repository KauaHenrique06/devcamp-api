<?php

namespace App\Http\Controllers\Role;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleAddPermissionsRequest;
use App\Http\Requests\Role\RoleDeleteRequest;
use App\Http\Requests\Role\RoleIndexRequest;
use App\Http\Requests\Role\RoleShowRequest;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Services\Role\RoleService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class Role extends Controller
{
    public function __construct(public RoleService $roleService) {}

    #[Endpoint(operationId: 'rolesIndex', title: 'Listar cargos', description: '**operationId:** `rolesIndex` — Lista os cargos paginados e ordenados por `name`, com as permissões de cada um já carregadas. Aceita `search` (filtra por `name` com `like`), `per_page` e `page`. Em **200**, `data` segue o schema **Role Collection** (`App\\Http\\Resources\\Role\\RoleCollection`): `data.data` é a lista de itens no schema **Role Resource** (`App\\Http\\Resources\\Role\\RoleResource`) e `data.pagination` traz `total`, `count`, `per_page`, `current_page` e `total_pages`. Requer a permissão `roles.view`.')]
    public function index(RoleIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->index($request->validated());

            return ReturnApi::success($data, 'Cargos listados com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao listar cargos: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'rolesShow', title: 'Exibir cargo', description: '**operationId:** `rolesShow` — Exibe um cargo pelo `id` informado no path, com a relação `permissions` carregada. O `id` é validado como `uuid` em `RoleShowRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` segue o schema **Role Resource** (`App\\Http\\Resources\\Role\\RoleResource`), cujo campo `permissions` traz itens no schema **Permission Resource**. Retorna **404** quando o cargo não existe. Requer a permissão `roles.view`.')]
    public function show(RoleShowRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->show($request->validated());

            return ReturnApi::success($data, 'Role encontrada.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Cargo não encontrado.', null, 404);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao exibir role: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'rolesStore', title: 'Cadastrar cargo', description: '**operationId:** `rolesStore` — Cria um cargo com o `name` informado e o `guard_name` enviado (padrão `api`, aplicado em `prepareForValidation()`). Quando `permissions` é enviado, sincroniza a lista pelos **nomes** das permissões, que devem existir no mesmo guard. Tudo roda dentro de uma transação. Em **201**, `data` segue o schema **Role Resource** (`App\\Http\\Resources\\Role\\RoleResource`) com `permissions` já carregado. Requer a permissão `roles.create`.')]
    public function store(RoleStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->store($request->validated());

            return ReturnApi::success($data, 'Cargo cadastrado com sucesso.', 201);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao cadastrar cargo: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'rolesUpdate', title: 'Atualizar cargo', description: '**operationId:** `rolesUpdate` — Atualiza `name` e `guard_name` do cargo identificado pelo `id` do path. O `id` é validado como `uuid` em `RoleUpdateRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Quando a chave `permissions` está presente, as permissões são **sincronizadas** pelos nomes (enviar lista vazia remove todas); omitir a chave mantém as permissões atuais. Tudo roda dentro de uma transação. Em **200**, `data` segue o schema **Role Resource** (`App\\Http\\Resources\\Role\\RoleResource`). Retorna **404** quando o cargo não existe. Requer a permissão `roles.update`.')]
    public function update(RoleUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->update($request->validated());

            return ReturnApi::success($data, 'Role atualizada com sucesso.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Cargo não encontrado.', null, 404);
        } catch (ApiException $e) {
            /**
             * @status 400
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao atualizar cargo: '.$e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'rolesAddPermissions', title: 'Definir permissões do cargo', description: '**operationId:** `rolesAddPermissions` — Sincroniza as permissões do cargo identificado pelo `id` do path. O `id` é validado como `uuid` em `RoleAddPermissionsRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. O campo `permissions` é obrigatório, precisa ter ao menos um item e recebe **UUIDs** de permissões existentes (diferente de `rolesStore`/`rolesUpdate`, que usam nomes). A operação é um `syncPermissions`: a lista enviada substitui as permissões atuais. Em **200**, `data` segue o schema **Role Resource** (`App\\Http\\Resources\\Role\\RoleResource`). Retorna **404** quando o cargo não existe. Requer a permissão `roles.update`.')]
    public function addPermissions(RoleAddPermissionsRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->addPermissions($request->validated());

            return ReturnApi::success($data, 'Permissões adicionadas ao cargo com sucesso.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Cargo não encontrado.', null, 404);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao adicionar permissões: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'rolesDelete', title: 'Excluir cargo', description: '**operationId:** `rolesDelete` — Exclui o cargo identificado pelo `id` do path, dentro de uma transação. O `id` é validado como `uuid` em `RoleDeleteRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` é `null`. Retorna **404** quando o cargo não existe. Requer a permissão `roles.delete`.')]
    public function delete(RoleDeleteRequest $request): JsonResponse
    {
        try {
            $this->roleService->delete($request->validated());

            return ReturnApi::success(null, 'Role excluída com sucesso.', 200);
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Cargo não encontrado.', null, 404);
        } catch (ApiException $e) {
            /**
             * @status 400
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao excluir cargo: '.$e->getMessage(), $e->data, $e->getCode());
        }
    }
}
