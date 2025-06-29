<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(
                [
                    'statusCode' => 401,
                    'message' => 'Unauthorized. User not authenticated.',
                ],
                401,
            );
        }

        if ($user->role !== $role) {
            return response()->json(
                [
                    'statusCode' => 403,
                    'message' => 'Unauthorized. You dont have access.',
                ],
                403,
            );
        }

        return $next($request);
    }
}
