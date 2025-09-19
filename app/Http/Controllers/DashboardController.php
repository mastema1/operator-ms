<?php

namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Use effective caching strategy with 5-minute cache duration
        // Cache key based on hour to reduce cache fragmentation
        $cacheKey = 'dashboard_data_' . auth()->user()->tenant_id . '_' . now()->format('Y-m-d-H');
        
        $dashboardData = Cache::remember($cacheKey, 300, function () {
            $tenantId = auth()->user()->tenant_id;
            
            // Step 1: Get critical positions with minimal data
            $criticalPositions = \App\Models\CriticalPosition::where('tenant_id', $tenantId)
                ->where('is_critical', true)
                ->select('id', 'poste_id', 'ligne', 'tenant_id')
                ->get();
            
            if ($criticalPositions->isEmpty()) {
                return [
                    'occupiedCriticalPostes' => 0,
                    'nonOccupiedCriticalPostes' => 0,
                    'criticalPostesWithOperators' => collect()
                ];
            }
            
            // Step 2: Get all relevant postes in one query
            $posteIds = $criticalPositions->pluck('poste_id')->unique();
            $postes = \App\Models\Poste::whereIn('id', $posteIds)
                ->select('id', 'name')
                ->get()
                ->keyBy('id');
            
            // Step 3: Get all operators for these postes with today's attendance
            $operators = \App\Models\Operator::whereIn('poste_id', $posteIds)
                ->select('id', 'first_name', 'last_name', 'poste_id', 'ligne')
                ->with(['attendances' => function ($query) {
                    $query->whereDate('date', today())
                          ->select('id', 'operator_id', 'status');
                }])
                ->get()
                ->groupBy(['poste_id', 'ligne']);
            
            // Step 4: Get backup assignments for today
            $backupAssignments = \App\Models\BackupAssignment::whereIn('poste_id', $posteIds)
                ->whereDate('assigned_date', today())
                ->with('backupOperator:id,first_name,last_name')
                ->select('id', 'poste_id', 'backup_operator_id', 'backup_slot')
                ->orderBy('backup_slot')
                ->get()
                ->groupBy('poste_id');

            // Calculate occupied critical positions
            $occupiedCriticalPositions = 0;
            $nonOccupiedCriticalPositions = 0;
            
            // Process each critical position
            $criticalPositionData = collect();
            
            foreach ($criticalPositions as $criticalPosition) {
                $posteId = $criticalPosition->poste_id;
                $ligne = $criticalPosition->ligne;
                $poste = $postes->get($posteId);
                
                // Get operators for this specific poste+ligne combination
                $operatorsForPosition = $operators->get($posteId, collect())->get($ligne, collect());
                
                // Check if at least one operator on this position is present
                $isOccupied = false;
                foreach ($operatorsForPosition as $operator) {
                    $attendance = $operator->attendances->first();
                    $isPresent = !$attendance || $attendance->status === 'present';
                    if ($isPresent) {
                        $isOccupied = true;
                        break;
                    }
                }
                
                // Count this critical position
                if ($isOccupied) {
                    $occupiedCriticalPositions++;
                } else {
                    $nonOccupiedCriticalPositions++;
                }
                
                // Store for detailed table data
                $criticalPositionData->push([
                    'poste' => $poste,
                    'ligne' => $ligne,
                    'operators' => $operatorsForPosition,
                    'is_occupied' => $isOccupied
                ]);
            }
            
            // Generate table data for critical positions with their operators
            $criticalPositionsWithOperators = $criticalPositionData->flatMap(function ($positionData) use ($backupAssignments) {
                $poste = $positionData['poste'];
                $ligne = $positionData['ligne'];
                $operators = $positionData['operators'];
                $isOccupied = $positionData['is_occupied'];
                
                if ($operators->isEmpty()) {
                    // No operators for this critical position - don't show in table
                    return collect();
                }
                
                // Get backup assignments for this poste
                $posteBackups = $backupAssignments->get($poste->id, collect());
                
                return $operators->map(function ($operator) use ($poste, $ligne, $isOccupied, $posteBackups) {
                    // Check if operator has attendance record for today
                    $todayAttendance = $operator->attendances->first();
                    $isPresent = !$todayAttendance || $todayAttendance->status === 'present';
                    
                    return [
                        'poste_id' => $poste->id,
                        'ligne' => $ligne,
                        'poste_name' => $poste->name,
                        'operator_name' => $operator->first_name . ' ' . $operator->last_name,
                        'is_present' => $isPresent,
                        'is_critical' => true, // All positions in this query are critical
                        'is_non_occupe' => !$isOccupied,
                        'backup_assignments' => $posteBackups->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'slot' => $assignment->backup_slot,
                                'operator_name' => $assignment->backupOperator->first_name . ' ' . $assignment->backupOperator->last_name,
                                'operator_id' => $assignment->backup_operator_id
                            ];
                        })
                    ];
                });
            });
            
            // Sort by priority: non-occupé critical first, then other critical, then alphabetically
            $criticalPositionsWithOperators = $criticalPositionsWithOperators->sortBy([
                ['is_non_occupe', 'desc'], // Non-occupé first
                ['is_critical', 'desc'],   // Critical positions next
                ['poste_name', 'asc'],     // Then alphabetically by poste name
                ['ligne', 'asc']           // Then by ligne
            ])->values(); // Reset array keys

            return [
                'occupiedCriticalPostes' => $occupiedCriticalPositions,
                'nonOccupiedCriticalPostes' => $nonOccupiedCriticalPositions,
                'criticalPostesWithOperators' => $criticalPositionsWithOperators
            ];
        });

        return view('dashboard', $dashboardData);
    }

    /**
     * Clear dashboard cache (useful when attendance, postes, or operators are updated)
     */
    public function clearCache()
    {
        // Clear all dashboard cache entries for current tenant
        $tenantId = auth()->user()->tenant_id;
        $pattern = 'dashboard_data_' . $tenantId . '_*';
        
        // Get all cache keys matching the pattern and clear them
        $cacheKeys = Cache::getRedis()->keys($pattern);
        foreach ($cacheKeys as $key) {
            Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
        }
        
        return response()->json(['message' => 'Dashboard cache cleared']);
    }

    /**
     * Static method to clear dashboard cache from other controllers
     */
    public static function clearDashboardCache()
    {
        if (auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
            
            // Clear current and recent cache entries (covers last hour and next hour)
            $baseTime = now();
            for ($hourOffset = -1; $hourOffset <= 1; $hourOffset++) {
                $time = $baseTime->copy()->addHours($hourOffset);
                for ($minute = 0; $minute < 60; $minute++) {
                    $cacheKey = 'dashboard_data_' . $tenantId . '_' . $time->format('Y-m-d-H') . '-' . sprintf('%02d', $minute);
                    Cache::forget($cacheKey);
                }
            }
        }
    }
}
