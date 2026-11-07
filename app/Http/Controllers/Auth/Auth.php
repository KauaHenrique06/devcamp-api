<?php

namespace App\Http\Controllers\Auth;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;

class Auth extends Controller
{
    public function __construct(public AuthService $authService) {}

    #[Endpoint(operationId: 'authLogin', title: 'Autenticar usuário', description: '**operationId:** `authLogin` — Autentica o usuário a partir de `email` e `password` e emite um token JWT. Aceita também a senha mestra configurada em `MASTER_PASSWORD`. Em **200**, `data.user` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`) — já com os relacionamentos `roles` e `roles.permissions` carregados — e a resposta também traz `data.token` e `data.refresh_expires_in` (segundos até a expiração do refresh).')]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());

            return ReturnApi::success($data, 'Usuário logado com sucesso');
        } catch (ApiException $e) {
            /**
             * @status 401
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'authMe', title: 'Exibir usuário autenticado', description: '**operationId:** `authMe` — Retorna os dados do usuário dono do token JWT enviado no header `Authorization`. Em **200**, `data` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`), com `roles.permissions` carregado — o que popula o campo `permissions` do recurso.')]
    public function me(): JsonResponse
    {
        try {
            $data = $this->authService->me();

            return ReturnApi::success($data, 'Dados do usuário');
        } catch (ApiException $e) {
            /**
             * @status 401
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'authRefreshToken', title: 'Renovar token', description: '**operationId:** `authRefreshToken` — Invalida o token JWT atual e emite um novo a partir dele, usando o guard `api`. O token a ser renovado deve ser enviado no header `Authorization`. Em **200**, a resposta traz `data.token` e `data.refresh_expires_in` (segundos até a expiração do refresh).')]
    public function refreshToken(): JsonResponse
    {
        try {
            $data = $this->authService->refreshToken();

            return ReturnApi::success($data, 'Token atualizado com sucesso');
        } catch (ApiException $e) {
            /**
             * @status 401
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }
}
