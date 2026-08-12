<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyImportToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.import.token', '');

        if ($expected === '') {
            return response()->json([
                'message' => 'Import API is not configured. Set IMPORT_API_TOKEN.',
            ], 503);
        }

        $provided = $request->bearerToken()
            ?? $request->header('X-Import-Token')
            ?? '';

        if (! hash_equals($expected, (string) $provided)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
