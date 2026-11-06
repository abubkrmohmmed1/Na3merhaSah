<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TierRateLimit
{
    public function handle(Request $request, Closure $next, string $tier = 'free'): Response
    {
        $company = $request->get('api_company');
        $key = 'api-tier:' . ($company?->id ?? 'anonymous');

        $limits = [
            'free' => ['max' => 100, 'decay' => 1440],    // 100/day
            'pro' => ['max' => 10000, 'decay' => 1440],    // 10k/day
            'enterprise' => ['max' => 100000, 'decay' => 1440], // 100k/day
        ];

        $plan = $company?->plan ?? 'free';
        $limit = $limits[$plan] ?? $limits['free'];

        $limiter = app(RateLimiter::class);

        if ($limiter->tooManyAttempts($key, $limit['max'])) {
            return response()->json([
                'message' => 'تجاوزت حد الاستخدام المسموح لخطتك. قم بترقية خطتك.',
                'retry_after' => $limiter->availableIn($key),
            ], 429);
        }

        $limiter->hit($key, $limit['decay'] * 60);

        return $next($request);
    }
}
