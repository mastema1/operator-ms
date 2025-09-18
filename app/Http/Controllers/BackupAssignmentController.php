<?php

namespace App\Http\Controllers;

use App\Models\BackupAssignment;
use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BackupAssignmentController extends Controller
{
    public function assign(Request $request): JsonResponse
    {
        \Log::info('BackupAssignmentController::assign called', $request->all());
        
        $request->validate([
            'poste_id' => 'required|exists:postes,id',
            'operator_id' => 'required|exists:operators,id',
            'backup_slot' => 'required|integer|in:1'
        ]);

        try {
            \Log::info('Using updateOrCreate method');
            
            // Delete any existing backup assignment for this poste (single backup only)
            BackupAssignment::where('poste_id', $request->poste_id)
                ->whereDate('assigned_date', today())
                ->delete();
            
            // Create new assignment
            $assignment = BackupAssignment::create([
                'poste_id' => $request->poste_id,
                'backup_operator_id' => $request->operator_id,
                'backup_slot' => $request->backup_slot,
                'assigned_date' => today()
            ]);

            // Clear dashboard cache
            $cacheKey = 'dashboard_data_' . today()->format('Y-m-d');
            Cache::forget($cacheKey);

            // Load the operator relationship
            $assignment->load('backupOperator:id,first_name,last_name');

            return response()->json([
                'success' => true,
                'assignment' => [
                    'id' => $assignment->id,
                    'slot' => $assignment->backup_slot,
                    'operator_name' => $assignment->backupOperator->first_name . ' ' . $assignment->backupOperator->last_name,
                    'operator_id' => $assignment->backup_operator_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to assign backup: ' . $e->getMessage()], 500);
        }
    }

    public function remove(Request $request, $assignmentId): JsonResponse
    {
        try {
            $assignment = BackupAssignment::findOrFail($assignmentId);
            $assignment->delete();

            // Clear dashboard cache
            $cacheKey = 'dashboard_data_' . today()->format('Y-m-d');
            Cache::forget($cacheKey);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove backup: ' . $e->getMessage()], 500);
        }
    }

    public function getAvailableOperators(Request $request): JsonResponse
    {
        $posteId = $request->get('poste_id');
        $search = $request->get('search', '');

        // Get operators not already assigned as backup for this poste today
        $assignedOperatorIds = BackupAssignment::where('poste_id', $posteId)
            ->whereDate('assigned_date', today())
            ->pluck('backup_operator_id');

        // Also exclude the primary operator(s) assigned to this poste
        $primaryOperatorIds = Operator::where('poste_id', $posteId)->pluck('id');

        $excludedIds = $assignedOperatorIds->merge($primaryOperatorIds);

        $operators = Operator::whereNotIn('id', $excludedIds)
            ->when($search, function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                      ->orWhere('last_name', 'like', $term);
                });
            })
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->limit(20)
            ->get();

        return response()->json($operators);
    }

    public function getPosteAssignments(Request $request, $posteId): JsonResponse
    {
        try {
            $assignments = BackupAssignment::where('poste_id', $posteId)
                ->whereDate('assigned_date', today())
                ->with('backupOperator:id,first_name,last_name')
                ->orderBy('backup_slot')
                ->get()
                ->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'slot' => $assignment->backup_slot,
                        'operator_name' => $assignment->backupOperator->first_name . ' ' . $assignment->backupOperator->last_name,
                        'operator_id' => $assignment->backup_operator_id
                    ];
                });

            return response()->json([
                'success' => true,
                'assignments' => $assignments
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch assignments: ' . $e->getMessage()], 500);
        }
    }
}
