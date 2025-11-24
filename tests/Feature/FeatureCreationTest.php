<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_feature_with_valid_data(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'description' => 'A test project',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/projects/{$project->slug}/features", [
            'title' => 'Test Feature Title',
            'description' => 'This is a test feature description.',
            'client_id' => 'test-client-12345',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'slug',
                'description',
                'status',
                'vote_count',
            ],
        ]);

        $this->assertDatabaseHas('features', [
            'title' => 'Test Feature Title',
            'project_id' => $project->id,
            'status' => 'submitted',
        ]);

        // Check that initial vote was created
        $this->assertDatabaseHas('votes', [
            'client_id' => 'test-client-12345',
        ]);
    }

    public function test_cannot_create_feature_with_invalid_title(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/projects/{$project->slug}/features", [
            'title' => 'abc', // Too short
            'description' => 'Valid description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_feature_slug_is_generated_automatically(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/projects/{$project->slug}/features", [
            'title' => 'My Awesome Feature',
            'description' => 'Description here',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.slug', 'my-awesome-feature');
    }
}
