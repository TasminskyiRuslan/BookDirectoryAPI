<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserSeeder extends Seeder
{
    /**
     * Seed the super admin user.
     */
    public function run(): void
    {
        $name = config('super-admin.name');
        $email = config('super-admin.email');
        $password = config('super-admin.password');
        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );
        $admin->assignRole(UserRole::SUPER_ADMIN->value);
        $this->command->info("Super admin user '{$admin->email}' created/updated successfully.");
    }
}
