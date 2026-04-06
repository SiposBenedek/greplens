<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Try Sanctum first (user tokens)
        if ($request->bearerToken() && auth('sanctum')->check()) {
            return $next($request);
        }

        // Try project API key via header or query param
        $apiKey = $request->header('X-Api-Key')
            ?? $request->bearerToken()
            ?? $request->query('key');

        if ($apiKey) {
            $project = Project::findByApiKey($apiKey);

            if ($project) {
                $project->update(['last_seen_at' => now()]);
                $request->attributes->set('project', $project);
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
