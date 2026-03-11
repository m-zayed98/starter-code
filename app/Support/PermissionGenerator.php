<?php

namespace App\Support;

use App\Models\Permission;
use Illuminate\Support\Str;

class PermissionGenerator
{
    /**
     * Generate a flat list of permissions from entities and actions.
     *
     * @param  array<int,string>  $entities  List of entity names, e.g. ['User', 'Role'].
     * @param  array<int,string>  $actions   List of actions, e.g. ['view', 'create', 'update', 'delete'].
     * @param  string|null        $guard     Guard name; defaults to the application's default auth guard.
     * @return array<int,array{name:string,guard_name:string}>
     */
    public static function generate(array $entities, array $actions, ?string $guard = null): void
    {
        $separator = config('permission.separator', '.');
        $guardName = $guard ?? config('auth.defaults.guard', 'api');

        $permissions = [];

        foreach ($entities as $entity) {
            $entitySlug = Str::lower($entity);

            foreach ($actions as $action) {
                $permissions[] = [
                    'name'       => $entitySlug . $separator . $action,
                    'guard_name' => $guardName,
                ];
            }
        }

        foreach ($permissions as $data) {
            Permission::firstOrCreate($data);
        }
    }
}
