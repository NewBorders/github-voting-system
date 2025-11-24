<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_endpoint_requires_token(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'title' => 'Test Feature',
            'status' => 'submitted',
        ]);

        $response = $this->patchJson("/api/v1/admin/features/{$feature->id}", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    public function test_admin_endpoint_rejects_invalid_token(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'title' => 'Test Feature',
            'status' => 'submitted',
        ]);

        $response = $this->withHeader('X-Admin-Token', 'wrong-token')
            ->patchJson("/api/v1/admin/features/{$feature->id}", [
                'status' => 'accepted',
            ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_update_feature_with_valid_token(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'title' => 'Test Feature',
            'status' => 'submitted',
        ]);

        $response = $this->withHeader('X-Admin-Token', 'test-admin-token')
            ->patchJson("/api/v1/admin/features/{$feature->id}", [
                'status' => 'accepted',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'status' => 'accepted',
        ]);
    }

    public function test_admin_can_delete_feature(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'title' => 'Test Feature to Delete',
            'status' => 'submitted',
        ]);

        $response = $this->withHeader('X-Admin-Token', 'test-admin-token')
            ->deleteJson("/api/v1/admin/features/{$feature->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Feature deleted successfully',
        ]);

        $this->assertDatabaseMissing('features', [
            'id' => $feature->id,
        ]);
    }

    public function test_admin_can_create_project(): void
    {
        $response = $this->withHeader('X-Admin-Token', 'test-admin-token')
            ->postJson('/api/v1/admin/projects', [
                'name' => 'New Admin Project',
                'slug' => 'new-admin-project',
                'description' => 'Created by admin',
                'is_active' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'New Admin Project');

        $this->assertDatabaseHas('projects', [
            'slug' => 'new-admin-project',
        ]);
    }

    public function test_admin_can_view_stats(): void
    {
        Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-Admin-Token', 'test-admin-token')
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'projects' => ['total', 'active'],
            'features' => ['total', 'by_status'],
            'votes' => ['total'],
            'top_features',
        ]);
    }
}
