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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Admin API token not configured',
                ], 500);
            }
            abort(500, 'Admin API token not configured');
        }

        // Check for token in header (API) or session (Web UI)
        $providedToken = $request->header('X-Admin-Token') ?? $request->session()->get('admin_token');

        // For web requests, also check cookie
        if (!$providedToken && $request->hasCookie('admin_token')) {
            $providedToken = $request->cookie('admin_token');
        }

        if (empty($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
            
            // For web UI, redirect to login
            return redirect()->route('admin.login')->with('error', 'Please authenticate to access admin panel');
        }

        return $next($request);
    }
}
