<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->input('search', '');
        $criticalOnly = $request->boolean('critical_only');

        $operators = Operator::with('poste')
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
                $q->whereHas('poste', function ($p) {
                    $p->where('is_critical', true);
                });
            })
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        $total = Operator::count();

        return view('operators.index', compact('operators', 'search', 'total'));
    }

    public function create(): View
    {
        $postes = Poste::query()
            ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
            ->orderBy('name')
            ->get();
        return view('operators.create', compact('postes'));
    }

    public function store(StoreOperatorRequest $request): RedirectResponse
    {
        Operator::create($request->validated());
        return redirect()->route('operators.index')->with('success', 'Success! Operator created.');
    }

    public function edit(Operator $operator): View
    {
        $postes = Poste::query()
            ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
            ->orderBy('name')
            ->get();
        return view('operators.edit', compact('operator','postes'));
    }

    public function update(UpdateOperatorRequest $request, Operator $operator): RedirectResponse
    {
        $operator->update($request->validated());
        return redirect()->route('operators.index')->with('success', 'Success! Operator updated.');
    }

    public function destroy(Operator $operator): RedirectResponse
    {
        $operator->delete();
        return redirect()->route('operators.index')->with('success', 'Operator deleted successfully.');
    }
} 