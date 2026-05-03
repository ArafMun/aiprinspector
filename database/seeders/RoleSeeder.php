<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Full administrative access',
                'permissions' => [
                    'admin.dashboard',
                    'admin.reviews',
                    'admin.logs',
                    'admin.users',
                    'admin.settings',
                    'webhook.process',
                    'ai.configure',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'developer',
                'description' => 'Developer access with limited admin features',
                'permissions' => [
                    'admin.dashboard',
                    'admin.reviews',
                    'webhook.process',
                    'ai.configure',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'description' => 'Read-only access to dashboard and reviews',
                'permissions' => [
                    'admin.dashboard',
                    'admin.reviews',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'description' => 'Basic user access',
                'permissions' => [],
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
