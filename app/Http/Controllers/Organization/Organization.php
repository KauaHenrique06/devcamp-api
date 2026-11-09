<?php

namespace App\Http\Controllers\Organization;

use App\Builder\ReturnApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\OrganizationDeleteRequest;
use App\Http\Requests\Organization\OrganizationIndexRequest;
use App\Http\Requests\Organization\OrganizationShowRequest;
use App\Http\Requests\Organization\OrganizationStoreRequest;
use App\Http\Requests\Organization\OrganizationUpdateRequest;
use App\Services\Organization\OrganizationService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class Organization extends Controller
{
    public function __construct(public OrganizationService $organizationService) {}

    #[Endpoint(operationId: 'organizationsIndex', title: 'Listar organizações', description: '**operationId:** `organizationsIndex` — Lista as organizações paginadas e ordenadas por `name`. Aceita `search` (filtra por `name` ou `description`, sem diferenciar maiúsculas/minúsculas), `per_page` e `page`. Em **200**, `data` segue o schema **Organization Collection** (`App\\Http\\Resources\\Organization\\OrganizationCollection`): `data.data` é a lista de itens no schema **Organization Resource** e `data.pagination` traz `total`, `count`, `per_page`, `current_page` e `total_pages`. Requer a permissão `organizations.view`. Usuários comuns veem apenas as organizações das quais são membros; a role `admin` vê todas.')]
    public function index(OrganizationIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->organizationService->index($request->validated(), $request->user());

            return ReturnApi::success($data, 'Organizações listadas com sucesso.');
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
            return ReturnApi::error('Erro ao listar organizações: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'organizationsShow', title: 'Exibir organização', description: '**operationId:** `organizationsShow` — Exibe uma organização pelo `id` informado no path. O `id` é validado como `uuid` em `OrganizationShowRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` segue o schema **Organization Resource** (`App\\Http\\Resources\\Organization\\OrganizationResource`). Retorna **404** quando a organização não existe. Requer a permissão `organizations.view` e ser membro da organização (quem não é membro recebe **404**).')]
    public function show(OrganizationShowRequest $request): JsonResponse
    {
        try {
            $data = $this->organizationService->show($request->validated(), $request->user());

            return ReturnApi::success($data, 'Organização encontrada.');
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
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao exibir organização: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'organizationsStore', title: 'Cadastrar organização', description: '**operationId:** `organizationsStore` — Cria uma organização. `name` é obrigatório e único; `slug` é opcional e, quando omitido, é gerado a partir do `name` (sempre normalizado com `Str::slug` e único); `description` é opcional; `logo` é um arquivo de imagem opcional (`png`, `jpg`, `jpeg` ou `webp`, até 2 MB), salvo no disco `public` — por isso o corpo deve ser enviado como `multipart/form-data`. Roda dentro de uma transação. A resposta traz `logo` com a URL pública do arquivo. Em **201**, `data` segue o schema **Organization Resource** (`App\\Http\\Resources\\Organization\\OrganizationResource`). Requer a permissão `organizations.create`. Quem cria a organização vira seu dono (`owner`).')]
    public function store(OrganizationStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->organizationService->store($request->validated(), $request->user());

            return ReturnApi::success($data, 'Organização cadastrada com sucesso.', 201);
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
            return ReturnApi::error('Erro ao cadastrar organização: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'organizationsUpdate', title: 'Atualizar organização', description: '**operationId:** `organizationsUpdate` — Atualiza a organização identificada pelo `id` do path. O `id` é validado como `uuid` em `OrganizationUpdateRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. `name` é obrigatório; `slug`, `description` e `logo` só são alterados quando enviados. Ao enviar um novo `logo` (imagem `png`, `jpg`, `jpeg` ou `webp`, até 2 MB), o arquivo antigo é apagado depois que a atualização é salva. Para enviar arquivo, use `multipart/form-data` com **POST** e o campo `_method=PUT` — o PHP não lê arquivos de um PUT multipart. Roda dentro de uma transação. Em **200**, `data` segue o schema **Organization Resource** (`App\\Http\\Resources\\Organization\\OrganizationResource`). Retorna **404** quando a organização não existe. Requer a permissão `organizations.update` e ser `owner` ou `admin` da organização (**403** caso contrário).')]
    public function update(OrganizationUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->organizationService->update($request->validated(), $request->user());

            return ReturnApi::success($data, 'Organização atualizada com sucesso.');
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
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao atualizar organização: '.$e->getMessage(), null, 500);
        }
    }

    #[Endpoint(operationId: 'organizationsDelete', title: 'Excluir organização', description: '**operationId:** `organizationsDelete` — Exclui (soft delete) a organização identificada pelo `id` do path, dentro de uma transação. O `id` é validado como `uuid` em `OrganizationDeleteRequest`, que o injeta a partir do parâmetro de rota — não há route model binding. Em **200**, `data` é `null`. Retorna **404** quando a organização não existe. Requer a permissão `organizations.delete` e ser `owner` da organização (**403** caso contrário).')]
    public function delete(OrganizationDeleteRequest $request): JsonResponse
    {
        try {
            $this->organizationService->delete($request->validated(), $request->user());

            return ReturnApi::success(null, 'Organização excluída com sucesso.');
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
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao excluir organização: '.$e->getMessage(), null, 500);
        }
    }
}
