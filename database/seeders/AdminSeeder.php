<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var \App\Models\Role|null $superAdminRole */
        $superAdminRole = Role::query()
            ->where('guard_name', 'admin')
            ->where('name->en', 'Super Admin')
            ->first();

        $admin = Admin::query()->firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'phone' => '+966500500500',
                'avatar' => null,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        if ($superAdminRole) {
            $admin->syncRoles([$superAdminRole->id]);
        }
    }
}
