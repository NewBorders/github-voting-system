<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_vote_for_feature(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'is_active' => true,
        ]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'title' => 'Test Feature',
            'description' => 'Test description',
            'status' => 'submitted',
        ]);

        $response = $this->postJson("/api/v1/features/{$feature->id}/vote", [
            'client_id' => 'unique-client-id-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'feature_id' => $feature->id,
            'vote_count' => 1,
            'voted' => true,
        ]);

        $this->assertDatabaseHas('votes', [
            'feature_id' => $feature->id,
            'client_id' => 'unique-client-id-123',
        ]);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'vote_count' => 1,
        ]);
    }

    public function test_cannot_vote_twice_with_same_client_id(): void
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

        // First vote
        $this->postJson("/api/v1/features/{$feature->id}/vote", [
            'client_id' => 'unique-client-id-456',
        ]);

        // Second vote with same client_id
        $response = $this->postJson("/api/v1/features/{$feature->id}/vote", [
            'client_id' => 'unique-client-id-456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'feature_id' => $feature->id,
            'vote_count' => 1, // Should still be 1
            'voted' => true,
        ]);

        // Should only have one vote record
        $this->assertEquals(1, $feature->votes()->count());
    }

    public function test_can_remove_vote(): void
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
            'vote_count' => 1,
        ]);

        $feature->votes()->create([
            'client_id' => 'client-to-remove-789',
        ]);

        $response = $this->deleteJson("/api/v1/features/{$feature->id}/vote", [
            'client_id' => 'client-to-remove-789',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'feature_id' => $feature->id,
            'vote_count' => 0,
            'voted' => false,
        ]);

        $this->assertDatabaseMissing('votes', [
            'feature_id' => $feature->id,
            'client_id' => 'client-to-remove-789',
        ]);
    }

    public function test_removing_nonexistent_vote_returns_success(): void
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
            'vote_count' => 0,
        ]);

        $response = $this->deleteJson("/api/v1/features/{$feature->id}/vote", [
            'client_id' => 'nonexistent-client',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'feature_id' => $feature->id,
            'vote_count' => 0,
            'voted' => false,
        ]);
    }
}
