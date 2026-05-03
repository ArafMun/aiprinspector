<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Repository;

class RepositorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $repositories = [
            [
                'name' => 'ai-reviewer',
                'full_name' => 'username/ai-reviewer',
                'url' => 'https://github.com/username/ai-reviewer',
                'description' => 'AI-powered code review system',
                'default_branch' => 'main',
                'is_active' => true,
            ],
            [
                'name' => 'example-project',
                'full_name' => 'username/example-project',
                'url' => 'https://github.com/username/example-project',
                'description' => 'Example project for testing',
                'default_branch' => 'main',
                'is_active' => true,
            ],
            [
                'name' => 'test-repo',
                'full_name' => 'username/test-repo',
                'url' => 'https://github.com/username/test-repo',
                'description' => 'Test repository',
                'default_branch' => 'develop',
                'is_active' => false,
            ],
        ];

        foreach ($repositories as $repo) {
            Repository::firstOrCreate(
                ['full_name' => $repo['full_name']],
                $repo
            );
        }
    }
}
