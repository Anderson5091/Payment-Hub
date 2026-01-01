<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\HmacService;

class VerifyApiSignature
{
    public function handle($request, Closure $next)
    {
        $signature = $request->header('X-Signature');

        if (!$signature) {
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $data = $request->except(['signature']);

        if (!HmacService::verify($data, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
