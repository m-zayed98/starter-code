<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create or retrieve the Super Admin role
        $role = Role::query()->firstOrCreate(
            ['name' => ['en' => 'Super Admin']],
            ['guard_name' => 'admin']
        );

        // Attach all permissions to Super Admin
        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->pluck('id')->all();

        if (! empty($permissions)) {
            $role->permissions()->sync($permissions);
        }
    }
}
