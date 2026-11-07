<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permissões sincronizadas com a role admin
    |--------------------------------------------------------------------------
    |
    | Lista de permissões que o comando `php artisan permission:sync` cria
    | (se não existirem) e atribui à role admin. Ao criar novos módulos,
    | adicione aqui as permissões. Chave = nome (recurso.ação), valor = label (tradução).
    |
    */

    'permissions' => [
        'user.view' => 'Visualizar usuários',
        'user.update' => 'Editar usuários',
        'user.create' => 'Criar usuários',
        'user.delete' => 'Excluir usuários',
        'roles.view' => 'Visualizar roles',
        'roles.create' => 'Criar roles',
        'roles.update' => 'Editar roles',
        'roles.delete' => 'Excluir roles',
        'permissions.view' => 'Visualizar permissões',
    ],

    /*
    |--------------------------------------------------------------------------
    | Labels dos módulos (para listagem agrupada)
    |--------------------------------------------------------------------------
    | Mapeamento module => label. O module é extraído do nome da permissão
    | (parte antes do ponto, ex.: user.view → user). Fallback: ucfirst(module).
    */

    'module_labels' => [
        'user' => 'Usuários',
        'users' => 'Usuários',
        'roles' => 'Cargos',
        'administrators' => 'Administradores',
        'permissions' => 'Permissões',
    ],

];
