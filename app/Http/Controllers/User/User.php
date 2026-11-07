<?php

namespace App\Http\Controllers\User;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserShowRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\User\UserService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class User extends Controller
{
    public function __construct(public UserService $userService) {}

    #[Endpoint(operationId: 'usersIndex', title: 'Listar usuários', description: '**operationId:** `usersIndex` — Lista os usuários paginados e ordenados por `name`, com a relação `roles` carregada. Aceita `search` (filtra por `name` **ou** `email` com `like`), `per_page` e `page`. Em **200**, `data` segue o schema **User Collection** (`App\\Http\\Resources\\User\\UserCollection`): `data.data` é a lista de itens no schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`) e `data.pagination` traz `total`, `count`, `per_page`, `current_page` e `total_pages`. Requer a permissão `user.view`.')]
    public function index(UserIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->index($request->validated());

            return ReturnApi::success($data, 'Usuários listados com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao listar usuários: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'usersShow', title: 'Exibir usuário', description: '**operationId:** `usersShow` — Exibe um usuário pelo `id` informado no path, com a relação `roles` carregada. O `id` é validado como `uuid` em `UserShowRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`). Retorna **404** quando o usuário não existe. Requer a permissão `user.view`.')]
    public function show(UserShowRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->show($request->validated());

            return ReturnApi::success($data, 'Usuário encontrado.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Usuário não encontrado.', null, 404);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao exibir usuário: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'usersStore', title: 'Cadastrar usuário', description: '**operationId:** `usersStore` — Cria um novo usuário com `name`, `email` (único na tabela `users`) e `password`, e atribui o cargo informado em `role_id` — um **UUID** de `roles` existente no guard `api`. Tudo roda dentro de uma transação. Em **201**, `data` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`), com `roles` carregado. Requer a permissão `user.create`.')]
    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->store($request->validated());

            return ReturnApi::success($data, 'Usuário cadastrado com sucesso.', 201);
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao cadastrar usuário: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'usersUpdate', title: 'Atualizar usuário', description: '**operationId:** `usersUpdate` — Atualiza `name` e `email` do usuário identificado pelo `id` do path. O `id` é validado como `uuid` em `UserUpdateRequest`, que o injeta a partir do parâmetro de rota — não há route model binding; ele também é usado para ignorar o próprio registro na regra de unicidade do `email`. `password` é opcional e só é gravado quando enviado e não vazio. Quando a chave `roles` está presente, os cargos são **sincronizados** pelos **nomes** dentro do guard `api` (enviar lista vazia remove todos); omitir a chave mantém os cargos atuais. Tudo roda dentro de uma transação. Em **200**, `data` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`). Retorna **404** quando o usuário não existe. Requer a permissão `user.update`.')]
    public function update(UserUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->update($request->validated());

            return ReturnApi::success($data, 'Usuário atualizado com sucesso.');
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Usuário não encontrado.', null, 404);
        } catch (ApiException $e) {
            /**
             * @status 400
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao atualizar usuário: '.$e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'usersDelete', title: 'Excluir usuário', description: '**operationId:** `usersDelete` — Exclui o usuário identificado pelo `id` do path, dentro de uma transação. O `id` é validado como `uuid` em `UserDeleteRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` é `null`. Retorna **404** quando o usuário não existe. Requer a permissão `user.delete`.')]
    public function delete(UserDeleteRequest $request): JsonResponse
    {
        try {
            $this->userService->delete($request->validated());

            return ReturnApi::success(null, 'Usuário excluído com sucesso.', 200);
        } catch (ModelNotFoundException $e) {
            /**
             * @status 404
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Usuário não encontrado.', null, 404);
        } catch (ApiException $e) {
            /**
             * @status 400
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao excluir usuário: '.$e->getMessage(), $e->data, $e->getCode());
        }
    }
}
