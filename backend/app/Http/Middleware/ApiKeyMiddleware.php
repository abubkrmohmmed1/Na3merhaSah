<?php

namespace App\Http\Middleware;

use App\Domains\Billing\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (!$key) {
            return response()->json(['message' => 'مفتاح API مطلوب (X-API-Key header)'], 401);
        }

        $apiKey = ApiKey::where('key', $key)->valid()->first();

        if (!$apiKey) {
            return response()->json(['message' => 'مفتاح API غير صالح أو منتهي الصلاحية'], 401);
        }

        $apiKey->update(['last_used_at' => now()]);

        $request->merge(['api_company' => $apiKey->company, 'api_key_record' => $apiKey]);

        return $next($request);
    }
}
