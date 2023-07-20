<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class validateAppCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $appKey = $request->header('AppKey');
        $appSecret = $request->header('AppSecret');

        /* ---------------- Check if header contains AppKey and AppSecret --------------- */
        if (!$appKey || !$appSecret) {
            return response()->json(['message' => 'AppKey and AppSecret are required.'], 401);
        }

        /* ---------------- Check if AppKey and AppSecret are valid --------------- */
        if ($appKey !== env('CLIENT_APP_KEY') || $appSecret !== env('CLIENT_APP_SECRET')) {
            return response()->json(['message' => 'Invalid AppKey or AppSecret.'], 401);
        }

        return $next($request);
    }
}
