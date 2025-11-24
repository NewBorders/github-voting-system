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
        $features = Feature::where('project_id', $project->id)
            ->whereIn('status', ['submitted', 'accepted', 'planned', 'in_progress'])
            ->sorted('top')
            ->paginate(20);

        return view('voting.show', compact('project', 'features'));
    }
}
