<?php

namespace Database\Seeders;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the permission table.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (UserPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        $viewer = Role::findOrCreate(UserRole::VIEWER->value);
        $editor = Role::findOrCreate(UserRole::EDITOR->value);
        $admin = Role::findOrCreate(UserRole::ADMIN->value);

        $viewer->syncPermissions([
            UserPermission::AUTHOR_INDEX->value,
            UserPermission::AUTHOR_SHOW->value,
            UserPermission::BOOK_INDEX->value,
            UserPermission::BOOK_SHOW->value,
        ]);
        $editor->syncPermissions([
            UserPermission::AUTHOR_INDEX->value,
            UserPermission::AUTHOR_STORE->value,
            UserPermission::AUTHOR_SHOW->value,
            UserPermission::AUTHOR_UPDATE->value,

            UserPermission::BOOK_INDEX->value,
            UserPermission::BOOK_STORE->value,
            UserPermission::BOOK_SHOW->value,
            UserPermission::BOOK_UPDATE->value,
        ]);
        $admin->syncPermissions(Permission::all());
    }
}
