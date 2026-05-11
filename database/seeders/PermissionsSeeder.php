<?php

namespace Database\Seeders;

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
        $actions = ['read', 'create', 'update', 'delete'];

        // Full CRUD modules
        $fullCrudEntities = [
            'Admins',
            'Roles',
            'Users',
            'Ad_packages',
            'Blogs',
            'Notification_groups',
            'Contact_messages',
        ];

        PermissionGenerator::generate($fullCrudEntities, $actions, 'admin');

        // Ads: read + update (toggle status) only — no create/delete from admin panel
        PermissionGenerator::generate(['Ads'], ['read', 'update'], 'admin');

        // Ad Reports: read + update (reply) only
        PermissionGenerator::generate(['Ad_reports'], ['read', 'update'], 'admin');

        // Ad Reviews: read + update (toggle visibility) only
        PermissionGenerator::generate(['Ad_reviews'], ['read', 'update'], 'admin');

        // Settings: read + update only
        $settingsEntities = [
            'General_settings',
            'Contact_settings',
            'About_us_settings',
            'Terms_settings',
            'Privacy_settings',
        ];

        PermissionGenerator::generate($settingsEntities, ['read', 'update'], 'admin');
    }
}
