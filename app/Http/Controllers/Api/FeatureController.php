<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeatureController extends Controller
{
    /**
     * Display a listing of features for a specific project.
     */
    public function indexByProject(Request $request, Project $project): AnonymousResourceCollection
    {
        $query = Feature::where('project_id', $project->id);

        // Filter by status
        if ($request->has('status')) {
            $statuses = is_array($request->status) ? $request->status : [$request->status];
            $query->withStatus($statuses);
        }

        // Sort
        $sort = $request->input('sort', 'top');
        $query->sorted($sort);

        // Paginate
        $limit = min((int) $request->input('limit', 20), 100);
        $features = $query->paginate($limit);

        return FeatureResource::collection($features);
    }

    /**
     * Store a newly created feature.
     */
    public function store(StoreFeatureRequest $request, Project $project): FeatureResource
    {
        $validated = $request->validated();

        $feature = $project->features()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'submitted',
        ]);

        // Optionally create an initial vote from the submitter
        if (!empty($validated['client_id'])) {
            $feature->addVote($validated['client_id']);
        }

        return new FeatureResource($feature);
    }

    /**
     * Display the specified feature.
     */
    public function show(Feature $feature): FeatureResource
    {
        $feature->load('project');

        return new FeatureResource($feature);
    }
}
