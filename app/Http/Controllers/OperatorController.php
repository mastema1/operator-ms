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

class OperatorController extends Controller
{
    /**
     * Get the strict list of allowed poste names for operator assignment
     */
    private function getAllowedPosteNames(): array
    {
        return [
            'Poste 1', 'Poste 2', 'Poste 3', 'Poste 4', 'Poste 5', 'Poste 6', 'Poste 7', 'Poste 8', 'Poste 9', 'Poste 10',
            'Poste 11', 'Poste 12', 'Poste 13', 'Poste 14', 'Poste 15', 'Poste 16', 'Poste 17', 'Poste 18', 'Poste 19', 'Poste 20',
            'Poste 21', 'Poste 22', 'Poste 23', 'Poste 24', 'Poste 25', 'Poste 26', 'Poste 27', 'Poste 28', 'Poste 29', 'Poste 30',
            'Poste 31', 'Poste 32', 'Poste 33', 'Poste 34', 'Poste 35', 'Poste 36', 'Poste 37', 'Poste 38', 'Poste 39', 'Poste 40',
            'ABS', 'Bol', 'Bouchon', 'CMC', 'COND', 'FILISTE', 'FILISTE EPS', 'FW', 'Polyvalent', 'Ravitailleur', 'Retouche', 'TAG', 'Team Speaker', 'VISSEUSE'
        ];
    }

    /**
     * Get cached ligne options (static data that never changes)
     */
    private function getLigneOptions(): array
    {
        return Cache::remember('ligne_options', 86400, function () {
            return ['Ligne 1', 'Ligne 2', 'Ligne 3', 'Ligne 4', 'Ligne 5'];
        });
    }

    public function index(Request $request): View
    {
        $search = (string) $request->input('search', '');
        $criticalOnly = $request->boolean('critical_only');

        $operators = Operator::with([
                'poste:id,name',
                'attendances' => function ($query) {
                    $query->whereDate('date', today())
                          ->select('id', 'operator_id', 'date', 'status');
                }
            ])
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('matricule', 'like', $term)
                        ->orWhere('anciente', 'like', $term)
                        ->orWhere('type_de_contrat', 'like', $term)
                        ->orWhere('ligne', 'like', $term)
                        ->orWhereHas('poste', function ($p) use ($term) {
                            $p->where('name', 'like', $term);
                        });
                });
            })
            ->when($criticalOnly, function ($q) {
                // Filter by operators who are in critical positions (poste+ligne combinations)
                $q->whereExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('critical_positions')
                          ->whereColumn('critical_positions.poste_id', 'operators.poste_id')
                          ->whereColumn('critical_positions.ligne', 'operators.ligne')
                          ->where('critical_positions.tenant_id', auth()->user()->tenant_id)
                          ->where('critical_positions.is_critical', true);
                });
            })
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        $total = Operator::count();

        // Cache critical positions for 1 hour since they change infrequently
        $criticalPositions = Cache::remember('critical_positions_' . auth()->user()->tenant_id, 3600, function () {
            return \App\Models\CriticalPosition::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_critical', true)
                ->get()
                ->keyBy(function ($item) {
                    return $item->poste_id . '_' . $item->ligne;
                });
        });

        // Cache non-critical position overrides
        $nonCriticalPositions = Cache::remember('non_critical_positions_' . auth()->user()->tenant_id, 3600, function () {
            return \App\Models\CriticalPosition::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_critical', false)
                ->get()
                ->keyBy(function ($item) {
                    return $item->poste_id . '_' . $item->ligne;
                });
        });

        return view('operators.index', compact('operators', 'search', 'total', 'criticalPositions', 'nonCriticalPositions'));
    }

    public function apiIndex(): JsonResponse
    {
        // Cache operators list for 1 hour since it rarely changes
        $operators = Cache::remember('operators_api_list', 3600, function () {
            return Operator::select('id', 'first_name', 'last_name', 'ligne')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        });

        return response()->json($operators);
    }

    public function create(): View
    {
        // Cache postes list for 24 hours since it rarely changes
        $postes = Cache::remember('postes_dropdown_' . auth()->user()->tenant_id, 86400, function () {
            return Poste::select('id', 'name')->orderBy('name')->get();
        });
        
        return view('operators.create', compact('postes'));
    }

    public function store(StoreOperatorRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        
        // Debug logging
        \Log::info('Creating operator with data:', $validatedData);
        
        // Ensure tenant_id is set for the operator
        if (auth()->check() && auth()->user()->tenant_id) {
            $validatedData['tenant_id'] = auth()->user()->tenant_id;
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
        
        // Clear all related caches when operators are modified
        Cache::forget('operators_api_list');
        Cache::forget('postes_list');
        
        // Clear dashboard cache since operator changes affect dashboard
        \App\Http\Controllers\DashboardController::clearDashboardCache();
        
        // Clear critical positions cache since operator assignment may affect critical status
        Cache::forget('critical_positions_' . auth()->user()->tenant_id);
        Cache::forget('non_critical_positions_' . auth()->user()->tenant_id);
        
        $successMessage = 'Success! Operator created with poste: ' . ($operator->poste?->name ?? 'ERROR: No poste found');
        
        return redirect()->route('operators.index')->with('success', $successMessage);
    }

    public function edit(Operator $operator): View
    {
        // Cache allowed postes list for 24 hours since it rarely changes
        $postes = Cache::remember('allowed_postes_dropdown_' . auth()->user()->tenant_id, 86400, function () {
            return Poste::query()
                ->select('id', 'name')
                ->whereIn('name', $this->getAllowedPosteNames())
                ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
                ->orderBy('name')
                ->get();
        });
            
        return view('operators.edit', compact('operator','postes'));
    }

    public function update(UpdateOperatorRequest $request, Operator $operator): RedirectResponse
    {
        $operator->update($request->validated());
        
        // Update critical status for the poste-ligne combination if specified
        if ($request->has('is_critical') && $operator->poste_id && $operator->ligne) {
            $operator->setCriticalPosition((bool)$request->is_critical);
        }
        
        // Clear cached data when operators are modified
        Cache::forget('operators_api_list');
        
        // Clear dashboard cache since operator changes affect dashboard
        \App\Http\Controllers\DashboardController::clearDashboardCache();
        
        // Clear critical positions cache since operator changes may affect critical status
        Cache::forget('critical_positions_' . auth()->user()->tenant_id);
        Cache::forget('non_critical_positions_' . auth()->user()->tenant_id);
        
        return redirect()->route('operators.index')->with('success', 'Success! Operator updated.');
    }

    public function destroy(Operator $operator): RedirectResponse
    {
        $operator->delete();
        
        // Clear cached data when operators are modified
        Cache::forget('operators_api_list');
        
        // Clear dashboard cache since operator changes affect dashboard
        \App\Http\Controllers\DashboardController::clearDashboardCache();
        
        // Clear critical positions cache since operator deletion may affect critical status
        Cache::forget('critical_positions_' . auth()->user()->tenant_id);
        Cache::forget('non_critical_positions_' . auth()->user()->tenant_id);
        
        return redirect()->route('operators.index')->with('success', 'Operator deleted successfully.');
    }
} 