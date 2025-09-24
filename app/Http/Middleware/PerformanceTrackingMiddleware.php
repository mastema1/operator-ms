<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\PerformanceMonitoringService;
use Symfony\Component\HttpFoundation\Response;

class PerformanceTrackingMiddleware
{
    /**
     * Handle an incoming request and track performance
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track performance for specific routes that need monitoring
        $routesToTrack = [
            'dashboard',
            'operators.index',
            'backup-assignments.available-operators',
            'livewire.dashboard',
            'livewire.operators'
        ];
        
        $routeName = $request->route()?->getName();
        $shouldTrack = in_array($routeName, $routesToTrack) || 
                      str_contains($request->path(), 'livewire') ||
                      str_contains($request->path(), 'dashboard');
        
        if (!$shouldTrack) {
            return $next($request);
        }
        
        // Determine operation name
        $operation = $this->getOperationName($request);
        
        // Track the request performance
        return PerformanceMonitoringService::trackQueryPerformance(
            $operation,
            function () use ($next, $request) {
                return $next($request);
            },
            [
                'route' => $routeName,
                'method' => $request->method(),
                'path' => $request->path(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()?->tenant_id,
                'concurrent_users' => $this->estimateConcurrentUsers()
            ]
        );
    }
    
    /**
     * Get operation name for tracking
     */
    private function getOperationName(Request $request): string
    {
        $routeName = $request->route()?->getName();
        $path = $request->path();
        
        if (str_contains($path, 'dashboard') || $routeName === 'dashboard') {
            return 'dashboard';
        }
        
        if (str_contains($path, 'operators') || $routeName === 'operators.index') {
            return 'operators_list';
        }
        
        if (str_contains($path, 'backup-assignments') || str_contains($path, 'backup')) {
            return 'backup_assignment';
        }
        
        if (str_contains($path, 'livewire')) {
            if (str_contains($path, 'dashboard')) {
                return 'dashboard';
            }
            if (str_contains($path, 'operators')) {
                return 'operators_list';
            }
            return 'livewire_component';
        }
        
        return 'general';
    }
    
    /**
     * Estimate concurrent users (simplified approach)
     */
    private function estimateConcurrentUsers(): int
    {
        // Simple estimation based on active sessions in the last 5 minutes
        // In production, you might want to use Redis or a more sophisticated approach
        $cacheKey = 'active_users_' . now()->format('Y-m-d-H-i');
        $activeUsers = cache()->get($cacheKey, []);
        
        $userId = auth()->id();
        if ($userId && !in_array($userId, $activeUsers)) {
            $activeUsers[] = $userId;
            cache()->put($cacheKey, $activeUsers, 300); // 5 minutes
        }
        
        return count($activeUsers);
    }
}
