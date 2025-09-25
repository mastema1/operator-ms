<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TenantIsolationMiddleware
{
    /**
     * Handle an incoming request and enforce tenant isolation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for non-authenticated routes
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        
        // Ensure user has a tenant
        if (!$user->tenant_id) {
            Log::warning('User without tenant attempted access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'path' => $request->path(),
                'ip' => $request->ip()
            ]);
            
            auth()->logout();
            return redirect()->route('login')->withErrors(['error' => 'Access denied: No tenant assigned']);
        }

        // Validate tenant exists and is active
        $tenant = \App\Models\Tenant::find($user->tenant_id);
        if (!$tenant) {
            Log::error('User with invalid tenant attempted access', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'path' => $request->path(),
                'ip' => $request->ip()
            ]);
            
            auth()->logout();
            return redirect()->route('login')->withErrors(['error' => 'Access denied: Invalid tenant']);
        }

        // Add tenant context to request for controllers
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);

        return $next($request);
    }
}
