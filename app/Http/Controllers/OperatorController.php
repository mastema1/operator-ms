<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use App\Services\DashboardCacheManager;

class OperatorController extends Controller
{
    /**
     * Get the current user's tenant ID safely
     */
    private function getCurrentTenantId(): ?int
    {
        if (!auth()->check() || !auth()->user()) {
            return null;
        }
        return auth()->user()->tenant_id;
    }

    /**
     * Get the strict list of allowed poste names for operator assignment
     */
    private function getAllowedPosteNames(): array
    {
        // Generate zero-padded numbered postes dynamically
        $numberedPostes = [];
        for ($i = 1; $i <= 40; $i++) {
            $numberedPostes[] = 'Poste ' . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        
        // Core named postes
        $namedPostes = [
            'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW', 
            'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE', 'Goullote'
        ];
        
        return array_merge($numberedPostes, $namedPostes);
    }

    /**
     * Get cached ligne options (static data that never changes)
     */
    private function getLigneOptions(): array
    {
        return Cache::remember('ligne_options', 300, function () { // 5 minutes - ligne options rarely change
            return ['Ligne 1', 'Ligne 2', 'Ligne 3', 'Ligne 4', 'Ligne 5'];
        });
    }

    public function index(Request $request): View
    {
        $search = (string) $request->input('search', '');
        $criticalOnly = $request->boolean('critical_only');
        $tenantId = $this->getCurrentTenantId();

        // Use optimized query service for better performance with concurrent users
        $operatorsCollection = \App\Services\AdvancedQueryOptimizationService::getOptimizedOperatorsList(
            $tenantId, 
            $search, 
            $criticalOnly
        );
        
        // Convert to paginated collection for view compatibility
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $operators = new \Illuminate\Pagination\LengthAwarePaginator(
            $operatorsCollection->forPage($currentPage, $perPage),
            $operatorsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(), 
                'pageName' => 'page'
            ]
        );
        $operators->withQueryString();

        $total = Operator::count();

        return view('operators.index', compact('operators', 'search', 'total'));
    }

    public function apiIndex(): JsonResponse
    {
        // Cache operators list for 1 hour since it rarely changes
        $operators = Cache::remember('operators_api_list', 3, function () { // 3 seconds for real-time updates
            return Operator::select('id', 'first_name', 'last_name', 'ligne')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        });

        return response()->json($operators);
    }

    public function create(): View
    {
        // Cache postes list for 24 hours since it rarely changes - now with Golden Order sorting
        $tenantId = $this->getCurrentTenantId();
        $postes = collect();
        
        if ($tenantId) {
            $postes = Cache::remember('postes_dropdown_golden_order_' . $tenantId, 300, function () { // 5 minutes - postes change less frequently
                return Poste::getForDropdownInGoldenOrder();
            });
        }
        
        return view('operators.create', compact('postes'));
    }

    public function store(StoreOperatorRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        
        // Debug logging
        \Log::info('Creating operator with data:', $validatedData);
        
        // Ensure tenant_id is set for the operator
        $tenantId = $this->getCurrentTenantId();
        if ($tenantId) {
            $validatedData['tenant_id'] = $tenantId;
        } else {
            return redirect()->back()->withErrors(['error' => 'User not authenticated or no tenant assigned.'])->withInput();
        }
        
        // Verify the poste exists and belongs to the current tenant
        $poste = \App\Models\Poste::find($validatedData['poste_id']);
        if (!$poste) {
            return redirect()->back()->withErrors(['poste_id' => 'Selected poste not found.'])->withInput();
        }
        
        $operator = Operator::create($validatedData);
        
        // Set critical status for the poste-ligne combination if specified
        if ($request->has('is_critical') && $request->is_critical && $operator->poste_id && $operator->ligne) {
            $operator->setCriticalPosition(true);
        }
        
        // Reload the operator with poste relationship to verify it was saved correctly
        $operator->load('poste');
        
        \Log::info('Operator created:', [
            'id' => $operator->id,
            'poste_id' => $operator->poste_id,
            'poste_name' => $operator->poste?->name,
            'tenant_id' => $operator->tenant_id
        ]);
        
        // Clear all related caches when operators are modified using centralized cache manager
        DashboardCacheManager::clearOnOperatorChange();
        
        $successMessage = 'Success! Operator created with poste: ' . ($operator->poste?->name ?? 'ERROR: No poste found');
        
        return redirect()->route('operators.index')->with('success', $successMessage);
    }

    public function edit(Operator $operator): View
    {
        // Cache allowed postes list for 24 hours since it rarely changes - now with Golden Order sorting
        $tenantId = $this->getCurrentTenantId();
        $postes = collect();
        
        if ($tenantId) {
            $postes = Cache::remember('allowed_postes_dropdown_golden_order_' . $tenantId, 300, function () { // 5 minutes - postes change less frequently
                // Get all postes for tenant, then filter by allowed names and apply Golden Order
                $allPostes = Poste::getForDropdownInGoldenOrder();
                $allowedNames = $this->getAllowedPosteNames();
                
                return $allPostes->filter(function ($poste) use ($allowedNames) {
                    return in_array($poste->name, $allowedNames);
                });
            });
        }
            
        return view('operators.edit', compact('operator','postes'));
    }

    public function update(UpdateOperatorRequest $request, Operator $operator): RedirectResponse
    {
        $operator->update($request->validated());
        
        // Update critical status for the poste-ligne combination if specified
        if ($request->has('is_critical') && $operator->poste_id && $operator->ligne) {
            $operator->setCriticalPosition((bool)$request->is_critical);
        }
        
        // Clear all related caches when operators are modified using centralized cache manager
        DashboardCacheManager::clearOnOperatorChange();
        
        return redirect()->route('operators.index')->with('success', 'Success! Operator updated.');
    }

    public function destroy(Operator $operator): RedirectResponse
    {
        $operator->delete();
        
        // Clear all related caches when operators are modified using centralized cache manager
        DashboardCacheManager::clearOnOperatorChange();
        
        return redirect()->route('operators.index')->with('success', 'Operator deleted successfully.');
    }
} 