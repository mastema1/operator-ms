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
        $tenantId = auth()->user()->tenant_id ?? 0;

        // Handle vacant positions (no operator_id) - for vacant positions, we don't need to exclude anyone
        if (empty($operatorId)) {
            \Log::info('BackupAssignmentController::getAvailableOperators - Vacant position detected, operator_id is empty');
            $operatorId = 0; // Use 0 as a safe default that won't match any real operator ID
        }

        // Use optimized service for better performance
        $operators = \App\Services\AdvancedQueryOptimizationService::getAvailableBackupOperators(
            $tenantId, 
            $operatorId
        );

        // Apply search filter if provided
        if (!empty($search)) {
            $searchTerm = strtolower(trim($search));
            $operators = $operators->filter(function ($operator) use ($searchTerm) {
                $firstName = strtolower($operator->first_name ?? '');
                $lastName = strtolower($operator->last_name ?? '');
                $matricule = strtolower($operator->matricule ?? '');
                
                return str_contains($firstName, $searchTerm) || 
                       str_contains($lastName, $searchTerm) ||
                       str_contains($matricule, $searchTerm);
            });
        }

        // Convert to array format expected by frontend and limit results
        $operatorsArray = $operators->take(20) // Limit results for performance
            ->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'first_name' => $operator->first_name,
                    'last_name' => $operator->last_name,
                    'matricule' => $operator->matricule,
                    'poste_name' => $operator->poste_name
                ];
            })
            ->values();

        return response()->json($operatorsArray);
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
