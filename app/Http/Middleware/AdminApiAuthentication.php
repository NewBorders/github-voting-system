<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.admin.api_token');

        if (empty($expectedToken)) {
            return response()->json([
                'message' => 'Admin API token not configured',
            ], 500);
        }

        $providedToken = $request->header('X-Admin-Token');

        if (empty($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
