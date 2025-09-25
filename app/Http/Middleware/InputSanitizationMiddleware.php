<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware
{
    /**
     * Handle an incoming request and sanitize input data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip sanitization for API endpoints that need raw data
        $skipRoutes = [
            'api/backup-assignments/available-operators'
        ];

        if (!in_array($request->path(), $skipRoutes)) {
            $this->sanitizeInput($request);
        }

        return $next($request);
    }

    /**
     * Sanitize input data to prevent XSS and injection attacks.
     */
    private function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        $sanitized = $this->sanitizeArray($input);
        $request->replace($sanitized);
    }

    /**
     * Recursively sanitize array data.
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                // Trim whitespace
                $value = trim($value);
                
                // Remove null bytes
                $value = str_replace("\0", '', $value);
                
                // For search fields, allow basic characters but escape HTML
                if (in_array($key, ['search', 'name', 'first_name', 'last_name', 'matricule'])) {
                    $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                } else {
                    $sanitized[$key] = $value;
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
