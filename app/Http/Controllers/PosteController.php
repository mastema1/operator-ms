<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosteRequest;
use App\Http\Requests\UpdatePosteRequest;
use App\Models\Poste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class PosteController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->input('search', '');

        // Get all postes with their operators
        $postes = \App\Models\Poste::with([
                'operators' => function ($query) {
                    $query->select('id', 'poste_id', 'first_name', 'last_name', 'matricule', 'ligne', 'tenant_id');
                }
            ])
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('operators', function ($op) use ($term) {
                            $op->where('first_name', 'like', $term)
                               ->orWhere('last_name', 'like', $term)
                               ->orWhere('matricule', 'like', $term)
                               ->orWhere('ligne', 'like', $term);
                        });
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $total = \App\Models\Poste::count();

        // Preload critical positions for display
        $criticalPositions = \App\Models\CriticalPosition::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_critical', true)
            ->get()
            ->keyBy(function ($item) {
                return $item->poste_id . '_' . $item->ligne;
            });

        return view('postes.index', compact('postes', 'search', 'total', 'criticalPositions'));
    }

    public function create(): View
    {
        return view('postes.create');
    }

    public function store(StorePosteRequest $request): RedirectResponse
    {
        Poste::create($request->validated());
        
        // Clear cached data when postes are modified
        Cache::forget('postes_list');
        
        // Clear dashboard cache since poste changes affect dashboard
        \App\Http\Controllers\DashboardController::clearDashboardCache();
        
        return redirect()->route('postes.index')->with('success', 'Success! Poste created.');
    }

    public function edit(Poste $poste): RedirectResponse
    {
        // Redirect to operators page filtered by this poste
        return redirect()->route('operators.index', ['search' => $poste->name])
            ->with('info', 'Showing operators assigned to ' . $poste->name . '. Click on an operator to edit their assignment.');
    }

    public function update(UpdatePosteRequest $request, Poste $poste): RedirectResponse
    {
        // This should not be used anymore - redirect to operators
        return redirect()->route('operators.index', ['search' => $poste->name])
            ->with('info', 'Please use the operators page to edit position assignments.');
    }

    public function destroy(Poste $poste): RedirectResponse
    {
        $poste->delete();
        
        // Clear cached data when postes are modified
        Cache::forget('postes_list');
        
        // Clear dashboard cache since poste changes affect dashboard
        \App\Http\Controllers\DashboardController::clearDashboardCache();
        
        return redirect()->route('postes.index')->with('success', 'Poste deleted successfully.');
    }
}
