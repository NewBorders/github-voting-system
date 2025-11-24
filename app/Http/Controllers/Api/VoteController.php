<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoteRequest;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    /**
     * Cast a vote on a feature.
     */
    public function store(VoteRequest $request, Feature $feature): JsonResponse
    {
        $validated = $request->validated();
        $clientId = $validated['client_id'];

        $feature->addVote($clientId);

        return response()->json([
            'feature_id' => $feature->id,
            'vote_count' => $feature->fresh()->vote_count,
            'voted' => true,
        ]);
    }

    /**
     * Remove a vote from a feature.
     */
    public function destroy(VoteRequest $request, Feature $feature): JsonResponse
    {
        $validated = $request->validated();
        $clientId = $validated['client_id'];

        $removed = $feature->removeVote($clientId);

        return response()->json([
            'feature_id' => $feature->id,
            'vote_count' => $feature->fresh()->vote_count,
            'voted' => false,
        ]);
    }
}
