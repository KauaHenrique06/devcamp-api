<?php

namespace App\Http\Controllers\Permission;

use App\Builder\ReturnApi;
use App\Http\Controllers\Controller;
use App\Services\Permission\PermissionService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;

class Permission extends Controller
{
    public function __construct(public PermissionService $permissionService) {}

    #[Endpoint(operationId: 'permissionsIndex', title: 'Listar permissões', description: '**operationId:** `permissionsIndex` — Lista todas as permissões do guard `api` agrupadas por módulo (o prefixo do nome antes do `.`, por exemplo `roles` em `roles.view`). Não é paginado. Cada grupo traz `module`, `module_label` (rótulo legível vindo de `config/permissions_sync.php` em `module_labels`, com fallback para o próprio módulo) e `permissions`, cujos itens seguem o schema **Permission Resource** (`App\\Http\\Resources\\Permission\\PermissionResource`). Em **200**, `data.data` é a lista de grupos. Requer a permissão `permissions.view`.')]
    public function index(): JsonResponse
    {
        try {
            $data = $this->permissionService->indexGroupedByModule();

            return ReturnApi::success($data, 'Permissões listadas com sucesso.');
        } catch (\Exception $e) {
            /**
             * @status 500
             *
             * @body array{error: true, message: string, data: mixed}
             */
            return ReturnApi::error('Erro ao listar permissões: '.$e->getMessage(), null, 500);
        }
    }
}
