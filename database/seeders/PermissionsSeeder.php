<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\PermissionGenerator;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->generateAdminPermissions();
    }

    private function generateAdminPermissions(): void
    {
        $entities = ['Admins', 'Roles'];
        $actions  = ['read', 'create', 'update', 'delete'];

        PermissionGenerator::generate($entities, $actions, 'admin');
    }
}
