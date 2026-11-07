<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'guard_name' => 'api',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ])->assignRole($adminRole);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
        ])->assignRole($userRole);
    }
}
