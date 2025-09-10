<?php

namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Calculate actual occupied critical postes (postes that are critical AND have operators assigned)
        $occupiedCriticalPostes = Poste::where('is_critical', true)
            ->whereHas('operators')
            ->count();

        // Calculate non-occupied critical postes (critical postes without operators)
        $nonOccupiedCriticalPostes = Poste::where('is_critical', true)
            ->whereDoesntHave('operators')
            ->count();

        // Get critical postes with their assigned operators and attendance status
        $criticalPostesWithOperators = Poste::where('is_critical', true)
            ->with(['operators' => function ($query) {
                $query->with(['attendances' => function ($attendanceQuery) {
                    $attendanceQuery->whereDate('date', today());
                }]);
            }])
            ->get()
            ->flatMap(function ($poste) {
                return $poste->operators->map(function ($operator) use ($poste) {
                    // Check if operator has attendance record for today
                    $todayAttendance = $operator->attendances->first();
                    $isPresent = !$todayAttendance || $todayAttendance->status === 'present';
                    
                    return [
                        'ligne' => $operator->ligne,
                        'poste_name' => $poste->name,
                        'operator_name' => $operator->first_name . ' ' . $operator->last_name,
                        'is_present' => $isPresent
                    ];
                });
            });

        return view('dashboard', compact('occupiedCriticalPostes', 'nonOccupiedCriticalPostes', 'criticalPostesWithOperators'));
    }
}
