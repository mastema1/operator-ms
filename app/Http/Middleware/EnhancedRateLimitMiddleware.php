<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnhancedRateLimitMiddleware
{
    /**
     * Rate limits for different endpoint types.
     */
    private const LIMITS = [
        'api' => ['requests' => 60, 'minutes' => 1],      // API endpoints
        'auth' => ['requests' => 5, 'minutes' => 1],       // Authentication
        'search' => ['requests' => 30, 'minutes' => 1],    // Search operations
        'default' => ['requests' => 100, 'minutes' => 1]   // General requests
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        $limit = self::LIMITS[$type] ?? self::LIMITS['default'];
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limit['requests']) {
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'path' => $request->path(),
                'type' => $type,
                'attempts' => $attempts
            ]);
            
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => $limit['minutes'] * 60
            ], 429);
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($limit['minutes']));

        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $limit['requests']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limit['requests'] - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($limit['minutes'])->timestamp);

        return $response;
    }

    /**
     * Resolve request signature for rate limiting.
     */
    private function resolveRequestSignature(Request $request, string $type): string
    {
        $identifier = auth()->check() ? 'user:' . auth()->id() : 'ip:' . $request->ip();
        return "rate_limit:{$type}:{$identifier}";
    }
}
