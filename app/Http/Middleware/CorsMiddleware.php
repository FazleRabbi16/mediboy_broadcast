<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        $allowedOrigins = [
            'https://admin.mediboy.org',
            'https://p.mediboy.org',
            'http://localhost:5173',
            'http://localhost:3000',
            'http://localhost:5174',
        ];

        $response = $next($request);

        // Check if the request's origin is allowed
        if (in_array($request->header('Origin'), $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $request->header('Origin'))
                     ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                     ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        return $response;
    }
}
