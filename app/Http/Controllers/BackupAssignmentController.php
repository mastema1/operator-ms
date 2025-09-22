<?php

namespace App\Http\Controllers;

use App\Models\BackupAssignment;
use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Services\DashboardCacheManager;

class BackupAssignmentController extends Controller
{
    public function assign(Request $request): JsonResponse
    {
        \Log::info('BackupAssignmentController::assign called', $request->all());
        
        $request->validate([
            'poste_id' => 'required|exists:postes,id',
            'operator_id' => 'required|exists:operators,id',
            'backup_operator_id' => 'required|exists:operators,id',
            'backup_slot' => 'required|integer|in:1'
        ]);

        try {
            \Log::info('Using updateOrCreate method');
            
            // Delete any existing backup assignment for this specific operator (single backup only)
            BackupAssignment::where('operator_id', $request->operator_id)
                ->whereDate('assigned_date', today())
                ->delete();
            
            // Create new assignment
            $assignment = BackupAssignment::create([
                'poste_id' => $request->poste_id,
                'operator_id' => $request->operator_id,
                'backup_operator_id' => $request->backup_operator_id,
                'backup_slot' => $request->backup_slot,
                'assigned_date' => today()
            ]);

            // Clear dashboard cache using centralized cache manager
            DashboardCacheManager::clearOnBackupChange();

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

            // Clear dashboard cache using centralized cache manager
            DashboardCacheManager::clearOnBackupChange();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove backup: ' . $e->getMessage()], 500);
        }
    }

    public function getAvailableOperators(Request $request): JsonResponse
    {
        $operatorId = $request->get('operator_id'); // The operator being replaced
        $search = $request->get('search', '');

        // Get operators already assigned as backup operators today
        $assignedOperatorIds = BackupAssignment::whereDate('assigned_date', today())
            ->pluck('backup_operator_id');

        // Exclude the specific operator being replaced and any already assigned backup operators
        $excludedIds = $assignedOperatorIds->push($operatorId);

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

    public function getOperatorAssignment(Request $request, $operatorId): JsonResponse
    {
        try {
            $assignment = BackupAssignment::where('operator_id', $operatorId)
                ->whereDate('assigned_date', today())
                ->with('backupOperator:id,first_name,last_name')
                ->first();

            if ($assignment) {
                $assignmentData = [
                    'id' => $assignment->id,
                    'slot' => $assignment->backup_slot,
                    'operator_name' => $assignment->backupOperator->first_name . ' ' . $assignment->backupOperator->last_name,
                    'operator_id' => $assignment->backup_operator_id
                ];
            } else {
                $assignmentData = null;
            }

            return response()->json([
                'success' => true,
                'assignment' => $assignmentData
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch assignment: ' . $e->getMessage()], 500);
        }
    }
}
