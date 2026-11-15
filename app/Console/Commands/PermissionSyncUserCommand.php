<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza as permissões da role user: todas as do config, exceto as de permissions_sync.user_role.except';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $guard = 'api';
        $roleName = config('permissions_sync.user_role.name', 'user');
        $except = config('permissions_sync.user_role.except', []);
        $permissionsConfig = config('permissions_sync.permissions', []);

        if (empty($permissionsConfig)) {
            $this->warn('Nenhuma permissão definida em config/permissions_sync.php.');

            return self::SUCCESS;
        }

        try {
            $role = Role::findByName($roleName, $guard);
        } catch (RoleDoesNotExist) {
            $this->error("Role '{$roleName}' não encontrada para o guard '{$guard}'. Rode o seeder ou crie a role primeiro.");

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $names = collect(array_keys($permissionsConfig))
            ->reject(fn (string $name) => Str::is($except, $name))
            ->values();

        // Garante que as permissões existem, caso o permission:sync ainda não tenha rodado.
        $permissions = $names->map(fn (string $name) => Permission::findOrCreate($name, $guard));

        // sync: a role fica exatamente com esta lista (remove o que foi excluído no config).
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Role '{$roleName}' sincronizada com {$names->count()} permissão(ões):");
        $this->line('  '.$names->implode(', '));

        if (! empty($except)) {
            $this->line('Excluídas pelos padrões: '.implode(', ', $except));
        }

        return self::SUCCESS;
    }
}
