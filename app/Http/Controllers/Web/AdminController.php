<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Project;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private GitHubService $githubService
    ) {}

    /**
     * Show admin login form.
     */
    public function showLogin(): View
    {
        return view('admin.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $expectedToken = config('services.admin.api_token');

        if (hash_equals($expectedToken, $validated['token'])) {
            $request->session()->put('admin_token', $validated['token']);
            
            return redirect()->route('admin.index')->with('success', 'Logged in successfully');
        }

        return back()->with('error', 'Invalid admin token');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('admin_token');
        
        return redirect()->route('voting.index')->with('success', 'Logged out successfully');
    }

    /**
     * Show admin dashboard.
     */
    public function index(): View
    {
        $projects = Project::withCount('features')->get();
        
        return view('admin.index', compact('projects'));
    }

    /**
     * Show create project form.
     */
    public function createProject(): View
    {
        return view('admin.projects.create');
    }

    /**
     * Store a new project.
     */
    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191|unique:projects,slug',
            'description' => 'nullable|string|max:5000',
            'github_owner' => 'required|string|max:191',
            'github_repo' => 'required|string|max:191',
            'github_token' => 'nullable|string|max:191',
            'auto_sync' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $project = Project::create($validated);

        // Auto-sync issues after creation
        if ($project->github_repo && $project->github_owner) {
            $this->githubService->syncIssues($project);
        }

        return redirect()->route('admin.projects.edit', $project)
            ->with('success', 'Project created and GitHub issues synced');
    }

    /**
     * Show edit project form.
     */
    public function editProject(Project $project): View
    {
        $project->load('features');
        
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Update a project.
     */
    public function updateProject(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string|max:5000',
            'github_owner' => 'nullable|string|max:191',
            'github_repo' => 'nullable|string|max:191',
            'github_token' => 'nullable|string|max:191',
            'auto_sync' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $project->update($validated);

        return back()->with('success', 'Project updated successfully');
    }

    /**
     * Sync GitHub issues for a project.
     */
    public function syncGithub(Project $project)
    {
        $result = $this->githubService->syncIssues($project);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Test GitHub connection.
     */
    public function testGithub(Request $request)
    {
        $validated = $request->validate([
            'owner' => 'required|string',
            'repo' => 'required|string',
            'token' => 'nullable|string',
        ]);

        $result = $this->githubService->testConnection(
            $validated['owner'],
            $validated['repo'],
            $validated['token'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Show feature management.
     */
    public function features(Project $project): View
    {
        $features = $project->features()
            ->orderBy('vote_count', 'desc')
            ->paginate(50);

        return view('admin.features', compact('project', 'features'));
    }

    /**
     * Update feature status.
     */
    public function updateFeature(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,accepted,planned,in_progress,done,rejected',
        ]);

        $feature->update($validated);

        return back()->with('success', 'Feature status updated');
    }

    /**
     * Delete a feature.
     */
    public function deleteFeature(Feature $feature)
    {
        $feature->delete();

        return back()->with('success', 'Feature deleted');
    }
}
