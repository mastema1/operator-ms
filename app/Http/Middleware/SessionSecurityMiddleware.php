<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionSecurityMiddleware
{
    /**
     * Handle an incoming request and enforce session security.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for non-authenticated routes
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        
        // Check session timeout
        $this->checkSessionTimeout($request);
        
        // Validate session integrity
        $this->validateSessionIntegrity($request, $user);
        
        // Check for suspicious activity
        $this->checkSuspiciousActivity($request, $user);
        
        // Update last activity
        session(['last_activity' => now()->timestamp]);
        
        return $next($request);
    }

    /**
     * Check if session has timed out.
     */
    private function checkSessionTimeout(Request $request): void
    {
        $timeout = config('security.session.timeout', 480) * 60; // Convert to seconds
        $lastActivity = session('last_activity', now()->timestamp);
        
        if (now()->timestamp - $lastActivity > $timeout) {
            Log::info('Session timeout', [
                'user_id' => auth()->id(),
                'last_activity' => date('Y-m-d H:i:s', $lastActivity),
                'timeout_minutes' => config('security.session.timeout', 480)
            ]);
            
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            
            abort(401, 'Session expired due to inactivity');
        }
    }

    /**
     * Validate session integrity.
     */
    private function validateSessionIntegrity(Request $request, $user): void
    {
        // Check if user agent changed (potential session hijacking)
        $currentUserAgent = $request->userAgent();
        $sessionUserAgent = session('user_agent');
        
        if ($sessionUserAgent && $sessionUserAgent !== $currentUserAgent) {
            Log::warning('User agent mismatch detected', [
                'user_id' => $user->id,
                'session_user_agent' => $sessionUserAgent,
                'current_user_agent' => $currentUserAgent,
                'ip' => $request->ip()
            ]);
            
            // Optionally logout user for security
            if (config('security.session.strict_validation', false)) {
                auth()->logout();
                session()->invalidate();
                abort(401, 'Session security violation detected');
            }
        }
        
        // Store user agent if not set
        if (!$sessionUserAgent) {
            session(['user_agent' => $currentUserAgent]);
        }
        
        // Check IP address consistency (optional)
        $currentIp = $request->ip();
        $sessionIp = session('ip_address');
        
        if ($sessionIp && $sessionIp !== $currentIp) {
            Log::info('IP address changed during session', [
                'user_id' => $user->id,
                'session_ip' => $sessionIp,
                'current_ip' => $currentIp
            ]);
            
            // Update IP in session
            session(['ip_address' => $currentIp]);
        } elseif (!$sessionIp) {
            session(['ip_address' => $currentIp]);
        }
    }

    /**
     * Check for suspicious activity patterns.
     */
    private function checkSuspiciousActivity(Request $request, $user): void
    {
        // Check for rapid requests (potential bot activity)
        $requestCount = session('request_count', 0);
        $requestWindow = session('request_window', now()->timestamp);
        
        // Reset counter every minute
        if (now()->timestamp - $requestWindow > 60) {
            $requestCount = 0;
            $requestWindow = now()->timestamp;
        }
        
        $requestCount++;
        session([
            'request_count' => $requestCount,
            'request_window' => $requestWindow
        ]);
        
        // Flag suspicious activity (more than 120 requests per minute)
        if ($requestCount > 120) {
            Log::warning('Suspicious request rate detected', [
                'user_id' => $user->id,
                'request_count' => $requestCount,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
        }
        
        // Check for suspicious IP
        if (\App\Services\SecurityService::isIpSuspicious($request->ip())) {
            Log::warning('Request from suspicious IP', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
        }
    }
}
