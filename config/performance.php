<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for performance optimization
    | features including caching, query optimization, and monitoring.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache durations for different types of data to optimize
    | performance for concurrent users.
    |
    */
    'cache' => [
        // Critical real-time data (dashboard, attendance)
        'critical_data_duration' => env('CACHE_CRITICAL_DURATION', 30), // 30 seconds
        
        // Reference data (operators, postes)
        'reference_data_duration' => env('CACHE_REFERENCE_DURATION', 300), // 5 minutes
        
        // Search results
        'search_results_duration' => env('CACHE_SEARCH_DURATION', 60), // 1 minute
        
        // Static configuration data
        'static_data_duration' => env('CACHE_STATIC_DURATION', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for database query optimization and performance monitoring.
    |
    */
    'queries' => [
        // Enable query logging for performance monitoring
        'enable_logging' => env('ENABLE_QUERY_LOGGING', true),
        
        // Maximum number of queries per request (warning threshold)
        'max_queries_per_request' => env('MAX_QUERIES_PER_REQUEST', 10),
        
        // Maximum execution time in milliseconds (warning threshold)
        'max_execution_time_ms' => env('MAX_EXECUTION_TIME_MS', 100),
        
        // Enable single-query optimization for dashboard
        'enable_single_query_optimization' => env('ENABLE_SINGLE_QUERY_OPT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Concurrent User Optimization
    |--------------------------------------------------------------------------
    |
    | Settings optimized for handling multiple concurrent users.
    |
    */
    'concurrency' => [
        // Target concurrent users
        'target_concurrent_users' => env('TARGET_CONCURRENT_USERS', 32),
        
        // Records per tenant (for optimization calculations)
        'avg_records_per_tenant' => env('AVG_RECORDS_PER_TENANT', 200),
        
        // Enable concurrent user tracking
        'track_concurrent_users' => env('TRACK_CONCURRENT_USERS', true),
        
        // Cache warming for frequently accessed data
        'enable_cache_warming' => env('ENABLE_CACHE_WARMING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for performance monitoring and alerting.
    |
    */
    'monitoring' => [
        // Enable performance tracking middleware
        'enable_tracking' => env('ENABLE_PERFORMANCE_TRACKING', true),
        
        // Routes to monitor (in addition to automatic detection)
        'monitored_routes' => [
            'dashboard',
            'operators.index',
            'backup-assignments.available-operators',
        ],
        
        // Performance thresholds for alerts
        'thresholds' => [
            'slow_query_ms' => env('SLOW_QUERY_THRESHOLD_MS', 200),
            'high_memory_mb' => env('HIGH_MEMORY_THRESHOLD_MB', 128),
            'many_queries' => env('MANY_QUERIES_THRESHOLD', 15),
        ],
        
        // Metrics retention (number of entries to keep in cache)
        'metrics_retention_count' => env('METRICS_RETENTION_COUNT', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for frontend performance optimization.
    |
    */
    'frontend' => [
        // Enable lazy loading for heavy components
        'enable_lazy_loading' => env('ENABLE_LAZY_LOADING', true),
        
        // Pagination settings for optimal performance
        'default_pagination_size' => env('DEFAULT_PAGINATION_SIZE', 15),
        'max_pagination_size' => env('MAX_PAGINATION_SIZE', 50),
        
        // Search result limits
        'search_result_limit' => env('SEARCH_RESULT_LIMIT', 20),
        
        // Enable component caching
        'enable_component_caching' => env('ENABLE_COMPONENT_CACHING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    |
    | Database-specific optimization settings.
    |
    */
    'database' => [
        // Enable query result caching
        'enable_query_caching' => env('ENABLE_QUERY_CACHING', true),
        
        // Connection pool settings (if supported)
        'connection_pool_size' => env('DB_CONNECTION_POOL_SIZE', 10),
        
        // Enable prepared statement caching
        'enable_prepared_statements' => env('ENABLE_PREPARED_STATEMENTS', true),
        
        // Index optimization settings
        'auto_analyze_tables' => env('AUTO_ANALYZE_TABLES', false),
    ],
];
