<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria as permissões definidas no config (se não existirem) e atribui todas à role admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $guard = 'api';
        $permissionsConfig = config('permissions_sync.permissions', []);

        if (empty($permissionsConfig)) {
            $this->warn('Nenhuma permissão definida em config/permissions_sync.php.');

            return self::SUCCESS;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $created = 0;
        $permissions = [];

        foreach ($permissionsConfig as $name => $label) {
            $permission = Permission::findOrCreate($name, $guard);
            if ($permission->wasRecentlyCreated) {
                $created++;
            }
            $permission->label = is_string($label) ? $label : $name;
            $permission->saveQuietly();
            $permissions[] = $permission;
        }

        try {
            $adminRole = Role::findByName('admin', $guard);
        } catch (RoleDoesNotExist) {
            $this->error("Role 'admin' não encontrada para o guard '{$guard}'. Rode o seeder ou crie a role primeiro.");

            return self::FAILURE;
        }

        $adminRole->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $existing = count($permissions) - $created;
        $this->info("Permissões: {$created} criada(s), {$existing} já existente(s).");
        $this->info('Todas as permissões foram atribuídas à role admin.');

        return self::SUCCESS;
    }
}
