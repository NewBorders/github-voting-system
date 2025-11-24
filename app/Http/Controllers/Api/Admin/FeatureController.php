<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;

class FeatureController extends Controller
{
    /**
     * Update the specified feature.
     */
    public function update(UpdateFeatureRequest $request, Feature $feature): FeatureResource
    {
        $validated = $request->validated();

        $feature->update($validated);

        return new FeatureResource($feature);
    }

    /**
     * Remove the specified feature from storage.
     */
    public function destroy(Feature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json([
            'message' => 'Feature deleted successfully',
        ]);
    }
}
