<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringService
{
    /**
     * Track query performance for monitoring
     */
    public static function trackQueryPerformance(string $operation, callable $callback, array $context = [])
    {
        $startTime = microtime(true);
        $startQueries = DB::getQueryLog();
        DB::enableQueryLog();
        
        try {
            $result = $callback();
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            $endQueries = DB::getQueryLog();
            $queryCount = count($endQueries) - count($startQueries);
            
            // Log performance metrics
            self::logPerformanceMetrics($operation, $executionTime, $queryCount, $context);
            
            return $result;
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            self::logPerformanceMetrics($operation, $executionTime, 0, array_merge($context, [
                'error' => $e->getMessage()
            ]));
            
            throw $e;
        }
    }
    
    /**
     * Log performance metrics
     */
    private static function logPerformanceMetrics(string $operation, float $executionTime, int $queryCount, array $context = []): void
    {
        $metrics = [
            'operation' => $operation,
            'execution_time_ms' => round($executionTime, 2),
            'query_count' => $queryCount,
            'timestamp' => now()->toISOString(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];
        
        if (!empty($context)) {
            $metrics['context'] = $context;
        }
        
        // Log to Laravel log
        Log::channel('performance')->info('Performance Metrics', $metrics);
        
        // Store in cache for real-time monitoring (keep last 100 entries)
        self::storeMetricsInCache($operation, $metrics);
    }
    
    /**
     * Store metrics in cache for real-time monitoring
     */
    private static function storeMetricsInCache(string $operation, array $metrics): void
    {
        $cacheKey = "performance_metrics_{$operation}";
        $existingMetrics = Cache::get($cacheKey, []);
        
        // Add new metric
        array_unshift($existingMetrics, $metrics);
        
        // Keep only last 100 entries
        $existingMetrics = array_slice($existingMetrics, 0, 100);
        
        // Store for 1 hour
        Cache::put($cacheKey, $existingMetrics, 3600);
    }
    
    /**
     * Get performance metrics for an operation
     */
    public static function getPerformanceMetrics(string $operation): array
    {
        $cacheKey = "performance_metrics_{$operation}";
        return Cache::get($cacheKey, []);
    }
    
    /**
     * Get performance summary for all operations
     */
    public static function getPerformanceSummary(): array
    {
        $operations = ['dashboard', 'operators_list', 'backup_assignment'];
        $summary = [];
        
        foreach ($operations as $operation) {
            $metrics = self::getPerformanceMetrics($operation);
            
            if (!empty($metrics)) {
                $executionTimes = array_column($metrics, 'execution_time_ms');
                $queryCounts = array_column($metrics, 'query_count');
                
                $summary[$operation] = [
                    'avg_execution_time_ms' => round(array_sum($executionTimes) / count($executionTimes), 2),
                    'min_execution_time_ms' => min($executionTimes),
                    'max_execution_time_ms' => max($executionTimes),
                    'avg_query_count' => round(array_sum($queryCounts) / count($queryCounts), 1),
                    'total_requests' => count($metrics),
                    'last_updated' => $metrics[0]['timestamp'] ?? null
                ];
            }
        }
        
        return $summary;
    }
    
    /**
     * Clear performance metrics
     */
    public static function clearMetrics(string $operation = null): void
    {
        if ($operation) {
            Cache::forget("performance_metrics_{$operation}");
        } else {
            $operations = ['dashboard', 'operators_list', 'backup_assignment'];
            foreach ($operations as $op) {
                Cache::forget("performance_metrics_{$op}");
            }
        }
    }
    
    /**
     * Get system performance overview
     */
    public static function getSystemOverview(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
            'cache_driver' => config('cache.default'),
            'database_driver' => config('database.default'),
            'current_memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];
    }
}
