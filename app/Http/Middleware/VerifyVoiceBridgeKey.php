<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyVoiceBridgeKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('voice.bridge_key');
        if ($expected === null || $expected === '') {
            abort(503, 'Voice bridge is not configured.');
        }

        $provided = $request->header('X-Voice-Bridge-Key');
        if ($provided === null || $provided === '') {
            abort(401, 'Missing X-Voice-Bridge-Key.');
        }

        if (! hash_equals((string) $expected, (string) $provided)) {
            abort(403, 'Invalid voice bridge key.');
        }

        return $next($request);
    }
}
