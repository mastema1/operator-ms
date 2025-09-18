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
        // Remove aggressive caching to ensure dashboard shows real-time data
        // Only use very short-term caching (30 seconds) to prevent excessive queries during page refreshes
        $cacheKey = 'dashboard_data_' . auth()->user()->tenant_id . '_' . now()->format('Y-m-d-H-i');
        
        // Clear existing cache to ensure ghost data fix takes effect immediately
        Cache::forget($cacheKey);
        
        $dashboardData = Cache::remember($cacheKey, 30, function () {
            // Get all critical positions (poste+ligne combinations) for the current tenant
            $criticalPositions = \App\Models\CriticalPosition::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_critical', true)
                ->with([
                    'poste:id,name',
                    'poste.operators' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'poste_id', 'ligne');
                    },
                    'poste.operators.attendances' => function ($query) {
                        $query->whereDate('date', today())
                              ->select('id', 'operator_id', 'date', 'status');
                    },
                    'poste.backupAssignments' => function ($query) {
                        $query->whereDate('assigned_date', today())
                              ->with('backupOperator:id,first_name,last_name')
                              ->orderBy('backup_slot');
                    }
                ])
                ->get();

            // Calculate occupied critical positions
            $occupiedCriticalPositions = 0;
            $nonOccupiedCriticalPositions = 0;
            
            // Process each critical position
            $criticalPositionData = collect();
            
            foreach ($criticalPositions as $criticalPosition) {
                $poste = $criticalPosition->poste;
                $ligne = $criticalPosition->ligne;
                
                // Find operators for this specific poste+ligne combination
                $operatorsForPosition = $poste->operators->filter(function ($operator) use ($ligne) {
                    return $operator->ligne === $ligne;
                });
                
                // Check if at least one operator on this position is present
                $presentOperators = $operatorsForPosition->filter(function ($operator) {
                    $todayAttendance = $operator->attendances->first();
                    return !$todayAttendance || $todayAttendance->status === 'present';
                });
                
                // Count this critical position
                $isOccupied = !$presentOperators->isEmpty();
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
            $criticalPositionsWithOperators = $criticalPositionData->flatMap(function ($positionData) {
                $poste = $positionData['poste'];
                $ligne = $positionData['ligne'];
                $operators = $positionData['operators'];
                $isOccupied = $positionData['is_occupied'];
                
                if ($operators->isEmpty()) {
                    // No operators for this critical position - don't show in table
                    return collect();
                }
                
                return $operators->map(function ($operator) use ($poste, $ligne, $isOccupied) {
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
                        'backup_assignments' => $poste->backupAssignments->map(function ($assignment) {
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
