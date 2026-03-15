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
        foreach (UserPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $viewer = Role::findOrCreate(UserRole::VIEWER->value);
        $editor = Role::findOrCreate(UserRole::EDITOR->value);
        $admin = Role::findOrCreate(UserRole::ADMIN->value);
        $superAdmin = Role::findOrCreate(UserRole::SUPER_ADMIN->value);

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
        $admin->syncPermissions([
            UserPermission::AUTHOR_INDEX->value,
            UserPermission::AUTHOR_STORE->value,
            UserPermission::AUTHOR_SHOW->value,
            UserPermission::AUTHOR_UPDATE->value,

            UserPermission::BOOK_INDEX->value,
            UserPermission::BOOK_STORE->value,
            UserPermission::BOOK_SHOW->value,
            UserPermission::BOOK_UPDATE->value,

            UserPermission::AUTHOR_DESTROY->value,
            UserPermission::BOOK_DESTROY->value,
            UserPermission::USER_INDEX->value,
            UserPermission::USER_SHOW->value,
            UserPermission::USER_DESTROY->value,
            UserPermission::USER_ROLE_UPDATE->value,
        ]);
    }
}
