<?php

namespace App\Http\Middleware;

use App\Domains\Billing\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $keyId = session('company_api_key_id');

        if (!$keyId) {
            return redirect()->route('company.login');
        }

        $apiKey = ApiKey::with('company')->find($keyId);

        if (!$apiKey || $apiKey->revoked_at) {
            session()->forget('company_api_key_id');
            return redirect()->route('company.login');
        }

        $request->merge(['api_company' => $apiKey->company, 'api_key' => $apiKey]);

        return $next($request);
    }
}
