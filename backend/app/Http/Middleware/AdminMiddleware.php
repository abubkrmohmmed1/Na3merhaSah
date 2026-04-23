<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من الصلاحيات: الوصول متاح للإداريين، المهندسين، والمساحين
        if ($request->user() && in_array($request->user()->role, ['admin', 'engineer', 'surveyor'])) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized access'], 403);
    }
}
