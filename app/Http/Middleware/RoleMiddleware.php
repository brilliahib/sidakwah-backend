<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(
                [
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 401,
                        'message' => 'Unauthorized. User not authenticated.',
                    ],
                    'data' => null,
                ],
                401,
            );
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        if (!in_array($user->role, $allowedRoles, true)) {
            return response()->json(
                [
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 403,
                        'message' => 'Forbidden. You do not have access to this resource.',
                    ],
                    'data' => null,
                ],
                403,
            );
        }

        return $next($request);
    }
}
