<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureLandlord
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->header('X-User-Id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Add any additional landlord verification logic here

        return $next($request);
    }
} 