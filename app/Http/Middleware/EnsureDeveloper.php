<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloper
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->type !== UserTypeEnum::DEVELOPER) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Developer account required.',
            ], 403);
        }

        if (!$user->developerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Developer profile not found. Please contact support.',
            ], 404);
        }

        return $next($request);
    }
}
