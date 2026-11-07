<?php

namespace App\Http\Controllers\Auth;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SocialAuth\CallbackRequest;
use App\Http\Requests\SocialAuth\RedirectRequest;
use App\Services\Auth\SocialAuthService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;

class SocialAuth extends Controller
{
    public function __construct(protected SocialAuthService $socialAuthService){}

    #[Endpoint(operationId: 'socialAuthRedirect', title: 'Gerar URL de login social', description: '**operationId:** `socialAuthRedirect` — Monta a URL de autorização do provedor social informado no path e a devolve para o client, que faz o redirecionamento. O `provider` é validado em `RedirectRequest`, que o injeta a partir do parâmetro de rota em `prepareForValidation()`, e precisa ser um valor do enum `App\\Enums\\SocialLoginProviders` — hoje `google`, `microsoft` ou `github`. O driver do Socialite é usado em modo `stateless()`, ou seja, sem sessão: o fluxo serve a clients de API. Em **200**, `data` traz apenas `url`, a URL de autorização do provedor. Não requer autenticação.')]
    public function redirect(RedirectRequest $request): JsonResponse
    {
        try {
            $data = $this->socialAuthService->redirect($request->validated());

            return ReturnApi::success($data, 'URL de redirecionamento gerada com sucesso');
        } catch (ApiException $e) {
            /**
             * @status 401
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(operationId: 'socialAuthCallback', title: 'Concluir login social', description: '**operationId:** `socialAuthCallback` — Recebe o retorno do provedor social, busca o usuário no provedor (Socialite em modo `stateless()`) e emite um token JWT. O usuário é resolvido em três etapas: primeiro por `provider` + `provider_id`; se não houver, por `email` — e nesse caso o registro é vinculado ao provedor, atualizando `provider`, `provider_id` e `avatar`; se ainda não houver, um novo usuário é criado com `password` nulo e `email_verified_at` preenchido, já que o e-mail vem verificado pelo provedor. Em **200**, `data.user` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`) e a resposta também traz `data.token` e `data.refresh_expires_in` (segundos até a expiração do refresh). Retorna **422** quando o provedor não devolve um e-mail. Não requer autenticação.')]
    public function callback(CallbackRequest $request): JsonResponse
    {
        try {
            $data = $this->socialAuthService->callback($request->validated());

            return ReturnApi::success($data, 'Usuário autenticado com sucesso');
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
