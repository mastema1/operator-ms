<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DashboardCacheManager
{
    /**
     * Cache duration constants (in seconds)
     */
    const DASHBOARD_CACHE_DURATION = 3; // 3 seconds for immediate updates
    const OPERATOR_LIST_CACHE_DURATION = 3; // 3 seconds for real-time updates
    const POSTE_LIST_CACHE_DURATION = 300; // 5 minutes (postes change less frequently)
    const CRITICAL_POSITIONS_CACHE_DURATION = 3; // 3 seconds for real-time updates
    
    /**
     * Generate the dashboard cache key for a specific tenant and date
     */
    public static function getCacheKey(?int $tenantId = null, ?string $date = null): string
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                throw new \Exception('User not authenticated');
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                throw new \Exception('User has no tenant assigned');
            }
        }
        $date = $date ?? now()->format('Y-m-d-H');
        
        return "dashboard_data_{$tenantId}_{$date}";
    }
    
    /**
     * Generate cache key for operator list
     */
    public static function getOperatorListCacheKey(int $tenantId): string
    {
        return "operators_list_{$tenantId}";
    }
    
    /**
     * Generate cache key for critical positions
     */
    public static function getCriticalPositionsCacheKey(int $tenantId): string
    {
        return "critical_positions_{$tenantId}";
    }
    
    /**
     * Generate cache key for today's attendances
     */
    public static function getTodayAttendancesCacheKey(int $tenantId): string
    {
        $today = now()->format('Y-m-d');
        return "attendances_today_{$tenantId}_{$today}";
    }
    
    /**
     * Clear all dashboard cache entries for a tenant
     */
    public static function clearDashboardCache(?int $tenantId = null): void
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return; // Silently return if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return; // Silently return if no tenant
            }
        }
        
        // Clear current hour cache
        $currentHourKey = self::getCacheKey($tenantId, now()->format('Y-m-d-H'));
        Cache::forget($currentHourKey);
        
        // Clear previous hour cache (in case we're at the boundary)
        $previousHourKey = self::getCacheKey($tenantId, now()->subHour()->format('Y-m-d-H'));
        Cache::forget($previousHourKey);
        
        // Clear next hour cache (in case of clock differences)
        $nextHourKey = self::getCacheKey($tenantId, now()->addHour()->format('Y-m-d-H'));
        Cache::forget($nextHourKey);
        
        // Also clear any legacy cache keys that might exist
        $legacyKeys = [
            "dashboard_data_{$tenantId}_" . now()->format('Y-m-d'),
            "dashboard_data_" . now()->format('Y-m-d'),
            "dashboard_data_" . now()->format('Y-m-d-H-i')
        ];
        
        foreach ($legacyKeys as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Clear all related caches for a tenant
     */
    public static function clearAllCaches(?int $tenantId = null): void
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return; // Silently return if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return; // Silently return if no tenant
            }
        }
        
        // Clear dashboard cache
        self::clearDashboardCache($tenantId);
        
        // Clear operator list cache
        Cache::forget(self::getOperatorListCacheKey($tenantId));
        
        // Clear critical positions cache
        Cache::forget(self::getCriticalPositionsCacheKey($tenantId));
        
        // Clear today's attendances cache
        Cache::forget(self::getTodayAttendancesCacheKey($tenantId));
        
        // Clear postes dropdown cache
        Cache::forget("postes_dropdown_golden_order_{$tenantId}");
    }
    
    /**
     * Clear dashboard cache when attendance changes
     */
    public static function clearOnAttendanceChange(?int $tenantId = null): void
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return; // Silently return if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return; // Silently return if no tenant
            }
        }
        
        self::clearDashboardCache($tenantId);
        Cache::forget(self::getTodayAttendancesCacheKey($tenantId));
        
        // Also clear any related caches that depend on attendance
        // $tenantId already validated above
        
        // Clear critical positions cache since attendance affects occupancy
        Cache::forget("critical_positions_{$tenantId}");
        Cache::forget("non_critical_positions_{$tenantId}");
    }
    
    /**
     * Clear dashboard cache when operator data changes
     */
    public static function clearOnOperatorChange(?int $tenantId = null): void
    {
        self::clearDashboardCache($tenantId);
        
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return; // Silently return if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return; // Silently return if no tenant
            }
        }
        
        // Clear operators and postes list caches (including new Golden Order caches)
        Cache::forget('operators_api_list');
        Cache::forget('postes_list');
        Cache::forget('postes_dropdown_' . $tenantId);
        Cache::forget('allowed_postes_dropdown_' . $tenantId);
        Cache::forget('postes_dropdown_golden_order_' . $tenantId);
        Cache::forget('allowed_postes_dropdown_golden_order_' . $tenantId);
        Cache::forget("critical_positions_{$tenantId}");
        Cache::forget("non_critical_positions_{$tenantId}");
    }
    
    /**
     * Clear dashboard cache when poste data changes
     */
    public static function clearOnPosteChange(?int $tenantId = null): void
    {
        self::clearDashboardCache($tenantId);
        
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return; // Silently return if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return; // Silently return if no tenant
            }
        }
        
        // Clear postes list cache since poste changes affect dropdown options (including Golden Order caches)
        Cache::forget('postes_list');
        Cache::forget('postes_dropdown_' . $tenantId);
        Cache::forget('allowed_postes_dropdown_' . $tenantId);
        Cache::forget('postes_dropdown_golden_order_' . $tenantId);
        Cache::forget('allowed_postes_dropdown_golden_order_' . $tenantId);
        Cache::forget("postes_dropdown_{$tenantId}");
        Cache::forget("allowed_postes_dropdown_{$tenantId}");
    }
    
    /**
     * Clear dashboard cache when backup assignments change
     */
    public static function clearOnBackupChange(?int $tenantId = null): void
    {
        self::clearDashboardCache($tenantId);
    }
    
    /**
     * Get cache statistics for debugging
     */
    public static function getCacheStats(?int $tenantId = null): array
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return []; // Return empty array if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return []; // Return empty array if no tenant
            }
        }
        
        $keys = [
            'current_hour' => self::getCacheKey($tenantId, now()->format('Y-m-d-H')),
            'previous_hour' => self::getCacheKey($tenantId, now()->subHour()->format('Y-m-d-H')),
            'critical_positions' => "critical_positions_{$tenantId}",
            'operators_api' => 'operators_api_list',
            'postes_dropdown' => "postes_dropdown_{$tenantId}"
        ];
        
        $stats = [];
        foreach ($keys as $name => $key) {
            $stats[$name] = [
                'key' => $key,
                'exists' => Cache::has($key),
                'value_preview' => Cache::has($key) ? 'Cached' : 'Not cached'
            ];
        }
        
        return $stats;
    }
}
