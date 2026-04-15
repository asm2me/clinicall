<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = config('rbac.roles', []);

        foreach ($roles as $slug => $definition) {
            Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['label'] ?? $slug,
                    'scope' => $definition['scope'] ?? 'tenant',
                    'permissions' => $definition['permissions'] ?? [],
                ],
            );
        }
    }
}