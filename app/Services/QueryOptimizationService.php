<?php

namespace App\Services;

use App\Models\Operator;
use App\Models\CriticalPosition;
use App\Models\Attendance;
use App\Models\BackupAssignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class QueryOptimizationService
{
    /**
     * Get optimized operators list with eager loading
     */
    public static function getOperatorsWithAttendance(int $tenantId, array $posteIds = []): Collection
    {
        $cacheKey = DashboardCacheManager::getOperatorListCacheKey($tenantId);
        
        return Cache::remember($cacheKey, DashboardCacheManager::OPERATOR_LIST_CACHE_DURATION, function () use ($tenantId, $posteIds) {
            $query = Operator::where('tenant_id', $tenantId);
            
            if (!empty($posteIds)) {
                $query->whereIn('poste_id', $posteIds);
            }
            
            return $query->select('id', 'first_name', 'last_name', 'poste_id', 'ligne', 'matricule')
                ->with(['attendances' => function ($query) {
                    $query->forToday()
                          ->select('id', 'operator_id', 'status');
                }])
                ->with(['poste:id,name'])
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        });
    }

    /**
     * Get critical positions with optimized eager loading
     */
    public static function getCriticalPositions(int $tenantId): Collection
    {
        $cacheKey = DashboardCacheManager::getCriticalPositionsCacheKey($tenantId);
        
        return Cache::remember($cacheKey, DashboardCacheManager::CRITICAL_POSITIONS_CACHE_DURATION, function () use ($tenantId) {
            return CriticalPosition::where('tenant_id', $tenantId)
                ->where('is_critical', true)
                ->with(['poste:id,name'])
                ->select('id', 'poste_id', 'ligne', 'tenant_id')
                ->get();
        });
    }

    /**
     * Get today's attendances with optimized query
     */
    public static function getTodayAttendances(int $tenantId, array $operatorIds = []): Collection
    {
        $cacheKey = DashboardCacheManager::getTodayAttendancesCacheKey($tenantId);
        
        return Cache::remember($cacheKey, 3, function () use ($tenantId, $operatorIds) { // 3 seconds for real-time updates
            $query = Attendance::forToday();
            
            if (!empty($operatorIds)) {
                $query->whereIn('operator_id', $operatorIds);
            }
            
            // Add tenant filter if column exists
            if (\Schema::hasColumn('attendances', 'tenant_id')) {
                $query->where('tenant_id', $tenantId);
            }
            
            return $query->select('id', 'operator_id', 'date', 'status')
                ->with(['operator:id,first_name,last_name,poste_id,ligne'])
                ->get();
        });
    }

    /**
     * Get backup assignments with optimized query
     */
    public static function getTodayBackupAssignments(int $tenantId, array $posteIds = []): Collection
    {
        $cacheKey = "backup_assignments_today_{$tenantId}_" . now()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 3, function () use ($tenantId, $posteIds) { // 3 seconds for real-time updates
            $query = BackupAssignment::where('tenant_id', $tenantId)
                ->whereDate('assigned_date', today());
            
            if (!empty($posteIds)) {
                $query->whereIn('poste_id', $posteIds);
            }
            
            return $query->select('id', 'poste_id', 'operator_id', 'backup_operator_id', 'backup_slot', 'assigned_date')
                ->with(['operator:id,first_name,last_name'])
                ->with(['backupOperator:id,first_name,last_name'])
                ->with(['poste:id,name'])
                ->orderBy('backup_slot')
                ->get();
        });
    }

    /**
     * Optimized dashboard data query
     */
    public static function getDashboardData(int $tenantId): array
    {
        // Get critical positions first
        $criticalPositions = self::getCriticalPositions($tenantId);
        
        if ($criticalPositions->isEmpty()) {
            return [
                'occupiedCriticalPostes' => 0,
                'nonOccupiedCriticalPostes' => 0,
                'criticalPostesWithOperators' => collect(),
                'dashboardTitle' => 'Dashboard'
            ];
        }

        // Get unique poste IDs
        $posteIds = $criticalPositions->pluck('poste_id')->unique()->toArray();
        
        // Get operators with attendance (cached)
        $operators = self::getOperatorsWithAttendance($tenantId, $posteIds)
            ->groupBy(['poste_id', 'ligne']);
        
        // Get backup assignments (cached) - now grouped by operator_id
        $backupAssignments = self::getTodayBackupAssignments($tenantId, $posteIds)
            ->keyBy('operator_id');

        // Process positions efficiently
        $occupiedCount = 0;
        $nonOccupiedCount = 0;
        $tableData = collect();

        foreach ($criticalPositions as $position) {
            $posteId = $position->poste_id;
            $ligne = $position->ligne;
            
            // Skip positions with missing poste data (data integrity issue)
            if (!$position->poste) {
                \Log::warning("Critical position {$position->id} references missing poste {$posteId}");
                continue;
            }
            
            // Get operators for this position
            $positionOperators = $operators->get($posteId, collect())->get($ligne, collect());
            
            // Determine occupancy based on actual attendance and backup coverage
            $hasOperators = $positionOperators->isNotEmpty();
            $hasPresentOperators = false;
            $hasBackupCoverage = false;
            
            // Check each operator's attendance status and backup coverage
            foreach ($positionOperators as $operator) {
                $attendance = $operator->attendances->first();
                $isPresent = !$attendance || $attendance->status === 'present';
                
                if ($isPresent) {
                    $hasPresentOperators = true;
                } elseif ($backupAssignments->has($operator->id)) {
                    // Absent operator has backup coverage
                    $hasBackupCoverage = true;
                }
            }
            
            // Position is occupied if it has present operators OR absent operators with backup coverage
            $isOccupied = $hasPresentOperators || $hasBackupCoverage;
            
            // Update counters
            if ($isOccupied) {
                $occupiedCount++;
            } else {
                $nonOccupiedCount++;
            }
            
            // Add to table data
            if ($hasOperators) {
                // Add regular operators with their specific backup assignments
                foreach ($positionOperators as $operator) {
                    $attendance = $operator->attendances->first();
                    $isPresent = !$attendance || $attendance->status === 'present';
                    
                    // Get backup assignment for this specific operator
                    $operatorBackup = $backupAssignments->get($operator->id);
                    $backupData = [];
                    
                    if ($operatorBackup) {
                        $backupData = [[
                            'id' => $operatorBackup->id,
                            'slot' => $operatorBackup->backup_slot,
                            'operator_name' => $operatorBackup->backupOperator->first_name . ' ' . $operatorBackup->backupOperator->last_name,
                            'operator_id' => $operatorBackup->backup_operator_id
                        ]];
                    }
                    
                    // Determine intelligent status
                    $hasBackup = !empty($backupData);
                    $statusTag = '';
                    $statusClass = '';
                    
                    if (!$isPresent && !$hasBackup) {
                        // Absent with no backup = URGENT
                        $statusTag = 'URGENT';
                        $statusClass = 'bg-red-500 text-white animate-pulse';
                    } elseif (!$isPresent && $hasBackup) {
                        // Absent but has backup = COVERED
                        $statusTag = 'COVERED';
                        $statusClass = 'bg-yellow-500 text-white';
                    } else {
                        // Present = OCCUPIED
                        $statusTag = 'OCCUPIED';
                        $statusClass = 'bg-green-500 text-white';
                    }
                    
                    $tableData->push([
                        'poste_id' => $posteId,
                        'ligne' => $ligne,
                        'poste_name' => $position->poste->name,
                        'operator_name' => $operator->first_name . ' ' . $operator->last_name,
                        'operator_id' => $operator->id, // Add operator ID for backup assignment
                        'is_present' => $isPresent,
                        'is_critical' => true,
                        'is_non_occupe' => false,
                        'occupation_type' => 'operator',
                        'backup_assignments' => $backupData,
                        'status_tag' => $statusTag,
                        'status_class' => $statusClass,
                        'urgency_level' => !$isPresent && !$hasBackup ? 3 : (!$isPresent && $hasBackup ? 2 : 1) // 3=urgent, 2=covered, 1=occupied
                    ]);
                }
            } else {
                // Position has no operators - add as non-occupied entry
                $tableData->push([
                    'poste_id' => $posteId,
                    'ligne' => $ligne,
                    'poste_name' => $position->poste->name,
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
            }
        }

        // Intelligent Priority Sorting: Most urgent situations first
        $tableData = $tableData->sortBy([
            ['is_non_occupe', 'desc'],           // Non-occupied positions first (most urgent)
            ['urgency_level', 'desc'],           // Then by urgency: URGENT(3) > COVERED(2) > OCCUPIED(1)
            ['is_critical', 'desc'],             // Critical positions prioritized
            ['poste_name', 'asc'],               // Then by poste name
            ['ligne', 'asc']                     // Finally by ligne
        ])->values();

        return [
            'occupiedCriticalPostes' => $occupiedCount,
            'nonOccupiedCriticalPostes' => $nonOccupiedCount,
            'criticalPostesWithOperators' => $tableData,
            'dashboardTitle' => 'Dashboard'
        ];
    }

    /**
     * Clear all query optimization caches
     */
    public static function clearAllCaches(int $tenantId): void
    {
        DashboardCacheManager::clearAllCaches($tenantId);
        
        // Clear additional optimization caches
        $today = now()->format('Y-m-d');
        Cache::forget("backup_assignments_today_{$tenantId}_{$today}");
    }
}
