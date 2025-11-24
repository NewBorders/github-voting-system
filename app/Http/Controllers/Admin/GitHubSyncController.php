<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\GitHubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GitHubSyncController extends Controller
{
    public function __construct(
        protected GitHubService $githubService
    ) {}

    /**
     * Sync GitHub issues for a project.
     */
    public function sync(Request $request, Project $project): JsonResponse
    {
        $result = $this->githubService->syncIssues($project);

        return response()->json($result);
    }

    /**
     * Test GitHub connection for a project.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'github_owner' => 'required|string',
            'github_repo' => 'required|string',
        ]);

        try {
            $url = "https://api.github.com/repos/{$validated['github_owner']}/{$validated['github_repo']}";
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Feature-Voting-Backend',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'repo_name' => $data['full_name'] ?? null,
                    'open_issues' => $data['open_issues_count'] ?? 0,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Repository not found or not accessible',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
