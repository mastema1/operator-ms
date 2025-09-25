<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SecurityService
{
    /**
     * Validate that a resource belongs to the current user's tenant.
     */
    public static function validateTenantOwnership(string $model, int $resourceId, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        
        if (!$tenantId) {
            return false;
        }

        $modelClass = "App\\Models\\{$model}";
        
        if (!class_exists($modelClass)) {
            Log::warning('Invalid model in tenant validation', [
                'model' => $model,
                'resource_id' => $resourceId,
                'tenant_id' => $tenantId
            ]);
            return false;
        }

        $resource = $modelClass::find($resourceId);
        
        if (!$resource || $resource->tenant_id !== $tenantId) {
            Log::warning('Attempted access to resource from different tenant', [
                'model' => $model,
                'resource_id' => $resourceId,
                'resource_tenant' => $resource?->tenant_id,
                'user_tenant' => $tenantId,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            return false;
        }

        return true;
    }

    /**
     * Sanitize SQL search input to prevent injection.
     */
    public static function sanitizeSearchInput(string $input): string
    {
        // Remove dangerous characters
        $sanitized = preg_replace('/[^\w\s\-\.@]/', '', $input);
        
        // Limit length
        $sanitized = substr($sanitized, 0, 100);
        
        // Trim whitespace
        return trim($sanitized);
    }

    /**
     * Log security events.
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        $defaultContext = [
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId()
        ];

        Log::channel('security')->warning($event, array_merge($defaultContext, $context));
    }

    /**
     * Validate file upload security.
     */
    public static function validateFileUpload($file): array
    {
        $errors = [];

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            $errors[] = 'File too large (max 5MB)';
        }

        // Check allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'File type not allowed';
        }

        // Check MIME type
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'Invalid file type';
        }

        return $errors;
    }

    /**
     * Generate secure random token.
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Check if IP is from a suspicious location or known threat.
     */
    public static function isIpSuspicious(string $ip): bool
    {
        // Basic checks - extend with threat intelligence feeds
        $suspiciousRanges = [
            // Add known malicious IP ranges here
        ];

        foreach ($suspiciousRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is within a given range.
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        
        return ($ip & $mask) === $subnet;
    }
}
