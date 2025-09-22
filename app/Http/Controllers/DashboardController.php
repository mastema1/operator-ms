<?php

namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\Attendance;
use App\Models\DashboardSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\DashboardCacheManager;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Check if user is authenticated
        if (!auth()->check() || !auth()->user()) {
            abort(401, 'User not authenticated');
        }

        $user = auth()->user();
        if (!$user->tenant_id) {
            abort(500, 'User has no tenant assigned');
        }

        // Use centralized cache management for consistent cache keys
        $cacheKey = \App\Services\DashboardCacheManager::getCacheKey();
        
        $dashboardData = Cache::remember($cacheKey, \App\Services\DashboardCacheManager::DASHBOARD_CACHE_DURATION, function () use ($user) {
            $tenantId = $user->tenant_id;
            
            // Use optimized service for better performance
            return \App\Services\QueryOptimizationService::getDashboardData($tenantId);
        });

        // Get the dashboard title for this tenant
        $dashboardTitle = DashboardSettings::getTitleForTenant($user->tenant_id);

        return view('dashboard', array_merge($dashboardData, [
            'dashboardTitle' => $dashboardTitle
        ]));
    }

    /**
     * Clear dashboard cache (useful when attendance, postes, or operators are updated)
     */
    public function clearCache()
    {
        // Check if user is authenticated
        if (!auth()->check() || !auth()->user()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $user = auth()->user();
        if (!$user->tenant_id) {
            return response()->json(['error' => 'User has no tenant assigned'], 500);
        }

        // Clear all dashboard cache entries for current tenant
        $tenantId = $user->tenant_id;
        $pattern = 'dashboard_data_' . $tenantId . '_*';
        
        // Get all cache keys matching the pattern and clear them
        $cacheKeys = Cache::getRedis()->keys($pattern);
        foreach ($cacheKeys as $key) {
            Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
        }
        
        return response()->json(['message' => 'Dashboard cache cleared']);
    }

    /**
     * Update the dashboard title for the current tenant
     */
    public function updateTitle(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255|min:1'
        ]);

        try {
            $settings = DashboardSettings::updateTitle($request->title);
            
            return response()->json([
                'success' => true,
                'title' => $settings->title,
                'message' => 'Dashboard title updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update dashboard title: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the current dashboard title for the tenant
     */
    public function getTitle(): JsonResponse
    {
        try {
            $title = DashboardSettings::getTitleForTenant();
            
            return response()->json([
                'success' => true,
                'title' => $title
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard title: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Static method to clear dashboard cache from other controllers
     * @deprecated Use DashboardCacheManager::clearDashboardCache() instead
     */
    public static function clearDashboardCache()
    {
        DashboardCacheManager::clearDashboardCache();
    }
}
