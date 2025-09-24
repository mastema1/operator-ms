<?php

namespace App\Services;

use App\Models\Operator;
use App\Models\CriticalPosition;
use App\Models\Attendance;
use App\Models\BackupAssignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AdvancedQueryOptimizationService
{
    // Optimized cache durations for concurrent load
    const CRITICAL_DATA_CACHE_DURATION = 30; // 30 seconds for critical real-time data
    const REFERENCE_DATA_CACHE_DURATION = 300; // 5 minutes for reference data
    const SEARCH_CACHE_DURATION = 60; // 1 minute for search results
    
    /**
     * Get dashboard data with advanced optimization for concurrent users
     */
    public static function getOptimizedDashboardData(int $tenantId): array
    {
        $cacheKey = "dashboard_optimized_{$tenantId}_" . now()->format('Y-m-d-H-i');
        
        return Cache::remember($cacheKey, self::CRITICAL_DATA_CACHE_DURATION, function () use ($tenantId) {
            // Use a single optimized query to get all required data
            $dashboardData = self::getSingleQueryDashboardData($tenantId);
            
            return self::processDashboardData($dashboardData, $tenantId);
        });
    }
    
    /**
     * Single optimized query to get all dashboard data
     */
    private static function getSingleQueryDashboardData(int $tenantId): Collection
    {
        // Single query with all necessary joins and conditions
        return DB::table('critical_positions as cp')
            ->select([
                'cp.poste_id',
                'cp.ligne',
                'p.name as poste_name',
                'o.id as operator_id',
                'o.first_name',
                'o.last_name',
                'a.status as attendance_status',
                'ba.id as backup_id',
                'bo.first_name as backup_first_name',
                'bo.last_name as backup_last_name',
                'ba.backup_slot'
            ])
            ->join('postes as p', 'cp.poste_id', '=', 'p.id')
            ->leftJoin('operators as o', function ($join) use ($tenantId) {
                $join->on('cp.poste_id', '=', 'o.poste_id')
                     ->on('cp.ligne', '=', 'o.ligne')
                     ->where('o.tenant_id', '=', $tenantId);
            })
            ->leftJoin('attendances as a', function ($join) {
                $join->on('o.id', '=', 'a.operator_id')
                     ->whereDate('a.date', today());
            })
            ->leftJoin('backup_assignments as ba', function ($join) {
                $join->on('o.id', '=', 'ba.operator_id')
                     ->whereDate('ba.assigned_date', today());
            })
            ->leftJoin('operators as bo', 'ba.backup_operator_id', '=', 'bo.id')
            ->where('cp.tenant_id', $tenantId)
            ->where('cp.is_critical', true)
            ->orderBy('cp.poste_id')
            ->orderBy('cp.ligne')
            ->get();
    }
    
    /**
     * Process raw dashboard data into structured format
     */
    private static function processDashboardData(Collection $rawData, int $tenantId): array
    {
        $occupiedCount = 0;
        $nonOccupiedCount = 0;
        $tableData = collect();
        $ligneBreakdown = collect();
        
        // Group by position (poste_id + ligne)
        $groupedData = $rawData->groupBy(function ($item) {
            return $item->poste_id . '_' . $item->ligne;
        });
        
        foreach ($groupedData as $positionKey => $positionData) {
            $position = $positionData->first();
            $operators = $positionData->filter(function ($item) {
                return !is_null($item->operator_id);
            });
            
            // Initialize ligne breakdown
            if (!$ligneBreakdown->has($position->ligne)) {
                $ligneBreakdown->put($position->ligne, [
                    'occupied' => 0,
                    'non_occupied' => 0
                ]);
            }
            
            if ($operators->isEmpty()) {
                // No operators assigned - non-occupied
                $nonOccupiedCount++;
                $currentCounts = $ligneBreakdown->get($position->ligne);
                $currentCounts['non_occupied']++;
                $ligneBreakdown->put($position->ligne, $currentCounts);
                
                // Add vacant position entry
                $tableData->push([
                    'poste_id' => $position->poste_id,
                    'ligne' => $position->ligne,
                    'poste_name' => $position->poste_name,
                    'operator_name' => 'Non-occupé',
                    'operator_id' => null,
                    'is_present' => false,
                    'is_critical' => true,
                    'is_non_occupe' => true,
                    'occupation_type' => 'vacant',
                    'backup_assignments' => [],
                    'status_tag' => 'URGENT',
                    'status_class' => 'bg-red-500 text-white animate-pulse',
                    'urgency_level' => 3
                ]);
            } else {
                // Process each operator
                $positionOccupied = false;
                
                foreach ($operators as $operator) {
                    $isPresent = is_null($operator->attendance_status) || $operator->attendance_status === 'present';
                    $hasBackup = !is_null($operator->backup_id);
                    
                    if ($isPresent || $hasBackup) {
                        $positionOccupied = true;
                    }
                    
                    // Prepare backup data
                    $backupData = [];
                    if ($hasBackup) {
                        $backupData = [[
                            'id' => $operator->backup_id,
                            'slot' => $operator->backup_slot,
                            'operator_name' => $operator->backup_first_name . ' ' . $operator->backup_last_name,
                            'operator_id' => $operator->operator_id
                        ]];
                    }
                    
                    // Determine status
                    $statusTag = '';
                    $statusClass = '';
                    $urgencyLevel = 1;
                    
                    if (!$isPresent && !$hasBackup) {
                        $statusTag = 'URGENT';
                        $statusClass = 'bg-red-500 text-white animate-pulse';
                        $urgencyLevel = 3;
                    } elseif (!$isPresent && $hasBackup) {
                        $statusTag = 'Occupied';
                        $statusClass = 'bg-green-500 text-white';
                        $urgencyLevel = 2;
                    }
                    
                    $tableData->push([
                        'poste_id' => $position->poste_id,
                        'ligne' => $position->ligne,
                        'poste_name' => $position->poste_name,
                        'operator_name' => $operator->first_name . ' ' . $operator->last_name,
                        'operator_id' => $operator->operator_id,
                        'is_present' => $isPresent,
                        'is_critical' => true,
                        'is_non_occupe' => false,
                        'occupation_type' => 'operator',
                        'backup_assignments' => $backupData,
                        'status_tag' => $statusTag,
                        'status_class' => $statusClass,
                        'urgency_level' => $urgencyLevel
                    ]);
                }
                
                // Update counters
                if ($positionOccupied) {
                    $occupiedCount++;
                    $currentCounts = $ligneBreakdown->get($position->ligne);
                    $currentCounts['occupied']++;
                    $ligneBreakdown->put($position->ligne, $currentCounts);
                } else {
                    $nonOccupiedCount++;
                    $currentCounts = $ligneBreakdown->get($position->ligne);
                    $currentCounts['non_occupied']++;
                    $ligneBreakdown->put($position->ligne, $currentCounts);
                }
            }
        }
        
        // Sort data by urgency
        $tableData = $tableData->sortBy([
            ['is_non_occupe', 'desc'],
            ['urgency_level', 'desc'],
            ['poste_name', 'asc'],
            ['ligne', 'asc']
        ])->values();
        
        return [
            'occupiedCriticalPostes' => $occupiedCount,
            'nonOccupiedCriticalPostes' => $nonOccupiedCount,
            'criticalPostesWithOperators' => $tableData,
            'ligneBreakdown' => $ligneBreakdown->sortKeys(),
            'dashboardTitle' => 'Dashboard'
        ];
    }
    
    /**
     * Get optimized operators list with minimal queries
     */
    public static function getOptimizedOperatorsList(int $tenantId, string $search = '', bool $criticalOnly = false): Collection
    {
        $cacheKey = "operators_list_{$tenantId}_" . md5($search . ($criticalOnly ? '1' : '0'));
        
        return Cache::remember($cacheKey, self::SEARCH_CACHE_DURATION, function () use ($tenantId, $search, $criticalOnly) {
            $query = DB::table('operators as o')
                ->select([
                    'o.id',
                    'o.first_name',
                    'o.last_name',
                    'o.matricule',
                    'o.poste_id',
                    'o.ligne',
                    'p.name as poste_name',
                    'a.status as attendance_status',
                    DB::raw('CASE WHEN cp.id IS NOT NULL THEN 1 ELSE 0 END as is_critical')
                ])
                ->join('postes as p', 'o.poste_id', '=', 'p.id')
                ->leftJoin('attendances as a', function ($join) {
                    $join->on('o.id', '=', 'a.operator_id')
                         ->whereDate('a.date', today());
                })
                ->leftJoin('critical_positions as cp', function ($join) use ($tenantId) {
                    $join->on('o.poste_id', '=', 'cp.poste_id')
                         ->on('o.ligne', '=', 'cp.ligne')
                         ->where('cp.tenant_id', '=', $tenantId)
                         ->where('cp.is_critical', '=', true);
                })
                ->where('o.tenant_id', $tenantId);
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $searchTerm = '%' . $search . '%';
                    $q->where('o.first_name', 'like', $searchTerm)
                      ->orWhere('o.last_name', 'like', $searchTerm)
                      ->orWhere('o.matricule', 'like', $searchTerm)
                      ->orWhere('p.name', 'like', $searchTerm);
                });
            }
            
            // Apply critical filter
            if ($criticalOnly) {
                $query->whereNotNull('cp.id');
            }
            
            return $query->orderBy('o.first_name')
                        ->orderBy('o.last_name')
                        ->get();
        });
    }
    
    /**
     * Get available operators for backup assignment with optimization
     */
    public static function getAvailableBackupOperators(int $tenantId, int $operatorId): Collection
    {
        $cacheKey = "backup_operators_{$tenantId}_{$operatorId}_" . now()->format('Y-m-d');
        
        return Cache::remember($cacheKey, self::REFERENCE_DATA_CACHE_DURATION, function () use ($tenantId, $operatorId) {
            return DB::table('operators as o')
                ->select([
                    'o.id',
                    'o.first_name',
                    'o.last_name',
                    'o.matricule',
                    'p.name as poste_name'
                ])
                ->join('postes as p', 'o.poste_id', '=', 'p.id')
                ->where('o.tenant_id', $tenantId)
                ->where('o.id', '!=', $operatorId)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('backup_assignments as ba')
                          ->whereColumn('ba.backup_operator_id', 'o.id')
                          ->whereDate('ba.assigned_date', today());
                })
                ->orderBy('o.first_name')
                ->orderBy('o.last_name')
                ->get();
        });
    }
    
    /**
     * Clear all optimization caches for a tenant
     */
    public static function clearTenantCaches(int $tenantId): void
    {
        $patterns = [
            "dashboard_optimized_{$tenantId}_*",
            "operators_list_{$tenantId}_*",
            "backup_operators_{$tenantId}_*"
        ];
        
        foreach ($patterns as $pattern) {
            Cache::flush(); // For simplicity, we'll flush all cache
            // In production, you might want to use tagged caching for more granular control
        }
    }
}
