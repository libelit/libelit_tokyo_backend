<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLender
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

        if ($user->type !== UserTypeEnum::LENDER) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Lender account required.',
            ], 403);
        }

        if (!$user->lenderProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Lender profile not found. Please contact support.',
            ], 404);
        }

        return $next($request);
    }
}
