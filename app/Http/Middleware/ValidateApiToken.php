<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token || $token !== config('app.esp32_api_token')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}