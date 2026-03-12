<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the user model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            if ($user->roles->isEmpty()) {
                $user->assignRole(UserRole::VIEWER->value);
            }
        });
    }

    /**
     * Indicate that the user has the admin role.
     *
     * @return $this
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles(UserRole::ADMIN->value);
        });
    }

    /**
     * Indicate that the user has the editor role.
     *
     * @return $this
     */
    public function editor(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles(UserRole::EDITOR->value);
        });
    }

    /**
     * Indicate that the user has the viewer role.
     *
     * @return $this
     */
    public function viewer(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles(UserRole::VIEWER->value);
        });
    }
}
