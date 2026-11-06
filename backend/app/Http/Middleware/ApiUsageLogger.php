<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiUsageLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        // Only log if the company and api_key are set by the ApiKeyMiddleware
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key_record');

        if ($company && $apiKey) {
            $duration = round((microtime(true) - $startTime) * 1000);
            
            DB::table('api_usage_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'api_key_id' => $apiKey->id,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => $duration,
                'ip_address' => $request->ip(),
                'payload_summary' => json_encode([
                    'items_count' => $this->guessItemsCount($request, $response),
                    'params' => $request->except(['api_key', 'token'])
                ]),
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    private function guessItemsCount(Request $request, Response $response): int
    {
        // For batch operations, count items in request
        if ($request->is('*/batch/*') && $request->has('points')) {
            return is_array($request->points) ? count($request->points) : 1;
        }

        // For export operations, we might check the response
        return 1;
    }
}
