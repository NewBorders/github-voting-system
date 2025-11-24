<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VotingController extends Controller
{
    /**
     * Display voting homepage with all projects.
     */
    public function index(): View
    {
        $projects = Project::active()
            ->withCount('features')
            ->orderBy('name')
            ->get();

        return view('voting.index', compact('projects'));
    }

    /**
     * Display voting page for a specific project.
     */
    public function show(Project $project): View
    {
        if (!$project->is_active) {
            abort(404);
        }

        $features = Feature::where('project_id', $project->id)
            ->whereIn('status', ['submitted', 'accepted', 'planned', 'in_progress'])
            ->sorted('top')
            ->paginate(20);

        return view('voting.show', compact('project', 'features'));
    }

    /**
     * Submit a new feature (HTMX).
     */
    public function submitFeature(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:200',
            'description' => 'nullable|string|max:5000',
            'client_id' => 'required|string|min:5|max:100',
        ]);

        $feature = $project->features()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'submitted',
        ]);

        // Add initial vote
        $feature->addVote($validated['client_id']);

        if ($request->header('HX-Request')) {
            return view('voting.partials.feature-item', compact('feature', 'project'))
                ->with('success', 'Feature submitted successfully!');
        }

        return redirect()->route('voting.show', $project);
    }

    /**
     * Vote for a feature (HTMX).
     */
    public function vote(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'client_id' => 'required|string|min:5|max:100',
        ]);

        $feature->addVote($validated['client_id']);
        $feature->refresh();

        if ($request->header('HX-Request')) {
            return view('voting.partials.vote-button', [
                'feature' => $feature,
                'hasVoted' => true,
            ]);
        }

        return back();
    }

    /**
     * Remove vote (HTMX).
     */
    public function unvote(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'client_id' => 'required|string|min:5|max:100',
        ]);

        $feature->removeVote($validated['client_id']);
        $feature->refresh();

        if ($request->header('HX-Request')) {
            return view('voting.partials.vote-button', [
                'feature' => $feature,
                'hasVoted' => false,
            ]);
        }

        return back();
    }
}
