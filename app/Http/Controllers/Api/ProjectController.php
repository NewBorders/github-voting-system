<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $activeOnly = $request->boolean('active_only', true);

        $query = Project::query();

        if ($activeOnly) {
            $query->active();
        }

        $projects = $query->orderBy('name')->get();

        return ProjectResource::collection($projects);
    }
}
