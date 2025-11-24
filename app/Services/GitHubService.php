<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Feature;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    /**
     * Sync GitHub issues for a project.
     */
    public function syncIssues(Project $project): array
    {
        if (!$project->github_sync_enabled || !$project->github_owner || !$project->github_repo) {
            return ['success' => false, 'message' => 'GitHub sync not configured'];
        }

        try {
            $issues = $this->fetchOpenIssues($project->github_owner, $project->github_repo);
            
            $synced = 0;
            $created = 0;
            $updated = 0;

            foreach ($issues as $issue) {
                // Skip pull requests
                if (isset($issue['pull_request'])) {
                    continue;
                }

                $feature = Feature::where('project_id', $project->id)
                    ->where('github_issue_number', $issue['number'])
                    ->first();

                if ($feature) {
                    // Update existing feature
                    $feature->update([
                        'title' => $issue['title'],
                        'description' => $this->formatIssueDescription($issue),
                        'github_issue_url' => $issue['html_url'],
                        'github_synced_at' => now(),
                    ]);
                    $updated++;
                } else {
                    // Create new feature from issue
                    $feature = Feature::create([
                        'project_id' => $project->id,
                        'title' => $issue['title'],
                        'description' => $this->formatIssueDescription($issue),
                        'status' => $this->mapIssueState($issue),
                        'github_issue_number' => $issue['number'],
                        'github_issue_url' => $issue['html_url'],
                        'github_synced_at' => now(),
                    ]);
                    $created++;
                }

                $synced++;
            }

            // Update last sync time
            $project->update(['github_last_sync' => now()]);

            return [
                'success' => true,
                'synced' => $synced,
                'created' => $created,
                'updated' => $updated,
            ];

        } catch (\Exception $e) {
            Log::error('GitHub sync failed for project ' . $project->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch open issues from GitHub.
     */
    protected function fetchOpenIssues(string $owner, string $repo): array
    {
        $url = "https://api.github.com/repos/{$owner}/{$repo}/issues";
        
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Feature-Voting-Backend',
        ])->get($url, [
            'state' => 'open',
            'per_page' => 100,
        ]);

        if (!$response->successful()) {
            throw new \Exception('GitHub API request failed: ' . $response->status());
        }

        return $response->json();
    }

    /**
     * Format issue description for display.
     */
    protected function formatIssueDescription(array $issue): ?string
    {
        $description = $issue['body'] ?? '';
        
        // Truncate if too long
        if (strlen($description) > 5000) {
            $description = substr($description, 0, 4997) . '...';
        }

        // Add issue metadata
        $metadata = "\n\n---\n";
        $metadata .= "**GitHub Issue #" . $issue['number'] . "**\n";
        
        if (!empty($issue['labels'])) {
            $labels = array_map(fn($label) => $label['name'], $issue['labels']);
            $metadata .= "Labels: " . implode(', ', $labels) . "\n";
        }

        if (!empty($issue['user']['login'])) {
            $metadata .= "Created by: @" . $issue['user']['login'] . "\n";
        }

        return $description . $metadata;
    }

    /**
     * Map GitHub issue state to feature status.
     */
    protected function mapIssueState(array $issue): string
    {
        // Check labels for status hints
        $labels = array_map(fn($label) => strtolower($label['name']), $issue['labels'] ?? []);

        if (in_array('in progress', $labels) || in_array('in-progress', $labels)) {
            return 'in_progress';
        }

        if (in_array('planned', $labels)) {
            return 'planned';
        }

        if (in_array('accepted', $labels) || in_array('approved', $labels)) {
            return 'accepted';
        }

        // Default to submitted for new issues
        return 'submitted';
    }
}
