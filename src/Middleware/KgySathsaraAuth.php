<?php

namespace KgySathsara\Monitor\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class KgySathsaraAuth
{
    public function handle($request, Closure $next)
    {
        // Allow if authenticated
        if (Auth::check()) {
            return $next($request);
        }
        
        // Check for secret key
        $secret = config('kgy-sathsara.dashboard.secret_key');
        if ($secret && $request->get('key') === $secret) {
            return $next($request);
        }
        
        // Check IP whitelist
        $allowedIps = config('kgy-sathsara.dashboard.allowed_ips', []);
        if (!empty($allowedIps) && in_array($request->ip(), $allowedIps)) {
            return $next($request);
        }
        
        abort(403, 'Unauthorized access to KGY Sathsara Monitor');
    }
}