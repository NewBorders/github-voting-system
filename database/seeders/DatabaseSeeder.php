<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Project;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample projects
        $minecraftProject = Project::create([
            'name' => 'Minecraft Hosting Helper',
            'slug' => 'minecraft-hosting-helper',
            'description' => 'A tool to help manage Minecraft servers and hosting.',
            'is_active' => true,
        ]);

        $gameToolProject = Project::create([
            'name' => 'Game Tool XYZ',
            'slug' => 'game-tool-xyz',
            'description' => 'An awesome gaming tool for various purposes.',
            'is_active' => true,
        ]);

        // Create sample features for Minecraft project
        $feature1 = Feature::create([
            'project_id' => $minecraftProject->id,
            'title' => 'Add automatic backup functionality',
            'description' => 'Automatically backup worlds at regular intervals.',
            'status' => 'accepted',
            'vote_count' => 15,
        ]);

        $feature2 = Feature::create([
            'project_id' => $minecraftProject->id,
            'title' => 'Support for multiple server versions',
            'description' => 'Allow users to switch between different Minecraft versions easily.',
            'status' => 'planned',
            'vote_count' => 23,
        ]);

        $feature3 = Feature::create([
            'project_id' => $minecraftProject->id,
            'title' => 'Plugin management interface',
            'description' => 'A web interface to install, update, and configure plugins.',
            'status' => 'submitted',
            'vote_count' => 8,
        ]);

        $feature4 = Feature::create([
            'project_id' => $minecraftProject->id,
            'title' => 'Performance monitoring dashboard',
            'description' => 'Real-time monitoring of server performance, TPS, memory usage, etc.',
            'status' => 'in_progress',
            'vote_count' => 12,
        ]);

        // Create sample features for Game Tool project
        $feature5 = Feature::create([
            'project_id' => $gameToolProject->id,
            'title' => 'Dark mode support',
            'description' => 'Add a dark theme for better nighttime usage.',
            'status' => 'submitted',
            'vote_count' => 31,
        ]);

        $feature6 = Feature::create([
            'project_id' => $gameToolProject->id,
            'title' => 'Mobile app version',
            'description' => 'Create native mobile apps for iOS and Android.',
            'status' => 'submitted',
            'vote_count' => 45,
        ]);

        $feature7 = Feature::create([
            'project_id' => $gameToolProject->id,
            'title' => 'Integration with Discord',
            'description' => 'Send notifications and updates to Discord channels.',
            'status' => 'done',
            'vote_count' => 19,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Projects created: ' . Project::count());
        $this->command->info('Features created: ' . Feature::count());
    }
}
