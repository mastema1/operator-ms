<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SecurityMonitoringService
{
    /**
     * Get security metrics for monitoring dashboard.
     */
    public static function getSecurityMetrics(): array
    {
        $cacheKey = 'security_metrics_' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () { // 5 minutes cache
            return [
                'failed_logins' => self::getFailedLoginAttempts(),
                'suspicious_ips' => self::getSuspiciousIpActivity(),
                'rate_limit_violations' => self::getRateLimitViolations(),
                'tenant_violations' => self::getTenantViolations(),
                'session_anomalies' => self::getSessionAnomalies(),
                'security_events' => self::getRecentSecurityEvents(),
                'system_health' => self::getSystemHealthMetrics()
            ];
        });
    }

    /**
     * Get failed login attempts in the last 24 hours.
     */
    private static function getFailedLoginAttempts(): array
    {
        // This would typically query your authentication logs
        // For now, return mock data structure
        return [
            'total' => 0,
            'unique_ips' => 0,
            'top_ips' => [],
            'hourly_breakdown' => []
        ];
    }

    /**
     * Get suspicious IP activity.
     */
    private static function getSuspiciousIpActivity(): array
    {
        return [
            'blocked_ips' => 0,
            'flagged_ips' => 0,
            'recent_blocks' => []
        ];
    }

    /**
     * Get rate limit violations.
     */
    private static function getRateLimitViolations(): array
    {
        $violations = [];
        $cacheKeys = Cache::getRedis()->keys('rate_limit:*');
        
        foreach ($cacheKeys as $key) {
            $attempts = Cache::get($key, 0);
            if ($attempts > 50) { // Threshold for flagging
                $violations[] = [
                    'key' => $key,
                    'attempts' => $attempts,
                    'timestamp' => now()
                ];
            }
        }

        return [
            'total_violations' => count($violations),
            'recent_violations' => array_slice($violations, -10)
        ];
    }

    /**
     * Get tenant isolation violations.
     */
    private static function getTenantViolations(): array
    {
        // This would query your security logs for tenant violations
        return [
            'total' => 0,
            'recent' => []
        ];
    }

    /**
     * Get session anomalies.
     */
    private static function getSessionAnomalies(): array
    {
        return [
            'concurrent_sessions' => 0,
            'suspicious_sessions' => 0,
            'expired_sessions' => 0
        ];
    }

    /**
     * Get recent security events.
     */
    private static function getRecentSecurityEvents(): array
    {
        // This would parse your security log files
        return [
            'total_events' => 0,
            'critical_events' => 0,
            'recent_events' => []
        ];
    }

    /**
     * Get system health metrics related to security.
     */
    private static function getSystemHealthMetrics(): array
    {
        return [
            'database_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
            'cache_hit_ratio' => self::getCacheHitRatio(),
            'memory_usage' => memory_get_usage(true),
            'disk_space' => disk_free_space('/'),
            'ssl_certificate_expiry' => self::getSSLCertificateExpiry()
        ];
    }

    /**
     * Get cache hit ratio.
     */
    private static function getCacheHitRatio(): float
    {
        try {
            $info = Cache::getRedis()->info();
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            
            if ($hits + $misses === 0) {
                return 0.0;
            }
            
            return round(($hits / ($hits + $misses)) * 100, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get SSL certificate expiry information.
     */
    private static function getSSLCertificateExpiry(): ?array
    {
        $url = config('app.url');
        
        if (!str_starts_with($url, 'https://')) {
            return null;
        }

        try {
            $host = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT) ?: 443;
            
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $stream = stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$stream) {
                return null;
            }
            
            $cert = stream_context_get_params($stream)['options']['ssl']['peer_certificate'];
            $certData = openssl_x509_parse($cert);
            
            return [
                'expires_at' => date('Y-m-d H:i:s', $certData['validTo_time_t']),
                'days_until_expiry' => ceil(($certData['validTo_time_t'] - time()) / 86400),
                'issuer' => $certData['issuer']['CN'] ?? 'Unknown'
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to check SSL certificate', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check system security status.
     */
    public static function getSecurityStatus(): array
    {
        $issues = [];
        
        // Check debug mode in production
        if (app()->environment('production') && config('app.debug')) {
            $issues[] = [
                'severity' => 'critical',
                'message' => 'Debug mode is enabled in production',
                'recommendation' => 'Set APP_DEBUG=false in .env file'
            ];
        }

        // Check HTTPS
        if (!request()->isSecure() && app()->environment('production')) {
            $issues[] = [
                'severity' => 'high',
                'message' => 'Application is not using HTTPS',
                'recommendation' => 'Configure SSL/TLS certificate'
            ];
        }

        // Check session configuration
        if (!config('session.secure') && app()->environment('production')) {
            $issues[] = [
                'severity' => 'medium',
                'message' => 'Secure session cookies not enabled',
                'recommendation' => 'Set SESSION_SECURE_COOKIE=true'
            ];
        }

        // Check database encryption
        if (!config('security.database.encrypt_connection')) {
            $issues[] = [
                'severity' => 'medium',
                'message' => 'Database connection not encrypted',
                'recommendation' => 'Enable SSL for database connection'
            ];
        }

        return [
            'status' => empty($issues) ? 'secure' : 'issues_found',
            'issues_count' => count($issues),
            'issues' => $issues,
            'last_check' => now()->toISOString()
        ];
    }

    /**
     * Log security event with context.
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        $defaultContext = [
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'request_id' => request()->header('X-Request-ID') ?: uniqid()
        ];

        Log::channel('security')->warning($event, array_merge($defaultContext, $context));
    }
}
