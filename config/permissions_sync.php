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
        'organizations.view' => 'Visualizar organizações',
        'organizations.create' => 'Criar organizações',
        'organizations.update' => 'Editar organizações',
        'organizations.delete' => 'Excluir organizações',
        'organizations.members.view' => 'Visualizar membros das organizações',
        'organizations.members.manage' => 'Gerenciar membros das organizações',
        'projects.view' => 'Visualizar projetos',
        'projects.create' => 'Criar projetos',
        'projects.update' => 'Editar projetos',
        'projects.delete' => 'Excluir projetos',
        'projects.restore' => 'Restaurar projetos',
        'projects.archive' => 'Arquivar projetos',
        'projects.force-delete' => 'Excluir projetos permanentemente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissões da role user (usuário comum)
    |--------------------------------------------------------------------------
    |
    | O comando `php artisan permission:sync-user` dá à role user todas as
    | permissões da lista acima, exceto as que casarem com estes padrões
    | (aceita curinga `*`, ex.: `roles.*`).
    |
    */

    'user_role' => [
        'name' => 'user',
        'except' => [
            'roles.*',
            'permissions.*',
        ],
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
        'organizations' => 'Organizações',
        'projects' => 'Projetos',
    ],

];
