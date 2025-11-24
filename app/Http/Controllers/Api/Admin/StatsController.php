<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Display statistics for the admin.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'projects' => [
                'total' => Project::count(),
                'active' => Project::active()->count(),
            ],
            'features' => [
                'total' => Feature::count(),
                'by_status' => Feature::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ],
            'votes' => [
                'total' => Vote::count(),
            ],
            'top_features' => Feature::with('project:id,name,slug')
                ->orderBy('vote_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'title' => $feature->title,
                        'slug' => $feature->slug,
                        'project' => $feature->project->name ?? null,
                        'vote_count' => $feature->vote_count,
                        'status' => $feature->status,
                    ];
                }),
        ];

        return response()->json($stats);
    }
}
