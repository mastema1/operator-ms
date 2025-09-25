<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLoggingMiddleware
{
    /**
     * Sensitive routes that require detailed logging.
     */
    private const SENSITIVE_ROUTES = [
        'operators.store',
        'operators.update', 
        'operators.destroy',
        'postes.store',
        'postes.update',
        'postes.destroy',
        'backup.assign',
        'backup.remove',
        'profile.update',
        'profile.destroy'
    ];

    /**
     * Fields to exclude from logging for privacy.
     */
    private const EXCLUDED_FIELDS = [
        'password',
        'password_confirmation',
        '_token',
        '_method'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);

        // Log sensitive operations
        if ($this->shouldLog($request)) {
            $this->logRequest($request, $response, $endTime - $startTime);
        }

        return $response;
    }

    /**
     * Determine if request should be logged.
     */
    private function shouldLog(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        
        return in_array($routeName, self::SENSITIVE_ROUTES) ||
               $request->isMethod('POST') ||
               $request->isMethod('PUT') ||
               $request->isMethod('DELETE') ||
               str_starts_with($request->path(), 'api/');
    }

    /**
     * Log the request details.
     */
    private function logRequest(Request $request, Response $response, float $duration): void
    {
        $user = auth()->user();
        $routeName = $request->route()?->getName();
        
        $logData = [
            'timestamp' => now()->toISOString(),
            'method' => $request->method(),
            'path' => $request->path(),
            'route_name' => $routeName,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->id,
            'tenant_id' => $user?->tenant_id,
            'session_id' => session()->getId()
        ];

        // Add request data for sensitive operations
        if (in_array($routeName, self::SENSITIVE_ROUTES)) {
            $logData['request_data'] = $this->sanitizeRequestData($request->all());
        }

        // Add query parameters for GET requests
        if ($request->isMethod('GET') && $request->query()) {
            $logData['query_params'] = $request->query();
        }

        // Log with appropriate level based on status code
        if ($response->getStatusCode() >= 400) {
            Log::warning('HTTP Request', $logData);
        } else {
            Log::info('HTTP Request', $logData);
        }
    }

    /**
     * Sanitize request data for logging.
     */
    private function sanitizeRequestData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, self::EXCLUDED_FIELDS)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeRequestData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
